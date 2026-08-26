<?php

namespace App\Services\Search;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Order;
use App\Models\User;
use App\Services\Companies\FilterService as CompanyFilterService;
use App\Services\Companies\PolicyAuthorisationService as CompanyPolicyAuthorisationService;
use App\Services\Contacts\FilterService as ContactFilterService;
use App\Services\Contacts\PolicyAuthorisationService as ContactPolicyAuthorisationService;
use App\Services\Deals\FilterService as DealFilterService;
use App\Services\Deals\PolicyAuthorisationService as DealPolicyAuthorisationService;
use App\Services\Orders\FilterService as OrderFilterService;
use App\Services\Orders\PolicyAuthorisationService as OrderPolicyAuthorisationService;
use App\Services\Users\FilterService as UserFilterService;
use App\Services\Users\PolicyAuthorisationService as UserPolicyAuthorisationService;

class SearchService
{
    /**
     * Maximum number of results returned for each searchable module.
     */
    private const int RESULTS_PER_MODULE = 5;

    /**
     * Inject the required filter and policy authorisation services.
     */
    public function __construct(
        private readonly UserFilterService $userFilterService,
        private readonly UserPolicyAuthorisationService $userPolicyAuthorisationService,
        private readonly CompanyFilterService $companyFilterService,
        private readonly CompanyPolicyAuthorisationService $companyPolicyAuthorisationService,
        private readonly ContactFilterService $contactFilterService,
        private readonly ContactPolicyAuthorisationService $contactPolicyAuthorisationService,
        private readonly OrderFilterService $orderFilterService,
        private readonly OrderPolicyAuthorisationService $orderPolicyAuthorisationService,
        private readonly DealFilterService $dealFilterService,
        private readonly DealPolicyAuthorisationService $dealPolicyAuthorisationService,
    ) {}

    /**
     * Search across all supported application modules.
     *
     * Each module is searched independently and is limited to the
     * configured number of results. Modules the actor cannot view
     * are returned as empty result sets.
     *
     * @return array<string, array<int, array{id: int, label: string, url: string}>>
     */
    public function search(User $actor, string $term): array
    {
        return [
            'users' => $this->searchUsers($actor, $term),
            'companies' => $this->searchCompanies($actor, $term),
            'contacts' => $this->searchContacts($actor, $term),
            'orders' => $this->searchOrders($actor, $term),
            'deals' => $this->searchDeals($actor, $term),
        ];
    }

    /**
     * Search users matching the supplied term.
     *
     * Returns an empty result when the actor does not have permission
     * to view users.
     */
    private function searchUsers(User $actor, string $term): array
    {
        if (! $this->userPolicyAuthorisationService->canViewAny($actor)) {
            return [];
        }

        return $this->userFilterService
            ->applySearch(User::query(), $term)
            ->take(self::RESULTS_PER_MODULE)
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'label' => $user->name,
                'url' => route('users.show', $user),
            ])
            ->all();
    }

    /**
     * Search companies matching the supplied term.
     *
     * Returns an empty result when the actor does not have permission
     * to view companies.
     */
    private function searchCompanies(User $actor, string $term): array
    {
        if (! $this->companyPolicyAuthorisationService->canViewAny($actor)) {
            return [];
        }

        return $this->companyFilterService
            ->applySearch(Company::query(), $term)
            ->take(self::RESULTS_PER_MODULE)
            ->get()
            ->map(fn (Company $company) => [
                'id' => $company->id,
                'label' => $company->name,
                'url' => route('companies.show', $company),
            ])
            ->all();
    }

    /**
     * Search contacts matching the supplied term.
     *
     * Returns an empty result when the actor does not have permission
     * to view contacts.
     */
    private function searchContacts(User $actor, string $term): array
    {
        if (! $this->contactPolicyAuthorisationService->canViewAny($actor)) {
            return [];
        }

        return $this->contactFilterService
            ->applySearch(Contact::query(), $term)
            ->take(self::RESULTS_PER_MODULE)
            ->get()
            ->map(fn (Contact $contact) => [
                'id' => $contact->id,
                'label' => $contact->email ?? $contact->phone ?? (string) $contact->id,
                'url' => route('contacts.show', $contact),
            ])
            ->all();
    }

    /**
     * Search orders matching the supplied term.
     *
     * Returns an empty result when the actor does not have permission
     * to view orders.
     */
    private function searchOrders(User $actor, string $term): array
    {
        if (! $this->orderPolicyAuthorisationService->canViewAny($actor)) {
            return [];
        }

        return $this->orderFilterService
            ->applySearch(Order::query(), $term)
            ->take(self::RESULTS_PER_MODULE)
            ->get()
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'label' => $order->order_number,
                'url' => route('orders.show', $order),
            ])
            ->all();
    }

    /**
     * Search deals matching the supplied term.
     *
     * Returns an empty result when the actor does not have permission
     * to view deals.
     */
    private function searchDeals(User $actor, string $term): array
    {
        if (! $this->dealPolicyAuthorisationService->canViewAny($actor)) {
            return [];
        }

        return $this->dealFilterService
            ->applySearch(Deal::query(), $term)
            ->take(self::RESULTS_PER_MODULE)
            ->get()
            ->map(fn (Deal $deal) => [
                'id' => $deal->id,
                'label' => $deal->title,
                'url' => route('deals.show', $deal),
            ])
            ->all();
    }
}
