<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BulkIdsRequest;
use App\Http\Requests\Products\ImportProductRequest;
use App\Http\Requests\Products\StoreProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;
use App\Models\Product;
use App\Services\Products\ManagementService;
use App\Services\Products\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
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
        $this->authorize('viewAny', Product::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $request->only([
                'search',
                'is_active',
                'unit',
                'sort_by',
                'sort_direction',
                'trashed',
                'per_page',
            ])
        );

        return Inertia::render('Products/Index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', Product::class);

        return Inertia::render('Products/Create', $this->query->getFormData());
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation and authorisation are handled by StoreProductRequest.
     */
    public function store(
        StoreProductRequest $request
    ): JsonResponse|RedirectResponse {
        $product = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($product, 201);
        }

        return redirect()->route('products.show', $product->id);
    }

    /**
     * Display the specified resource.
     *
     * Authorises via the 'view' policy before rendering.
     */
    public function show(Product $product, Request $request): Response
    {
        $this->authorize('view', $product);

        return Inertia::render(
            'Products/Show',
            $this->query->getById($request->user(), $product)
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(Product $product, Request $request): Response
    {
        $this->authorize('update', $product);

        return Inertia::render(
            'Products/Edit',
            $this->query->getById($request->user(), $product)
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation and authorisation are handled by UpdateProductRequest.
     */
    public function update(
        UpdateProductRequest $request,
        Product $product
    ): JsonResponse|RedirectResponse {
        $product = $this->management->update($request, $product);

        if ($request->wantsJson()) {
            return response()->json($product);
        }

        return redirect()->route('products.show', $product->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     */
    public function destroy(
        Request $request,
        Product $product
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $product);

        $this->management->destroy($product, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('products.index');
    }

    /**
     * Restore a soft-deleted product.
     *
     * Route model binding excludes trashed records, so the model is
     * resolved manually. Authorises via the 'restore' policy.
     */
    public function restore(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $product = Product::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $product);

        $this->management->restore($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('products.index');
    }

    /**
     * Permanently delete a soft-deleted product.
     *
     * Authorises via the 'forceDelete' policy before proceeding.
     */
    public function forceDelete(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $product = Product::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $product);

        $this->management->forceDelete($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('products.index');
    }

    /**
     * Bulk soft-delete multiple products.
     *
     * Authorises each product individually via the 'delete' policy.
     */
    public function bulkDelete(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkDelete', Product::class);

        $result = $this->management->bulkDelete(
            $request->validated('ids'),
            $request->user(),
            fn (Product $product) => $this->authorize('delete', $product)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('products.index');
    }

    /**
     * Bulk restore multiple soft-deleted products.
     *
     * Authorises each product individually via the 'restore' policy.
     */
    public function bulkRestore(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkRestore', Product::class);

        $result = $this->management->bulkRestore(
            $request->validated('ids'),
            $request->user(),
            fn (Product $product) => $this->authorize('restore', $product)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('products.index');
    }

    /**
     * Import products from an uploaded CSV file.
     *
     * Authorisation is handled by ImportProductRequest::authorize().
     */
    public function import(ImportProductRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->management->import($request);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('products.index')
            ->with('import_result', $result);
    }

    /**
     * Export products matching the current filters as a CSV download.
     *
     * Authorises via the 'export' policy before proceeding.
     */
    public function export(Request $request): JsonResponse|RedirectResponse|StreamedResponse
    {
        $this->authorize('export', Product::class);

        return $this->management->export(
            $request->only(['search', 'trashed'])
        );
    }

    /**
     * Parse and validate an uploaded CSV, returning a per-row preview
     * without persisting anything.
     *
     * Authorisation is handled by ImportProductRequest::authorize().
     */
    public function importPreview(ImportProductRequest $request): JsonResponse
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
        $this->authorize('import', Product::class);

        $request->validate(['token' => ['required', 'uuid']]);

        return response()->json($this->management->importer()->commit($request->input('token'), $request->user()->id));
    }
}
