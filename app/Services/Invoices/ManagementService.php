<?php

namespace App\Services\Invoices;

use App\Http\Requests\Invoices\StoreInvoiceRequest;
use App\Http\Requests\Invoices\UpdateInvoiceRequest;
use App\Models\Invoice;
use App\Models\User;

class ManagementService
{
    /**
     * Inject the required services into the management service.
     */
    public function __construct(
        protected readonly CreatorService $creator,
        protected readonly UpdaterService $updater,
        protected readonly DeleterService $destructor,
        protected readonly RestorerService $restorer
    ) {}

    /**
     * Create a new invoice.
     */
    public function store(StoreInvoiceRequest $request): Invoice
    {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing invoice.
     */
    public function update(
        UpdateInvoiceRequest $request,
        Invoice $invoice
    ): Invoice {
        return $this->updater->update(
            $invoice,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a invoice.
     */
    public function destroy(
        Invoice $invoice,
        User $actor
    ): void {
        $this->destructor->delete($invoice, $actor->id);
    }

    /**
     * Restore a soft-deleted invoice.
     */
    public function restore(
        int $id,
        User $actor
    ): Invoice {
        $invoice = Invoice::withTrashed()->findOrFail($id);

        return $this->restorer->restore($invoice, $actor->id);
    }

    /**
     * Force delete a invoice, permanently removing it from the database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $invoice = Invoice::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($invoice, $actor->id);
    }

    /**
     * Bulk restore invoices.
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $invoices = Invoice::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($invoices as $invoice) {
            /** @var Invoice $invoice */
            $authoriseCallback($invoice);
            $this->restorer->restore($invoice, $actor->id);
            $restored[] = $invoice->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($invoices->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete invoices.
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $deleted = [];

        foreach ($ids as $id) {
            $invoice = Invoice::findOrFail($id);
            $authoriseCallback($invoice);

            $this->destructor->delete($invoice, $actor->id);
            $deleted[] = $id;
        }

        return $deleted;
    }
}
