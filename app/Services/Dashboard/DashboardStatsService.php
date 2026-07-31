<?php

namespace App\Services\Dashboard;

use App\Models\User;

class DashboardStatsService
{
    public function __construct(
        protected TaskStatsService $taskStatsService,
        protected CompanyStatsService $companyStatsService,
        protected DealStatsService $dealStatsService,
        protected PipelineStatsService $pipelineStatsService,
        protected OrderStatsService $orderStatsService,
        protected InvoiceStatsService $invoiceStatsService,
        protected LatestPostsService $latestPostsService,
    ) {}

    /**
     * Build the combined dashboard stats payload for the given user.
     *
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        return [
            'tasks' => $this->taskStatsService->forUser($user),
            'companies' => $this->companyStatsService->summary(),
            'deals' => $this->dealStatsService->summary(),
            'pipelines' => $this->pipelineStatsService->summary(),
            'orders' => $this->orderStatsService->summary(),
            'invoices' => $this->invoiceStatsService->summary(),
            'posts' => $this->latestPostsService->latest(),
        ];
    }
}
