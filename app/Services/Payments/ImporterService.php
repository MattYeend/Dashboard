<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Actions\Payments\RecordPayment;
use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Models\Log;
use App\Models\Payment;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Concerns\ImportsViaPreview;
use App\Services\Invoices\BalanceService;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Handles CSV import of payments, including the preview/commit workflow
 * (via ImportsViaPreview) alongside the one-shot import() method.
 */
class ImporterService
{
    use ImportsViaPreview;

    /**
     * Column headers that must be present in the uploaded CSV.
     *
     * @var array<int, string>
     */
    protected const REQUIRED_COLUMNS = [
        'invoice_number',
        'amount',
        'method',
        'paid_at',
    ];

    /**
     * The most data rows accepted in one file.
     */
    protected const MAX_ROWS = 1000;

    /**
     * Inject the required services.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly RecordPayment $recordPayment,
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * The private-disk directory this module's uploads are stored under.
     */
    protected function importStoragePath(): string
    {
        return 'imports/payments';
    }

    /**
     * The Log::ACTION_IMPORT_* constant for this module's batch log entry.
     */
    protected function importAuditAction(): int
    {
        return Log::ACTION_IMPORT_PAYMENT;
    }

    /**
     * Persist a single validated row as a new completed payment.
     *
     * A ValidationException here means the balance or invoice changed
     * between preview and commit (assumption 6 in this issue).
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $context
     *
     * @throws ValidationException
     */
    protected function persistRow(array $data, int $actorId, array $context = []): void
    {
        $this->recordRow($data, User::findOrFail($actorId));
    }

    /**
     * Import payments from an uploaded CSV file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        UploadedFile $file,
        int $actorId
    ): array {
        $handle = fopen($file->getRealPath(), 'r');

        $header = fgetcsv($handle);
        $header = array_map(fn (?string $column) => strtolower(trim((string) $column)), $header ?: []);

        $missing = array_diff(self::REQUIRED_COLUMNS, $header);

        if ($missing !== []) {
            fclose($handle);

            return [
                'imported' => 0,
                'skipped' => [[
                    'row' => 0,
                    'reason' => 'Missing required column(s): '.implode(', ', $missing),
                ]],
            ];
        }

        $imported = 0;
        $skipped = [];
        $rowNumber = 1;
        $actor = User::findOrFail($actorId);

        DB::transaction(function () use ($handle, $header, $actor, &$imported, &$skipped, &$rowNumber) {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                if ($rowNumber - 1 > self::MAX_ROWS) {
                    $skipped[] = [
                        'row' => $rowNumber,
                        'reason' => 'Row limit of '.self::MAX_ROWS.' reached. Split the file and import again.',
                    ];

                    break;
                }

                if (count($row) !== count($header)) {
                    $skipped[] = [
                        'row' => $rowNumber,
                        'reason' => sprintf(
                            'Expected %d columns but found %d',
                            count($header),
                            count($row),
                        ),
                    ];

                    continue;
                }

                $data = array_combine($header, $row);

                $error = $this->validateRow($data);

                if ($error !== null) {
                    $skipped[] = ['row' => $rowNumber, 'reason' => $error];

                    continue;
                }

                try {
                    $payment = $this->recordRow($data, $actor);
                } catch (ValidationException $exception) {
                    $skipped[] = [
                        'row' => $rowNumber,
                        'reason' => (string) collect($exception->errors())->flatten()->first(),
                    ];

                    continue;
                }

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_PAYMENT,
                    $actor,
                    $payment,
                    ['after' => $this->auditLogService->snapshot($payment)],
                );

                $imported++;
            }
        });

        fclose($handle);

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    /**
     * Validate a single row, returning an error string or null if valid.
     *
     * The balance is re-checked under a lock when the row is recorded, so
     * this method only rejects what can be known without one.
     *
     * @param  array<string, mixed>  $data
     */
    protected function validateRow(array $data): ?string
    {
        $number = trim((string) ($data['invoice_number'] ?? ''));

        if ($number === '') {
            return "Missing value for 'invoice_number'";
        }

        $invoice = Invoice::query()->with('status')->where('invoice_number', $number)->first();

        if ($invoice === null) {
            return "No invoice numbered '{$number}'";
        }

        if (! in_array($invoice->status?->title, BalanceService::PAYABLE_STATUSES, true)) {
            return "Invoice '{$number}' cannot receive payments in its current status";
        }

        $amount = trim((string) ($data['amount'] ?? ''));

        if (! preg_match('/^\d{1,8}(\.\d{1,2})?$/', $amount) || bccomp($amount, '0', 2) !== 1) {
            return 'amount must be a positive number with at most two decimal places';
        }

        $method = strtolower(trim((string) ($data['method'] ?? '')));

        if (PaymentMethod::tryFrom($method) === null) {
            return 'method must be one of: '.implode(', ', PaymentMethod::values());
        }

        $paidAt = trim((string) ($data['paid_at'] ?? ''));

        if ($paidAt === '') {
            return "Missing value for 'paid_at'";
        }

        try {
            $date = CarbonImmutable::parse($paidAt);
        } catch (\Throwable) {
            return 'paid_at is not a valid date';
        }

        if ($date->isFuture()) {
            return 'paid_at cannot be in the future';
        }

        $reference = trim((string) ($data['reference'] ?? ''));

        if (mb_strlen($reference) > 128) {
            return 'reference may not exceed 128 characters';
        }

        if (
            $reference !== ''
            && Payment::withTrashed()->where('invoice_id', $invoice->id)->where('reference', $reference)->exists()
        ) {
            return "A payment with reference '{$reference}' already exists on invoice '{$number}'";
        }

        if (mb_strlen(trim((string) ($data['notes'] ?? ''))) > 2000) {
            return 'notes may not exceed 2000 characters';
        }

        return null;
    }

    /**
     * Record one validated row through the same action the form uses.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    protected function recordRow(array $data, User $actor): Payment
    {
        $invoice = Invoice::query()
            ->where('invoice_number', trim((string) $data['invoice_number']))
            ->firstOrFail();

        return $this->recordPayment->handle(
            $this->dataPreparation->prepareForCreation([
                'invoice_id' => $invoice->id,
                'method' => strtolower(trim((string) $data['method'])),
                'amount' => $data['amount'],
                'paid_at' => $data['paid_at'],
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]),
            $actor
        );
    }
}
