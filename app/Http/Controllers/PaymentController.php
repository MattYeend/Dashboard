<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BulkIdsRequest;
use App\Http\Requests\Payments\ImportPaymentRequest;
use App\Http\Requests\Payments\RefundPaymentRequest;
use App\Http\Requests\Payments\StorePaymentRequest;
use App\Http\Requests\Payments\UpdatePaymentRequest;
use App\Models\Payment;
use App\Services\Payments\ManagementService;
use App\Services\Payments\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Inject the required services into the controller.
     */
    public function __construct(
        private readonly QueryService $query,
        private readonly ManagementService $management,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Payment::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $request->only([
                'search',
                'invoice_id',
                'status_id',
                'method',
                'type',
                'from',
                'to',
                'sort_by',
                'sort_direction',
                'trashed',
                'per_page',
            ])
        );

        return Inertia::render('Payments/Index', $data);
    }

    /**
     * Show the form for recording a payment, optionally for a given invoice.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(Request $request): Response
    {
        $this->authorize('create', Payment::class);

        return Inertia::render(
            'Payments/Create',
            $this->query->getFormData($request->integer('invoice_id') ?: null)
        );
    }

    /**
     * Store a newly recorded payment.
     *
     * Validation and authorisation are handled by StorePaymentRequest. The
     * user returns to the invoice, where the new balance is shown.
     */
    public function store(
        StorePaymentRequest $request
    ): JsonResponse|RedirectResponse {
        $payment = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($payment, 201);
        }

        return redirect()
            ->route('invoices.show', $payment->invoice_id)
            ->with('success', 'Payment recorded.');
    }

    /**
     * Display the specified resource.
     *
     * Authorises via the 'view' policy before rendering.
     */
    public function show(Payment $payment, Request $request): Response
    {
        $this->authorize('view', $payment);

        return Inertia::render(
            'Payments/Show',
            $this->query->getById($request->user(), $payment)
        );
    }

    /**
     * Show the form for editing a payment that has not completed.
     *
     * Authorises via the 'update' policy before rendering, which refuses a
     * completed payment.
     */
    public function edit(Payment $payment, Request $request): Response
    {
        $this->authorize('update', $payment);

        return Inertia::render(
            'Payments/Edit',
            $this->query->getById($request->user(), $payment)
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation and authorisation are handled by UpdatePaymentRequest.
     */
    public function update(
        UpdatePaymentRequest $request,
        Payment $payment
    ): JsonResponse|RedirectResponse {
        $payment = $this->management->update($request, $payment);

        if ($request->wantsJson()) {
            return response()->json($payment);
        }

        return redirect()->route('payments.show', $payment->id);
    }

    /**
     * Refund all or part of a completed payment.
     *
     * Validation and authorisation are handled by RefundPaymentRequest.
     */
    public function refund(
        RefundPaymentRequest $request,
        Payment $payment
    ): JsonResponse|RedirectResponse {
        $refund = $this->management->refund($request, $payment);

        if ($request->wantsJson()) {
            return response()->json(['id' => $refund->id], 201);
        }

        return redirect()
            ->route('invoices.show', $payment->invoice_id)
            ->with('success', 'Refund recorded.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     */
    public function destroy(
        Request $request,
        Payment $payment
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $payment);

        $this->management->destroy($payment, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('payments.index');
    }

    /**
     * Restore a soft-deleted payment.
     *
     * Route model binding excludes trashed records, so the model is
     * resolved manually. Authorises via the 'restore' policy.
     */
    public function restore(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $payment = Payment::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $payment);

        $this->management->restore($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('payments.index');
    }

    /**
     * Permanently delete a soft-deleted payment.
     *
     * Authorises via the 'forceDelete' policy before proceeding.
     */
    public function forceDelete(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $payment = Payment::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $payment);

        $this->management->forceDelete($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('payments.index');
    }

    /**
     * Bulk soft-delete multiple payments.
     *
     * Authorises each payment individually via the 'delete' policy.
     */
    public function bulkDelete(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkDelete', Payment::class);

        $result = $this->management->bulkDelete(
            $request->validated('ids'),
            $request->user(),
            fn (Payment $payment) => $this->authorize('delete', $payment)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('payments.index');
    }

    /**
     * Bulk restore multiple soft-deleted payments.
     *
     * Authorises each payment individually via the 'restore' policy.
     */
    public function bulkRestore(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkRestore', Payment::class);

        $result = $this->management->bulkRestore(
            $request->validated('ids'),
            $request->user(),
            fn (Payment $payment) => $this->authorize('restore', $payment)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('payments.index');
    }

    /**
     * Import payments from an uploaded CSV file.
     *
     * Authorisation is handled by ImportPaymentRequest::authorize().
     */
    public function import(ImportPaymentRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->management->import($request);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('payments.index')
            ->with('import_result', $result);
    }

    /**
     * Export payments matching the current filters as a CSV download.
     *
     * Authorises via the 'export' policy before proceeding.
     */
    public function export(Request $request): JsonResponse|RedirectResponse|StreamedResponse
    {
        $this->authorize('export', Payment::class);

        return $this->management->export(
            $request->only(['search', 'trashed'])
        );
    }

    /**
     * Parse and validate an uploaded CSV, returning a per-row preview
     * without persisting anything.
     *
     * Authorisation is handled by ImportPaymentRequest::authorize().
     */
    public function importPreview(ImportPaymentRequest $request): JsonResponse
    {
        $importer = $this->management->importer();
        $token = $importer->storeUpload($request->file('file'), $request->user()->id);

        return response()->json(['token' => $token, ...$importer->preview($token, $request->user()->id)]);
    }

    /**
     * Commit a previously previewed import, persisting valid rows only.
     *
     * Authorises via the 'import' policy before proceeding.
     */
    public function importCommit(Request $request): JsonResponse
    {
        $this->authorize('import', Payment::class);

        $request->validate(['token' => ['required', 'uuid']]);

        return response()->json($this->management->importer()->commit($request->input('token'), $request->user()->id));
    }
}
