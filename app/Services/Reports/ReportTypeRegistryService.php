<?php

namespace App\Services\Reports;

use App\Services\Companies\QueryService as CompaniesQueryService;
use App\Services\Orders\QueryService as OrdersQueryService;
use App\Services\Users\QueryService as UsersQueryService;

class ReportTypeRegistryService
{
    /**
     * Allow-list of report types. Keys are short, UI-facing identifiers.
     * 'query_service' is the fully-qualified QueryService class used to
     * fetch the underlying dataset for that type - never resolved from
     * user input directly, to prevent arbitrary class instantiation.
     *
     * @return array<string, array{label: string, query_service: string}>
     */
    public function all(): array
    {
        return [
            'orders' => [
                'label' => 'Orders',
                'query_service' => OrdersQueryService::class,
            ],
            'companies' => [
                'label' => 'Companies',
                'query_service' => CompaniesQueryService::class,
            ],
            'users' => [
                'label' => 'Users',
                'query_service' => UsersQueryService::class,
            ],
        ];
    }

    /**
     * Short keys and labels for populating the "report type" select.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function types(): array
    {
        return collect($this->all())
            ->map(fn (array $config, string $key) => ['value' => $key, 'label' => $config['label']])
            ->values()
            ->all();
    }

    /**
     * Resolve the QueryService class for a given report type key.
     */
    public function queryServiceForKey(string $key): ?string
    {
        return $this->all()[$key]['query_service'] ?? null;
    }

    /**
     * Resolve the human-readable label for a given report type key.
     */
    public function labelForKey(?string $key): ?string
    {
        if ($key === null) {
            return null;
        }

        return $this->all()[$key]['label'] ?? null;
    }
}
