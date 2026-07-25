<?php

namespace App\Http\Controllers;

use App\Http\Requests\Invoices\StoreInvoiceRequest;
use App\Http\Requests\Invoices\UpdateInvoiceRequest;
use App\Models\Invoice;
use App\Services\Invoices\ManagementService;
use App\Services\Invoices\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
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
     * Passes paginated invoices to the Invoices/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(
        Request $request
    ): Response {
        $this->authorize(
            'viewAny',
            Invoice::class
        );

        $data = $this->query->getPaginated(
            $request->user(),
            request()->only([
                'search',
                'sort_by',
                'sort_direction',
                'trashed',
                'per_page',
            ])
        );

        return Inertia::render(
            'Invoices/Index',
            $data
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize(
            'create',
            Invoice::class
        );

        return Inertia::render(
            'Invoices/Create',
            $this->query->getFormData()
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreInvoiceRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(
        StoreInvoiceRequest $request
    ): JsonResponse|RedirectResponse {
        $invoice = $this->management->store(
            $request
        );

        if ($request->wantsJson()) {
            return response()->json(
                $invoice,
                201
            );
        }

        return redirect()->route(
            'invoices.show',
            $invoice->id
        );
    }

    /**
     * Display the specified resource.
     *
     * Passes a single invoice to the Invoices/Show Inertia page.
     *
     * Authorises via the 'view' policy before rendering.
     */
    public function show(
        Invoice $invoice,
        Request $request
    ): Response {
        $this->authorize(
            'view',
            $invoice
        );

        $data = $this->query->getById(
            $request->user(),
            $invoice->id
        );

        return Inertia::render(
            'Invoices/Show',
            $data
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(
        Invoice $invoice,
        Request $request
    ): Response {
        $this->authorize(
            'update',
            $invoice
        );

        $data = array_merge(
            $this->query->getById(
                $request->user(),
                $invoice->id
            ),
            $this->query->getFormData()
        );

        return Inertia::render(
            'Invoices/Edit',
            $data
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateInvoiceRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     */
    public function update(
        UpdateInvoiceRequest $request,
        Invoice $invoice
    ): JsonResponse|RedirectResponse {
        $invoice = $this->management->update(
            $request,
            $invoice
        );

        if ($request->wantsJson()) {
            return response()->json(
                $invoice
            );
        }

        return redirect()->route(
            'invoices.show',
            $invoice->id
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * invoice instance is still fully accessible during logging.
     */
    public function destroy(
        Request $request,
        Invoice $invoice
    ): JsonResponse|RedirectResponse {
        $this->authorize(
            'delete',
            $invoice
        );

        $this->management->destroy(
            $invoice,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(
                null,
                204
            );
        }

        return redirect()->route(
            'invoices.index'
        );
    }

    /**
     * Restore a soft-deleted invoice.
     *
     * Resolves the trashed model manually since route model binding
     * excludes soft-deleted records by default.
     *
     * Authorises via the 'restore' policy before proceeding.
     */
    public function restore(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $invoice = Invoice::onlyTrashed()->findOrFail($id);

        $this->authorize(
            'restore',
            $invoice
        );

        $this->management->restore(
            $id,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(
                null,
                204
            );
        }

        return redirect()->route(
            'invoices.index'
        );
    }

    /**
     * Permanently delete a soft-deleted invoice.
     *
     * Resolves the trashed model manually since route model binding
     * excludes soft-deleted records by default.
     *
     * Authorises via the 'forceDelete' policy before proceeding.
     */
    public function forceDelete(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $invoice = Invoice::onlyTrashed()->findOrFail($id);

        $this->authorize(
            'forceDelete',
            $invoice
        );

        $this->management->forceDelete(
            $id,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route(
            'invoices.index'
        );
    }

    /**
     * Bulk soft-delete multiple invoices.
     *
     * Authorises each invoice individually via the 'delete' policy.
     */
    public function bulkDelete(
        Request $request
    ): JsonResponse|RedirectResponse {
        $request->validate([
            'ids' => [
                'required',
                'array',
            ],
            'ids.*' => [
                'required',
                'integer',
                'exists:invoices,id',
            ],
        ]);

        $actor = $request->user();
        $ids = $request->input('ids');

        $this->management->bulkDelete(
            $ids,
            $actor,
            fn (Invoice $invoice) => $this->authorize(
                'delete',
                $invoice
            )
        );

        if (request()->wantsJson()) {
            return response()->json(
                null,
                204
            );
        }

        return redirect()->route(
            'invoices.index'
        );
    }

    /**
     * Bulk restore multiple soft-deleted invoices.
     *
     * Authorises each invoice individually via the 'restore' policy.
     */
    public function bulkRestore(
        Request $request
    ): JsonResponse|RedirectResponse {
        $validated = $request->validate([
            'ids' => [
                'required',
                'array',
            ],
            'ids.*' => [
                'required',
                'integer',
                'exists:invoices,id',
            ],
        ]);

        $this->management->bulkRestore(
            $validated['ids'],
            $request->user(),
            fn (Invoice $invoice) => $this->authorize(
                'restore',
                $invoice
            )
        );

        if ($request->wantsJson()) {
            return response()->json(
                null,
                204
            );
        }

        return redirect()->route(
            'invoices.index'
        );
    }

    /**
     * Mark the specified invoice as sent.
     *
     * Sets status_id to 'Sent' and records sent_at.
     *
     * TODO: dispatch the invoice email/PDF once invoice items are
     * complete - this currently only updates status and timestamp.
     *
     * Authorises via the 'update' policy before proceeding.
     */
    public function send(
        Invoice $invoice,
        Request $request
    ): JsonResponse|RedirectResponse {
        $this->authorize(
            'send',
            $invoice
        );

        $invoice = $this->management->send(
            $invoice,
            $request->user()
        );

        if ($request->wantsJson()) {
            return response()->json(
                $invoice
            );
        }

        return redirect()->route(
            'invoices.show',
            $invoice->id
        );
    }

    /**
     * Mark the specified invoice as paid.
     *
     * Sets status_id to 'Paid' and records paid_at.
     *
     * Authorises via the 'update' policy before proceeding.
     */
    public function markAsPaid(
        Invoice $invoice,
        Request $request
    ): JsonResponse|RedirectResponse {
        $this->authorize(
            'markAsPaid',
            $invoice
        );

        $invoice = $this->management->markAsPaid(
            $invoice,
            $request->user()
        );

        if ($request->wantsJson()) {
            return response()->json(
                $invoice
            );
        }

        return redirect()->route(
            'invoices.show',
            $invoice->id
        );
    }

    /**
     * Mark the specified invoice as unpaid.
     *
     * Reverts status_id to 'Pending' and clears paid_at.
     *
     * Authorises via the 'update' policy before proceeding.
     */
    public function markAsUnpaid(
        Invoice $invoice,
        Request $request
    ): JsonResponse|RedirectResponse {
        $this->authorize(
            'markAsUnpaid',
            $invoice
        );

        $invoice = $this->management->markAsUnpaid(
            $invoice,
            $request->user()
        );

        if ($request->wantsJson()) {
            return response()->json(
                $invoice
            );
        }

        return redirect()->route(
            'invoices.show',
            $invoice->id
        );
    }
}
