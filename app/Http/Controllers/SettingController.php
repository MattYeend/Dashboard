<?php

namespace App\Http\Controllers;

use App\Http\Requests\Settings\UpdateGeneralSettingRequest;
use App\Http\Requests\Settings\UpdateSecuritySettingRequest;
use App\Http\Requests\Settings\UpdateSystemSettingRequest;
use App\Services\Settings\FormatterService;
use App\Services\Settings\ManagementService;
use App\Services\Settings\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    use AuthorizesRequests;

    /**
     * Inject the required services into the controller.
     */
    public function __construct(
        private readonly QueryService $query,
        private readonly ManagementService $management,
        private readonly FormatterService $formatter,
    ) {}

    /**
     * Display the settings page.
     */
    public function index(Request $request): Response
    {
        $setting = $this->query->current();
        $permissions = $this->query->getWithPermissions($request->user())['permissions'];

        return Inertia::render('settings/Index', [
            'setting' => $this->formatter->format($setting),
            'permissions' => $permissions,
        ]);
    }

    /**
     * Update the general settings group.
     */
    public function updateGeneral(
        UpdateGeneralSettingRequest $request
    ): JsonResponse|RedirectResponse {
        $setting = $this->management->updateGeneral($request);

        if ($request->wantsJson()) {
            return response()->json($this->formatter->format($setting));
        }

        return redirect()->route('settings.index');
    }

    /**
     * Update the system settings group.
     */
    public function updateSystem(
        UpdateSystemSettingRequest $request
    ): JsonResponse|RedirectResponse {
        $setting = $this->management->updateSystem($request);

        if ($request->wantsJson()) {
            return response()->json($this->formatter->format($setting));
        }

        return redirect()->route('settings.index');
    }

    /**
     * Update the security settings group.
     */
    public function updateSecurity(
        UpdateSecuritySettingRequest $request
    ): JsonResponse|RedirectResponse {
        $setting = $this->management->updateSecurity($request);

        if ($request->wantsJson()) {
            return response()->json($this->formatter->format($setting));
        }

        return redirect()->route('settings.index');
    }
}
