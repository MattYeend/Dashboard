<?php

namespace App\Http\Controllers;

use App\Http\Requests\Organisations\BulkInviteOrganisationMembersRequest;
use App\Http\Requests\Organisations\InviteOrganisationMemberRequest;
use App\Http\Requests\Organisations\StoreOrganisationRequest;
use App\Http\Requests\Organisations\UpdateOrganisationRequest;
use App\Http\Requests\Organisations\UpdateOrganisationSettingsRequest;
use App\Http\Requests\Organisations\RequestOrganisationDeletionRequest;
use App\Models\Organisation;
use App\Models\OrganisationDataExport;
use App\Models\User;
use App\Services\Organisations\InvitationService;
use App\Services\Organisations\ManagementService;
use App\Services\Organisations\PolicyAuthorisationService;
use App\Services\Organisations\QueryService;
use App\Services\Plans\FormatterService;
use App\Services\Plans\SeatCalculatorService;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrganisationController extends Controller
{
    use AuthorizesRequests;

    /**
     * Inject the required services into the controller.
     */
    public function __construct(
        private readonly QueryService $query,
        private readonly ManagementService $management,
        private readonly InvitationService $invitations,
        private readonly SeatCalculatorService $seatCalculatorService,
        private readonly FormatterService $formatterService,
        private readonly PolicyAuthorisationService $authorisationService,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Organisation::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $request->only([
                'search',
                'trashed',
                'sort_by',
                'sort_direction',
                'per_page',
            ])
        );

        return Inertia::render('Organisations/Index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $this->authorize('create', Organisation::class);

        return Inertia::render('Organisations/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        StoreOrganisationRequest $request
    ): JsonResponse|RedirectResponse {
        $organisation = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($organisation, 201);
        }

        return redirect()->route('organisations.show', $organisation->id);
    }

    /**
     * Display the specified resource.
     */
    public function show(
        Request $request,
        Organisation $organisation
    ): Response {
        $this->authorize('view', $organisation);

        $data = $this->query->getById($request->user(), $organisation);

        $data['can_switch'] = $request->user()->can('switch', $organisation);
        $data['can_remove_member'] = $request->user()->can('removeMember', $organisation);
        $data['can_view_billing'] = $request->user()->can('viewBilling', $organisation);
        $data['can_invite'] = $request->user()->can('invite', $organisation);
        $data['assignable_roles'] = $this->authorisationService->assignableRolesFor($request->user(), $organisation);

        return Inertia::render('Organisations/Show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(
        Request $request,
        Organisation $organisation
    ): Response {
        $this->authorize('update', $organisation);

        $data = $this->query->getById($request->user(), $organisation);

        return Inertia::render('Organisations/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateOrganisationRequest $request,
        Organisation $organisation
    ): JsonResponse|RedirectResponse {
        $this->authorize('update', $organisation);

        $updated = $this->management->update($request, $organisation);

        if ($request->wantsJson()) {
            return response()->json($updated);
        }

        return redirect()->route('organisations.show', $updated->id);
    }

    /**
     * Display the organisation's settings page.
     */
    public function settings(Organisation $organisation): Response
    {
        $this->authorize('manageSettings', $organisation);

        $logo = $organisation->logoAttachment();

        return Inertia::render('Organisations/Settings', [
            'organisation' => $organisation,
            'logo_download_url' => $logo ? route('attachments.download', $logo) : null,
        ]);
    }

    /**
     * Update the organisation's settings.
     *
     * Authorisation is handled inside UpdateOrganisationSettingsRequest's
     * authorize() method, consistent with Store/UpdateOrganisationRequest.
     */
    public function updateSettings(
        UpdateOrganisationSettingsRequest $request,
        Organisation $organisation
    ): JsonResponse|RedirectResponse {
        $updated = $this->management->updateSettings($request, $organisation);

        if ($request->wantsJson()) {
            return response()->json($updated);
        }

        return redirect()->route(
            'organisations.settings',
            $updated->id
        )->with('success', 'Settings updated.');
    }

    /**
     * Display the organisation's billing page - current plan, active seat
     * count, and the resulting total based on price_per_user_per_month.
     */
    public function billing(Organisation $organisation): Response
    {
        $this->authorize('viewBilling', $organisation);

        $subscription = $organisation->subscriptions()->active()->first();
        $plan = $subscription?->plan;
        $seats = $this->seatCalculatorService->currentSeatCount($organisation);

        return Inertia::render('Organisations/Billing', [
            'organisation' => $organisation,
            'plan' => $plan ? $this->formatterService->format($plan) : null,
            'seats' => $seats,
            'total' => $plan ? $this->seatCalculatorService->calculateTotal($plan, $seats) : null,
        ]);
    }

    /**
     * Soft delete the specified resource.
     */
    public function destroy(
        Request $request,
        Organisation $organisation
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $organisation);

        $this->management->destroy($organisation, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('organisations.index');
    }

    /**
     * Restore a soft-deleted resource.
     */
    public function restore(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $organisation = Organisation::withTrashed()->findOrFail($id);
        $this->authorize('restore', $organisation);

        $this->management->restore($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('organisations.index');
    }

    /**
     * Permanently delete the specified resource.
     */
    public function forceDelete(
        Request $request,
        int $id
    ): JsonResponse|RedirectResponse {
        $organisation = Organisation::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $organisation);

        $this->management->forceDelete($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('organisations.index');
    }

    /**
     * Bulk soft delete multiple organisations.
     */
    public function bulkDelete(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer'],
        ]);

        $result = $this->management->bulkDelete(
            $request->input('ids'),
            $request->user(),
            fn (Organisation $organisation) => $this->authorize('delete', $organisation)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('organisations.index');
    }

    /**
     * Bulk restore multiple organisations.
     */
    public function bulkRestore(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer'],
        ]);

        $result = $this->management->bulkRestore(
            $request->input('ids'),
            $request->user(),
            fn (Organisation $organisation) => $this->authorize('restore', $organisation)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('organisations.index');
    }

    /**
     * Invite a user, by email, to join the organisation.
     */
    public function invite(
        InviteOrganisationMemberRequest $request,
        Organisation $organisation
    ): RedirectResponse|JsonResponse {
        $this->authorize('inviteWithRole', [$organisation, $request->validated()['invited_role']]);

        $this->invitations->invite(
            $organisation,
            $request->validated()['email'],
            $request->user(),
            $request->validated()['invited_role']
        );

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Invitation sent.']);
        }

        return redirect()->back()->with('success', 'Invitation sent.');
    }

    /**
     * Accept an organisation invitation via a signed link.
     *
     * The 'signed' route middleware rejects the request before this
     * method runs if the URL has been tampered with or has expired.
     */
    public function acceptInvitation(Request $request, string $token): RedirectResponse
    {
        $membership = $this->invitations->accept($token, $request->user());

        return redirect()
            ->route('organisations.show', $membership->organisation_id)
            ->with('success', 'You have joined the organisation.');
    }

    /**
     * Remove a member from the organisation.
     */
    public function removeMember(
        Organisation $organisation,
        User $user,
        Request $request
    ): RedirectResponse|JsonResponse {
        $this->authorize('removeMember', $organisation);

        $this->invitations->removeMember($organisation, $user, $request->user());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Member removed.']);
        }

        return redirect()->back()->with('success', 'Member removed.');
    }

    /**
     * Preview a bulk invitation batch - classify each email as invited,
     * skipped, or invalid without persisting anything, and surface the
     * seat-count impact if the organisation has an active subscription.
     */
    public function previewBulkInvite(
        BulkInviteOrganisationMembersRequest $request,
        Organisation $organisation
    ): JsonResponse {
        $this->authorize('inviteWithRole', [$organisation, $request->validated()['invited_role']]);

        $result = $this->invitations->previewBulk(
            $organisation,
            collect($request->validated()['emails'])
        );

        return response()->json([
            ...$result,
            'seat_impact' => $this->seatImpactFor($organisation, count($result['invited'])),
        ]);
    }

    /**
     * Commit a bulk invitation batch.
     */
    public function inviteBulk(
        BulkInviteOrganisationMembersRequest $request,
        Organisation $organisation
    ): JsonResponse {
        $this->authorize('inviteWithRole', [$organisation, $request->validated()['invited_role']]);

        $result = $this->invitations->inviteBulk(
            $organisation,
            collect($request->validated()['emails']),
            $request->validated()['invited_role'],
            $request->user()
        );

        return response()->json($result);
    }

    /**
     * Show the organisation's data privacy page - GDPR-style export and
     * deletion request actions.
     */
    public function dataPrivacy(Organisation $organisation): Response
    {
        $this->authorize('exportData', $organisation);

        return Inertia::render('Organisations/DataPrivacy', [
            'organisation' => [
                'id' => $organisation->id,
                'name' => $organisation->name,
            ],
            'permissions' => [
                'can_export' => request()->user()->can('exportData', $organisation),
                'can_delete' => request()->user()->can('requestDeletion', $organisation),
            ],
        ]);
    }

    /**
     * Request a full data export for the organisation, run as a queued job.
     */
    public function exportData(Request $request, Organisation $organisation): RedirectResponse
    {
        $this->authorize('exportData', $organisation);

        $this->management->requestDataExport($organisation, $request->user());

        return back()->with('success', 'Your data export has been requested. You will be notified when it is ready to download.');
    }

    /**
     * Request permanent deletion of the organisation, subject to the
     * type-the-organisation-name confirmation.
     */
    public function requestDeletion(
        RequestOrganisationDeletionRequest $request, 
        Organisation $organisation
    ): RedirectResponse {
        $this->management->requestDeletion($organisation, $request->user());

        return redirect()->route('organisations.index')
            ->with('success', "{$organisation->name} has been scheduled for deletion.");
    }

    /**
     * Download a previously generated data export archive.
     */
    public function downloadExport(
        Organisation $organisation, 
        OrganisationDataExport $export
    ): StreamedResponse {
        $this->authorize('exportData', $organisation);
        abort_unless($export->organisation_id === $organisation->id, 404);

        return $this->management->downloadExport($organisation, $export);
    }

    /**
     * Work out the seat-count and cost impact of adding the given number
     * of new members, if the organisation has an active subscription.
     *
     * @return array{current_seats: int, projected_seats: int, current_total: string, projected_total: string}|null
     */
    private function seatImpactFor(Organisation $organisation, int $additionalMembers): ?array
    {
        $subscription = $organisation->subscriptions()->active()->first();

        if ($subscription?->plan === null) {
            return null;
        }

        $currentSeats = $this->seatCalculatorService->currentSeatCount($organisation);
        $projectedSeats = $currentSeats + $additionalMembers;

        return [
            'current_seats' => $currentSeats,
            'projected_seats' => $projectedSeats,
            'current_total' => $this->seatCalculatorService->calculateTotal($subscription->plan, $currentSeats),
            'projected_total' => $this->seatCalculatorService->calculateTotal($subscription->plan, $projectedSeats),
        ];
    }
}
