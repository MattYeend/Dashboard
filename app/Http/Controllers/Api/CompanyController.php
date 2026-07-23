<?php

namespace App\Http\Controllers\Api;

use App\Enums\TokenAbility;
use App\Http\Controllers\Controller;
use App\Http\Requests\Companies\StoreCompanyRequest;
use App\Http\Requests\Companies\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use App\Services\Companies\CreatorService;
use App\Services\Companies\DeleterService;
use App\Services\Companies\QueryService;
use App\Services\Companies\UpdaterService;
use App\Traits\AuthorisesTokenAbility;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
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
     * Returns paginated companies, already formatted by the QueryService,
     * as a raw JSON response (not a resource collection, since getPaginated
     * returns a pre-shaped array rather than an Eloquent collection).
     *
     * Authorises via the 'viewAny' policy, then confirms the request's token carries the
     * 'companies:read' ability before returning data.
     */
    public function index(
        Request $request
    ): JsonResponse {
        $this->authorize('viewAny', Company::class);
        $this->authoriseTokenAbility($request, TokenAbility::CompaniesRead->value);

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
     * Creates a company from validated request data via the CreatorService,
     * passing the authenticated user's ID as the acting creator.
     *
     * Authorises via the 'create' policy, then confirms the request's token carries the
     * 'companies:write' ability before persisting.
     */
    public function store(
        StoreCompanyRequest $request
    ): CompanyResource {
        $this->authorize('create', Company::class);
        $this->authoriseTokenAbility($request, TokenAbility::CompaniesWrite->value);

        $company = $this->creatorService->create(
            $request->validated(),
            $request->user()->id,
        );

        return new CompanyResource($company);
    }

    /**
     * Display the specified resource.
     *
     * Returns a single company via the QueryService, which includes formatted
     * relations and permissions metadata, as a raw JSON response.
     *
     * Authorises via the 'view' policy, then confirms the request's token carries the
     * 'companies:read' ability before returning data.
     */
    public function show(
        Request $request,
        Company $company
    ): JsonResponse {
        $this->authorize('view', $company);
        $this->authoriseTokenAbility($request, TokenAbility::CompaniesRead->value);

        return response()->json(
            $this->queryService->getById(
                $request->user(),
                $company->id
            )
        );
    }

    /**
     * Update the specified resource.
     *
     * Updates a company from validated request data via the UpdaterService,
     * passing the authenticated user's ID as the acting updater.
     *
     * Authorises via the 'update' policy, then confirms the request's token carries the
     * 'companies:write' ability before persisting.
     */
    public function update(
        UpdateCompanyRequest $request,
        Company $company
    ): CompanyResource {
        $this->authorize('update', $company);
        $this->authoriseTokenAbility($request, TokenAbility::CompaniesWrite->value);

        $updated = $this->updaterService->update(
            $company,
            $request->validated(),
            $request->user()->id,
        );

        return new CompanyResource($updated);
    }

    /**
     * Remove the specified resource.
     *
     * Soft-deletes a company via the DeleterService, passing the authenticated
     * user's ID as the acting deleter, and returns an empty 204 response.
     *
     * Authorises via the 'delete' policy, then confirms the request's token carries the
     * 'companies:write' ability before deleting.
     */
    public function destroy(
        Request $request,
        Company $company
    ): JsonResponse {
        $this->authorize('delete', $company);
        $this->authoriseTokenAbility($request, TokenAbility::CompaniesWrite->value);

        $this->deleterService->delete(
            $company,
            $request->user()->id
        );

        return response()->json(null, 204);
    }
}
