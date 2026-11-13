<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BulkIdsRequest;
use App\Http\Requests\PaymentStatuses\ImportPaymentStatusRequest;
use App\Http\Requests\PaymentStatuses\StorePaymentStatusRequest;
use App\Http\Requests\PaymentStatuses\UpdatePaymentStatusRequest;
use App\Models\PaymentStatus;
use App\Services\PaymentStatuses\ManagementService;
use App\Services\PaymentStatuses\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentStatusController extends Controller
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
        $this->authorize('viewAny', PaymentStatus::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $request->only([
                'search',
                'sort_by',
                'sort_direction',
                'trashed',
                'per_page',
            ])
        );

        return Inertia::render('PaymentStatuses/Index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', PaymentStatus::class);

        return Inertia::render('PaymentStatuses/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation and authorisation are handled by StorePaymentStatusRequest.
     */
    public function store(
        StorePaymentStatusRequest $request
    ): JsonResponse|RedirectResponse {
        $paymentStatus = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($paymentStatus, 201);
        }

        return redirect()->route('payment-statuses.show', $paymentStatus->id);
    }

    /**
     * Display the specified resource.
     *
     * Authorises via the 'view' policy before rendering.
     */
    public function show(PaymentStatus $paymentStatus, Request $request): Response
    {
        $this->authorize('view', $paymentStatus);

        return Inertia::render(
            'PaymentStatuses/Show',
            $this->query->getById($request->user(), $paymentStatus)
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(PaymentStatus $paymentStatus, Request $request): Response
    {
        $this->authorize('update', $paymentStatus);

        return Inertia::render(
            'PaymentStatuses/Edit',
            $this->query->getById($request->user(), $paymentStatus)
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation and authorisation are handled by UpdatePaymentStatusRequest.
     */
    public function update(
        UpdatePaymentStatusRequest $request,
        PaymentStatus $paymentStatus
    ): JsonResponse|RedirectResponse {
        $paymentStatus = $this->management->update($request, $paymentStatus);

        if ($request->wantsJson()) {
            return response()->json($paymentStatus);
        }

        return redirect()->route('payment-statuses.show', $paymentStatus->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding. System statuses
     * are refused by the policy and again by the deleter service.
     */
    public function destroy(
        Request $request,
        PaymentStatus $paymentStatus
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $paymentStatus);

        $this->management->destroy($paymentStatus, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('payment-statuses.index');
    }

    /**
     * Restore a soft-deleted payment status.
     *
     * Route model binding excludes trashed records, so the model is
     * resolved manually. Authorises via the 'restore' policy.
     */
    public function restore(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $paymentStatus = PaymentStatus::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $paymentStatus);

        $this->management->restore($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('payment-statuses.index');
    }

    /**
     * Permanently delete a soft-deleted payment status.
     *
     * Authorises via the 'forceDelete' policy before proceeding.
     */
    public function forceDelete(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $paymentStatus = PaymentStatus::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $paymentStatus);

        $this->management->forceDelete($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('payment-statuses.index');
    }

    /**
     * Bulk soft-delete multiple payment statuses.
     *
     * Authorises each payment status individually via the 'delete' policy.
     */
    public function bulkDelete(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkDelete', PaymentStatus::class);

        $result = $this->management->bulkDelete(
            $request->validated('ids'),
            $request->user(),
            fn (PaymentStatus $paymentStatus) => $this->authorize('delete', $paymentStatus)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('payment-statuses.index');
    }

    /**
     * Bulk restore multiple soft-deleted payment statuses.
     *
     * Authorises each payment status individually via the 'restore' policy.
     */
    public function bulkRestore(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkRestore', PaymentStatus::class);

        $result = $this->management->bulkRestore(
            $request->validated('ids'),
            $request->user(),
            fn (PaymentStatus $paymentStatus) => $this->authorize('restore', $paymentStatus)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('payment-statuses.index');
    }

    /**
     * Import payment statuses from an uploaded CSV file.
     *
     * Authorisation is handled by ImportPaymentStatusRequest::authorize().
     */
    public function import(ImportPaymentStatusRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->management->import($request);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('payment-statuses.index')
            ->with('import_result', $result);
    }

    /**
     * Export payment statuses matching the current filters as a CSV download.
     *
     * Authorises via the 'export' policy before proceeding.
     */
    public function export(Request $request): JsonResponse|RedirectResponse|StreamedResponse
    {
        $this->authorize('export', PaymentStatus::class);

        return $this->management->export(
            $request->only(['search', 'trashed'])
        );
    }

    /**
     * Parse and validate an uploaded CSV, returning a per-row preview
     * without persisting anything.
     *
     * Authorisation is handled by ImportPaymentStatusRequest::authorize().
     */
    public function importPreview(ImportPaymentStatusRequest $request): JsonResponse
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
        $this->authorize('import', PaymentStatus::class);

        $request->validate(['token' => ['required', 'uuid']]);

        return response()->json($this->management->importer()->commit($request->input('token'), $request->user()->id));
    }
}
