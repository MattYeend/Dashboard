<?php

namespace App\Http\Controllers\Api;

use App\Enums\TokenAbility;
use App\Traits\AuthorisesTokenAbility;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use App\Services\Contacts\CreatorService;
use App\Services\Contacts\DeleterService;
use App\Services\Contacts\QueryService;
use App\Services\Contacts\UpdaterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
     * Returns paginated contacts as a JSON resource collection.
     *
     * Authorises via the 'viewAny' policy, then confirms the request's token carries the
     * 'contacts:read' ability before returning data.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Contact::class);
        $this->authoriseTokenAbility($request, TokenAbility::ContactsRead->value);

        return ContactResource::collection($this->queryService->paginate($request));
    }

    /**
     * Store a newly created resource.
     *
     * Creates a contact from validated request data via the CreatorService.
     *
     * Authorises via the 'create' policy, then confirms the request's token carries the
     * 'contacts:write' ability before persisting.
     */
    public function store(StoreContactRequest $request): ContactResource
    {
        $this->authorize('create', Contact::class);
        $this->authoriseTokenAbility($request, TokenAbility::ContactsWrite->value);

        return new ContactResource($this->creatorService->create($request->validated()));
    }

    /**
     * Display the specified resource.
     *
     * Returns a single contact as a JSON resource.
     *
     * Authorises via the 'view' policy, then confirms the request's token carries the
     * 'contacts:read' ability before returning data.
     */
    public function show(Request $request, Contact $contact): ContactResource
    {
        $this->authorize('view', $contact);
        $this->authoriseTokenAbility($request, TokenAbility::ContactsRead->value);

        return new ContactResource($contact);
    }

    /**
     * Update the specified resource.
     *
     * Updates a contact from validated request data via the UpdaterService.
     *
     * Authorises via the 'update' policy, then confirms the request's token carries the
     * 'contacts:write' ability before persisting.
     */
    public function update(UpdateContactRequest $request, Contact $contact): ContactResource
    {
        $this->authorize('update', $contact);
        $this->authoriseTokenAbility($request, TokenAbility::ContactsWrite->value);

        return new ContactResource($this->updaterService->update($contact, $request->validated()));
    }

    /**
     * Remove the specified resource.
     *
     * Soft-deletes a contact via the DeleterService and returns an empty 204 response.
     *
     * Authorises via the 'delete' policy, then confirms the request's token carries the
     * 'contacts:write' ability before deleting.
     */
    public function destroy(Request $request, Contact $contact): JsonResponse
    {
        $this->authorize('delete', $contact);
        $this->authoriseTokenAbility($request, TokenAbility::ContactsWrite->value);

        $this->deleterService->delete($contact);

        return response()->json(null, 204);
    }
}
