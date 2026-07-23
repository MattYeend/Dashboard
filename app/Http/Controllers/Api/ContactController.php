<?php

namespace App\Http\Controllers\Api;

use App\Enums\TokenAbility;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contacts\StoreContactRequest;
use App\Http\Requests\Contacts\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use App\Services\Contacts\CreatorService;
use App\Services\Contacts\DeleterService;
use App\Services\Contacts\QueryService;
use App\Services\Contacts\UpdaterService;
use App\Traits\AuthorisesTokenAbility;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    use AuthorisesTokenAbility;

    public function __construct(
        private readonly QueryService $queryService,
        private readonly CreatorService $creatorService,
        private readonly UpdaterService $updaterService,
        private readonly DeleterService $deleterService,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * Returns paginated contacts, already formatted by the QueryService,
     * as a raw JSON response (not a resource collection, since getPaginated
     * returns a pre-shaped array rather than an Eloquent collection).
     *
     * Authorises via the 'viewAny' policy, then confirms the request's token carries the
     * 'contacts:read' ability before returning data.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Contact::class);
        $this->authoriseTokenAbility(
            $request,
            TokenAbility::ContactsRead->value
        );

        $data = $this->queryService->getPaginated(
            $request->user(),
            $request->only([
                'search',
                'sort_by',
                'sort_direction',
                'trashed',
                'per_page',
            ])
        );

        return response()->json($data);
    }

    /**
     * Store a newly created resource.
     *
     * Creates a contact from validated request data via the CreatorService,
     * passing the authenticated user's ID as the acting creator.
     *
     * Authorises via the 'create' policy, then confirms the request's token carries the
     * 'contacts:write' ability before persisting.
     */
    public function store(
        StoreContactRequest $request
    ): ContactResource {
        $this->authorize('create', Contact::class);
        $this->authoriseTokenAbility(
            $request,
            TokenAbility::ContactsWrite->value
        );

        $contact = $this->creatorService->create(
            $request->validated(),
            $request->user()->id,
        );

        return new ContactResource($contact);
    }

    /**
     * Display the specified resource.
     *
     * Returns a single contact via the QueryService, which includes formatted
     * relations and permissions metadata, as a raw JSON response.
     *
     * Authorises via the 'view' policy, then confirms the request's token carries the
     * 'contacts:read' ability before returning data.
     */
    public function show(
        Request $request,
        Contact $contact
    ): JsonResponse {
        $this->authorize('view', $contact);
        $this->authoriseTokenAbility(
            $request,
            TokenAbility::ContactsRead->value
        );

        return response()->json(
            $this->queryService->getById(
                $request->user(),
                $contact->id
            )
        );
    }

    /**
     * Update the specified resource.
     *
     * Updates a contact from validated request data via the UpdaterService,
     * passing the authenticated user's ID as the acting updater.
     *
     * Authorises via the 'update' policy, then confirms the request's token carries the
     * 'contacts:write' ability before persisting.
     */
    public function update(
        UpdateContactRequest $request,
        Contact $contact
    ): ContactResource {
        $this->authorize('update', $contact);
        $this->authoriseTokenAbility(
            $request,
            TokenAbility::ContactsWrite->value
        );

        $updated = $this->updaterService->update(
            $contact,
            $request->validated(),
            $request->user()->id,
        );

        return new ContactResource($updated);
    }

    /**
     * Remove the specified resource.
     *
     * Soft-deletes a contact via the DeleterService, passing the authenticated
     * user's ID as the acting deleter, and returns an empty 204 response.
     *
     * Authorises via the 'delete' policy, then confirms the request's token carries the
     * 'contacts:write' ability before deleting.
     */
    public function destroy(
        Request $request,
        Contact $contact
    ): JsonResponse {
        $this->authorize('delete', $contact);
        $this->authoriseTokenAbility(
            $request,
            TokenAbility::ContactsWrite->value
        );

        $this->deleterService->delete(
            $contact,
            $request->user()->id
        );

        return response()->json(null, 204);
    }
}
