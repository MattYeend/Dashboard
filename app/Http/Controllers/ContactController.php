<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Models\Contact;
use App\Services\Contacts\ManagementService;
use App\Services\Contacts\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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
        protected readonly ManagementService $management,
        protected readonly QueryService $query,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * Passes paginated contacts to the Contacts/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Contact::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $request->only(['search', 'sort_by', 'sort_direction', 'trashed', 'per_page'])
        );

        return Inertia::render('Contacts/Index', $data);
    }

    /**
     * Show the form for creating a new contact.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', Contact::class);

        $data = $this->query->getFormData();

        return Inertia::render('Contacts/Create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreContactRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(StoreContactRequest $request): JsonResponse|RedirectResponse
    {
        $contact = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($contact, 201);
        }

        return redirect()->route('contacts.show', $contact->id);
    }

    /**
     * Display the specified resource.
     *
     * Passes a single contact to the Contacts/Show Inertia page.
     *
     * Authorises via the 'view' and 'access' policies before rendering.
     */
    public function show(
        Contact $contact,
        Request $request
    ): Response {
        $this->authorize('view', $contact);

        $data = $this->query->getById(
            $request->user(),
            $contact->id
        );

        return Inertia::render('Contacts/Show', $data);
    }

    /**
     * Show the form for editing an existing contact.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(
        Contact $contact,
        Request $request
    ): Response {
        $this->authorize('update', $contact);

        $data = $this->query->getById(
            $request->user(),
            $contact->id
        );

        return Inertia::render('Contacts/Edit', $data);
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
    ): JsonResponse|RedirectResponse {
        $contact = $this->management->update($request, $contact);

        if ($request->wantsJson()) {
            return response()->json($contact);
        }

        return redirect()->route('contacts.show', $contact->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * contact instance is still fully accessible during logging.
     */
    public function destroy(
        Request $request,
        Contact $contact
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $contact);

        $this->management->destroy($contact, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('contacts.index');
    }

    /**
     * Restore a soft-deleted contact.
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
        $contact = Contact::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $contact);

        $this->management->restore($id, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('contacts.index');
    }

    /**
     * Permanently delete a soft-deleted contact.
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
        $contact = Contact::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $contact);

        $this->management->forceDelete($id, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('contacts.index');
    }

    /**
     * Bulk soft-delete multiple contacts.
     *
     * Authorises each contact individually via the 'delete' policy.
     */
    public function bulkDelete(Request $request): JsonResponse|RedirectResponse
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

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('contacts.index');
    }

    /**
     * Bulk restore multiple soft-deleted contacts.
     *
     * Authorises each contact individually via the 'restore' policy.
     */
    public function bulkRestore(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:contacts,id'],
        ]);

        $this->management->bulkRestore(
            $validated['ids'],
            $request->user(),
            fn (Contact $contact) => $this->authorize('restore', $contact)
        );

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('contacts.index');
    }

    /**
     * Get the list of selectable "owner" options for a given contactable type.
     */
    public function contactableOptions(Request $request): JsonResponse
    {
        $type = $request->query('type', '');

        $options = $this->query->getContactableOptions($type);

        return response()->json($options);
    }
}
