<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvoiceItems\StoreInvoiceItemRequest;
use App\Http\Requests\InvoiceItems\UpdateInvoiceItemRequest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\InvoiceItems\ManagementService;
use App\Services\InvoiceItems\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceItemController extends Controller
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
     * Passes paginated invoice items, scoped to their parent invoice,
     * to the InvoiceItems/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(
        Invoice $invoice,
        Request $request
    ): Response {
        $this->authorize(
            'viewAny',
            InvoiceItem::class
        );

        $data = $this->query->getPaginated(
            $request->user(),
            $invoice,
            $request->only([
                'search',
                'sort_by',
                'sort_direction',
                'trashed',
                'per_page',
            ])
        );

        return Inertia::render(
            'InvoiceItems/Index',
            $data
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(
        Invoice $invoice
    ): Response {
        $this->authorize(
            'create',
            InvoiceItem::class
        );

        return Inertia::render(
            'InvoiceItems/Create',
            $this->query->getFormData($invoice)
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreInvoiceItemRequest, which also
     * implicitly authorises the operation via its authorize() method.
     */
    public function store(
        StoreInvoiceItemRequest $request,
        Invoice $invoice
    ): JsonResponse|RedirectResponse {
        $invoiceItem = $this->management->store(
            $request,
            $invoice
        );

        if ($request->wantsJson()) {
            return response()->json(
                $invoiceItem,
                201
            );
        }

        return redirect()->route(
            'invoices.items.show',
            [$invoice->id, $invoiceItem->id]
        );
    }

    /**
     * Display the specified resource.
     *
     * Passes a single invoice item to the InvoiceItems/Show Inertia page.
     *
     * Authorises via the 'view' policy before rendering.
     */
    public function show(
        Invoice $invoice,
        InvoiceItem $invoiceItem,
        Request $request
    ): Response {
        $this->authorize(
            'view',
            $invoiceItem
        );

        $data = $this->query->getById(
            $request->user(),
            $invoice,
            $invoiceItem->id
        );

        return Inertia::render(
            'InvoiceItems/Show',
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
        InvoiceItem $invoiceItem,
        Request $request
    ): Response {
        $this->authorize(
            'update',
            $invoiceItem
        );

        $data = array_merge(
            $this->query->getById(
                $request->user(),
                $invoice,
                $invoiceItem->id
            ),
            $this->query->getFormData($invoice)
        );

        return Inertia::render(
            'InvoiceItems/Edit',
            $data
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateInvoiceItemRequest, which also
     * implicitly authorises the operation via its authorize() method.
     */
    public function update(
        UpdateInvoiceItemRequest $request,
        Invoice $invoice,
        InvoiceItem $invoiceItem
    ): JsonResponse|RedirectResponse {
        $invoiceItem = $this->management->update(
            $request,
            $invoiceItem
        );

        if ($request->wantsJson()) {
            return response()->json(
                $invoiceItem
            );
        }

        return redirect()->route(
            'invoices.items.show',
            [$invoice->id, $invoiceItem->id]
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     */
    public function destroy(
        Request $request,
        Invoice $invoice,
        InvoiceItem $invoiceItem
    ): JsonResponse|RedirectResponse {
        $this->authorize(
            'delete',
            $invoiceItem
        );

        $this->management->destroy(
            $invoiceItem,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(
                null,
                204
            );
        }

        return redirect()->route(
            'invoices.items.index',
            $invoice->id
        );
    }

    /**
     * Restore a soft-deleted invoice item.
     *
     * Resolves the trashed model manually, scoped to its parent invoice,
     * since route model binding excludes soft-deleted records by default.
     *
     * Authorises via the 'restore' policy before proceeding.
     */
    public function restore(
        Invoice $invoice,
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $invoiceItem = $invoice->items()->onlyTrashed()->findOrFail($id);

        $this->authorize(
            'restore',
            $invoiceItem
        );

        $this->management->restore(
            $invoice,
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
            'invoices.items.index',
            $invoice->id
        );
    }

    /**
     * Permanently delete a soft-deleted invoice item.
     *
     * Resolves the trashed model manually, scoped to its parent invoice,
     * since route model binding excludes soft-deleted records by default.
     *
     * Authorises via the 'forceDelete' policy before proceeding.
     */
    public function forceDelete(
        Invoice $invoice,
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $invoiceItem = $invoice->items()->onlyTrashed()->findOrFail($id);

        $this->authorize(
            'forceDelete',
            $invoiceItem
        );

        $this->management->forceDelete(
            $invoice,
            $id,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route(
            'invoices.items.index',
            $invoice->id
        );
    }

    /**
     * Bulk soft-delete multiple invoice items, scoped to their parent invoice.
     *
     * Authorises each invoice item individually via the 'delete' policy.
     */
    public function bulkDelete(
        Invoice $invoice,
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
                'exists:invoice_items,id',
            ],
        ]);

        $this->management->bulkDelete(
            $invoice,
            $validated['ids'],
            $request->user(),
            fn (InvoiceItem $invoiceItem) => $this->authorize(
                'delete',
                $invoiceItem
            )
        );

        if (request()->wantsJson()) {
            return response()->json(
                null,
                204
            );
        }

        return redirect()->route(
            'invoices.items.index',
            $invoice->id
        );
    }

    /**
     * Bulk restore multiple soft-deleted invoice items, scoped to their
     * parent invoice.
     *
     * Authorises each invoice item individually via the 'restore' policy.
     */
    public function bulkRestore(
        Invoice $invoice,
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
                'exists:invoice_items,id',
            ],
        ]);

        $this->management->bulkRestore(
            $invoice,
            $validated['ids'],
            $request->user(),
            fn (InvoiceItem $invoiceItem) => $this->authorize(
                'restore',
                $invoiceItem
            )
        );

        if ($request->wantsJson()) {
            return response()->json(
                null,
                204
            );
        }

        return redirect()->route(
            'invoices.items.index',
            $invoice->id
        );
    }
}
