<?php

namespace App\Services\Dashboard;

use App\Models\User;

/**
 * Gates access to the four distinct dashboard permissions.
 *
 * Dashboard has no Eloquent model of its own, so this doesn't bind to a
 * native Laravel Policy - the controller calls these methods directly and
 * aborts with a 403 when they return false, mirroring the standard
 * PolicyAuthorisationService pattern used on model-backed resources.
 */
class PolicyAuthorisationService
{
    /**
     * Gate for the main dashboard page (summary cards).
     */
    public function canViewDashboard(User $user): bool
    {
        return $user->can('view dashboard');
    }

    /**
     * Gate for the standalone statistics refresh endpoint.
     */
    public function canViewStatistics(User $user): bool
    {
        return $user->can('view statistics');
    }

    /**
     * Gate for the charts refresh endpoint.
     */
    public function canViewCharts(User $user): bool
    {
        return $user->can('view charts');
    }

    /**
     * Gate for the CSV export.
     */
    public function canExportData(User $user): bool
    {
        return $user->can('export dashboard data');
    }
}
