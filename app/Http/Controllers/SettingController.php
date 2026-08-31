<?php

namespace App\Http\Controllers;

use App\Http\Requests\Settings\UpdateGeneralSettingRequest;
use App\Http\Requests\Settings\UpdateSecuritySettingRequest;
use App\Http\Requests\Settings\UpdateSystemSettingRequest;
use App\Models\Setting;
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
     * Display the settings landing page.
     *
     * Accessible to any user who can view at least one settings
     * group; the page itself uses the `permissions` prop to decide
     * which group links/tabs to surface.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Setting::class);

        return Inertia::render('settings/Index', $this->pageData($request));
    }

    /**
     * Display the general settings page.
     *
     * Explicitly authorised per-page (rather than left to the
     * frontend to hide) — a direct GET to a group's page a user
     * cannot view is rejected server-side, not just visually hidden.
     */
    public function general(Request $request): Response
    {
        $this->authorize('viewGeneral', Setting::class);

        return Inertia::render('settings/general', $this->pageData($request));
    }

    /**
     * Display the system settings page.
     */
    public function system(Request $request): Response
    {
        $this->authorize('viewSystem', Setting::class);

        return Inertia::render('settings/system', $this->pageData($request));
    }

    /**
     * Display the security settings page.
     *
     * Rendered at 'settings/security-policy' rather than
     * 'settings/security' — the bare settings/security URI already
     * belongs to the starter kit's personal account security page
     * (Settings\SecurityController@edit, password/2FA for the
     * logged-in user's own account). Reusing that segment for our
     * app-wide security settings would silently shadow one of the
     * two GET routes depending on registration order, so this group
     * gets its own URI and Inertia component name. The PUT endpoint
     * this page submits to is unaffected — /settings/security was
     * never claimed by the account controller, only the GET verb was.
     */
    public function securityPolicy(Request $request): Response
    {
        $this->authorize('viewSecurity', Setting::class);

        return Inertia::render('settings/security-policy', $this->pageData($request));
    }

    /**
     * Build the shared prop payload for all three settings pages.
     *
     * Every page receives the full permissions set (not just its own
     * group's flags) so a shared settings tab/nav component can show
     * or hide links to the other two groups without a separate request.
     *
     * @return array{setting: array<string, mixed>, permissions: array<string, bool>}
     */
    private function pageData(Request $request): array
    {
        $setting = $this->query->current();
        $permissions = $this->query->getWithPermissions($request->user())['permissions'];

        return [
            'setting' => $this->formatter->format($setting),
            'permissions' => $permissions,
        ];
    }

    /**
     * Update the general settings group.
     *
     * Validation and authorisation are handled upstream by
     * UpdateGeneralSettingRequest, which checks the 'updateGeneral'
     * policy ability (backed by the 'edit settings' permission) via
     * its authorize() method.
     */
    public function updateGeneral(
        UpdateGeneralSettingRequest $request
    ): JsonResponse|RedirectResponse {
        $setting = $this->management->updateGeneral($request);

        if ($request->wantsJson()) {
            return response()->json($this->formatter->format($setting));
        }

        return redirect()->route('settings.general');
    }

    /**
     * Update the system settings group.
     *
     * Validation and authorisation are handled upstream by
     * UpdateSystemSettingRequest, which checks the 'updateSystem'
     * policy ability (backed by the 'edit system settings'
     * permission) via its authorize() method — distinct from the
     * general 'edit settings' permission.
     */
    public function updateSystem(
        UpdateSystemSettingRequest $request
    ): JsonResponse|RedirectResponse {
        $setting = $this->management->updateSystem($request);

        if ($request->wantsJson()) {
            return response()->json($this->formatter->format($setting));
        }

        return redirect()->route('settings.system');
    }

    /**
     * Update the security settings group.
     *
     * Validation and authorisation are handled upstream by
     * UpdateSecuritySettingRequest, which checks the 'updateSecurity'
     * policy ability (backed by the 'edit security settings'
     * permission) via its authorize() method — distinct from both the
     * general and system edit permissions.
     */
    public function updateSecurity(
        UpdateSecuritySettingRequest $request
    ): JsonResponse|RedirectResponse {
        $setting = $this->management->updateSecurity($request);

        if ($request->wantsJson()) {
            return response()->json($this->formatter->format($setting));
        }

        return redirect()->route('settings.security-policy');
    }
}
