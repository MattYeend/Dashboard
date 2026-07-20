<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tags\StoreTagRequest;
use App\Http\Requests\Tags\UpdateTagRequest;
use App\Models\Tag;
use App\Services\Tags\ManagementService;
use App\Services\Tags\QueryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller
{
    /**
     * Inject the required services into the controller.
     */
    public function __construct(
        protected readonly QueryService $queryService,
        protected readonly ManagementService $managementService,
    ) {}

    /**
     * Display a listing of tags.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Tag::class);

        return Inertia::render('Tags/Index', $this->queryService->getPaginated(
            Auth::user(),
            $request->all()
        ));
    }

    /**
     * Show the form for creating a new tag.
     */
    public function create(): Response
    {
        $this->authorize('create', Tag::class);

        return Inertia::render('Tags/Create');
    }

    /**
     * Store a newly created tag.
     */
    public function store(StoreTagRequest $request): RedirectResponse
    {
        $tag = $this->managementService->store($request);

        return redirect()->route('tags.show', $tag)
            ->with('success', 'Tag created successfully.');
    }

    /**
     * Display the specified tag.
     */
    public function show(int $id): Response
    {
        $data = $this->queryService->getById(Auth::user(), $id, withTrashed: true);

        $this->authorize('view', $data['tag']);

        return Inertia::render('Tags/Show', $data);
    }

    /**
     * Show the form for editing the specified tag.
     */
    public function edit(Tag $tag): Response
    {
        $this->authorize('update', $tag);

        return Inertia::render('Tags/Edit', [
            'tag' => $tag,
        ]);
    }

    /**
     * Update the specified tag.
     */
    public function update(UpdateTagRequest $request, Tag $tag): RedirectResponse
    {
        $this->managementService->update($request, $tag);

        return redirect()->route('tags.show', $tag)
            ->with('success', 'Tag updated successfully.');
    }

    /**
     * Soft delete the specified tag.
     */
    public function destroy(Tag $tag): RedirectResponse
    {
        $this->authorize('delete', $tag);

        $this->managementService->destroy($tag, Auth::user());

        return redirect()->route('tags.index')
            ->with('success', 'Tag deleted successfully.');
    }

    /**
     * Restore the specified soft-deleted tag.
     */
    public function restore(int $id): RedirectResponse
    {
        $tag = Tag::withTrashed()->findOrFail($id);
        $this->authorize('restore', $tag);

        $this->managementService->restore($id, Auth::user());

        return redirect()->back()->with('success', 'Tag restored successfully.');
    }

    /**
     * Permanently delete the specified tag.
     */
    public function forceDelete(int $id): RedirectResponse
    {
        $tag = Tag::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $tag);

        $this->managementService->forceDelete($id, Auth::user());

        return redirect()->route('tags.index')
            ->with('success', 'Tag permanently deleted.');
    }

    /**
     * Bulk soft delete tags.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $ids = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:tags,id'],
        ])['ids'];

        $this->managementService->bulkDelete(
            $ids,
            Auth::user(),
            fn (Tag $tag) => $this->authorize('delete', $tag)
        );

        return redirect()->back()->with('success', 'Tags deleted successfully.');
    }

    /**
     * Bulk restore tags.
     */
    public function bulkRestore(Request $request): RedirectResponse
    {
        $ids = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:tags,id'],
        ])['ids'];

        $this->managementService->bulkRestore(
            $ids,
            Auth::user(),
            fn (Tag $tag) => $this->authorize('restore', $tag)
        );

        return redirect()->back()->with('success', 'Tags restored successfully.');
    }
}
