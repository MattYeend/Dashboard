<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Models\Contact;
use App\Services\Contacts\LogService;
use App\Services\Contacts\ManagementService;
use App\Services\Contacts\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    use AuthorizesRequests;

    /**
     * Inject the required services into the controller.
     */
    public function __construct(
        protected ManagementService $management,
        protected QueryService $query,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * Passes paginated contacts to the Contacts/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', Contact::class);

        $contacts = $this->query->getPaginated(
            request()->only(['search', 'sort_by', 'sort_direction', 'trashed', 'per_page'])
        );

        return Inertia::render('Contacts/Index', [
            'contacts' => $contacts,
        ]);
    }

    /**
     * Show the form for creating a new contact.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', Contact::class);

        return Inertia::render('Contacts/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreContactRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(StoreContactRequest $request): JsonResponse
    {
        $contact = $this->management->store($request);

        return response()->json($contact, 201);
    }

    /**
     * Display the specified resource.
     *
     * Passes a single contact to the Contacts/Show Inertia page.
     *
     * Authorises via the 'view' and 'access' policies before rendering.
     */
    public function show(Contact $contact): Response
    {
        $this->authorize('view', $contact);
        $this->authorize('access', $contact);

        $contact = $this->query->getById($contact->id);

        return Inertia::render('Contacts/Show', [
            'contact' => $contact,
        ]);
    }

    /**
     * Show the form for editing an existing contact.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(Contact $contact): Response
    {
        $this->authorize('update', $contact);

        $contact = $this->query->getById($contact->id);

        return Inertia::render('Contacts/Edit', [
            'contact' => $contact,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateContactRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     */
    public function update(
        UpdateContactRequest $request,
        Contact $contact
    ): JsonResponse {
        $contact = $this->management->update($request, $contact);

        return response()->json($contact);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * contact instance is still fully accessible during logging.
     */
    public function destroy(Contact $contact): JsonResponse
    {
        $this->authorize('delete', $contact);

        $this->management->destroy($contact);

        return response()->json(null, 204);
    }

    /**
     * Restore a soft-deleted contact.
     *
     * Resolves the trashed model manually since route model binding
     * excludes soft-deleted records by default.
     *
     * Authorises via the 'restore' policy before proceeding.
     */
    public function restore(int $id): JsonResponse
    {
        $contact = Contact::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $contact);

        $this->management->restore($id);

        return response()->json(null, 204);
    }

    /**
     * Permanently delete a soft-deleted contact.
     *
     * Resolves the trashed model manually since route model binding
     * excludes soft-deleted records by default.
     *
     * Authorises via the 'forceDelete' policy before proceeding.
     */
    public function forceDelete(int $id): JsonResponse
    {
        $contact = Contact::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $contact);

        $this->management->forceDelete($id);

        return response()->json(null, 204);
    }

    /**
     * Bulk soft-delete multiple contacts.
     *
     * Authorises each contact individually via the 'delete' policy.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:contacts,id'],
        ]);

        $actor = $request->user();
        $ids = $request->input('ids');

        $this->management->bulkDelete(
            $ids,
            $actor,
            fn (Contact $contact) => $this->authorize('delete', $contact)
        );

        return response()->json(null, 204);
    }

    /**
     * Bulk restore multiple soft-deleted contacts.
     *
     * Authorises each contact individually via the 'restore' policy.
     */
    public function bulkRestore(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:contacts,id'],
        ]);

        $actor = $request->user();
        $ids = $request->input('ids');

        $this->management->bulkRestore(
            $ids,
            $actor,
            fn (Contact $contact) => $this->authorize('restore', $contact)
        );

        return response()->json(null, 204);
    }
}
