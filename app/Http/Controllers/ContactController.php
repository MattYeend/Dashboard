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
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ContactController extends Controller
{
    use AuthorizesRequests;
    
    /**
     * Inject the required services into the controller.
     *
     * @param  LogService $logger
     * @param  ManagementService $management
     * @param  QueryService $query
     */
    public function __construct(
        protected LogService $logger,
        protected ManagementService $management,
        protected QueryService $query,
    ) {
    }

    /**
     * Display a listing of the resource.
     *
     * Also includes the authenticated user's permissions for the Contact
     * resource, so the frontend can conditionally render create/view controls.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param  Request $request.
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Contact::class);

        $contacts = $this->query->getPaginated($request->all());

        return response()->json($contacts);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreContactRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     *
     * @param  StoreContactRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreContactRequest $request)
    {
        $contact = $this->management->store($request);
        return response()->json($contact, 201);
    }

    /**
     * Display the specified resource.
     *
     * Return a single contact by its model binding.
     *
     * Authorises via the 'view' policy before returning data.
     *
     * @param  Contact $contact
     *
     * @return JsonResponse
     */
    public function show(Contact $contact): JsonResponse
    {
        $this->authorize('view', $contact);
        $this->authorize('access', $contact);

        $contact = $this->query->getById($contact->id);

        return response()->json($contact);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateContactRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     *
     * @param  UpdateContactRequest $request
     * @param  Contact $contact
     *
     * @return JsonResponse
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
     *
     * @param  Contact $contact
     *
     * @return JsonResponse
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
     *
     * @param  int $id
     *
     * @return JsonResponse
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
     *
     * @param  int $id
     *
     * @return JsonResponse
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
     *
     * @param  Request $request
     *
     * @return JsonResponse
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:contacts,id'],
        ]);

        $actor = $request->user();
        $ids   = $request->input('ids');

        $this->management->bulkDelete(
            $ids,
            $actor,
            fn(Contact $contact) => $this->authorize('delete', $contact)
        );

        return response()->json(null, 204);
    }

    /**
     * Bulk restore multiple soft-deleted contacts.
     *
     * Authorises each contact individually via the 'restore' policy.
     *
     * @param  Request $request
     *
     * @return JsonResponse
     */
    public function bulkRestore(Request $request): JsonResponse
    {
        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:contacts,id'],
        ]);

        $actor = $request->user();
        $ids   = $request->input('ids');

        $this->management->bulkRestore(
            $ids,
            $actor,
            fn(Contact $contact) => $this->authorize('restore', $contact)
        );

        return response()->json(null, 204);
    }
}
