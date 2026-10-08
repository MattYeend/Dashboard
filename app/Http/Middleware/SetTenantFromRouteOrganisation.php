<?php

namespace App\Http\Middleware;

use App\Models\Organisation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantFromRouteOrganisation
{
    /**
     * Make the organisation named in the route the current tenant for the
     * duration of the request, once the user is confirmed to belong to it.
     *
     * Runs before route model binding (see bootstrap/app.php), so the
     * organisation is resolved here and written back to the route, and any
     * tenant-scoped bindings further along resolve inside that tenant.
     *
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();
        $organisation = $route->parameter('organisation');

        if (! $organisation instanceof Organisation) {
            $organisation = Organisation::query()->findOrFail($organisation);
            $route->setParameter('organisation', $organisation);
        }

        // Membership only (active member of an active organisation). The
        // 'view' ability is avoided on purpose: it checks the team-scoped
        // 'view organisations' permission, which cannot be evaluated until
        // the tenant (and so the permissions team, see
        // SwitchPermissionsTeamTask) has been switched.
        // 404 rather than 403, so organisation IDs cannot be enumerated.
        abort_unless($request->user()?->can('switch', $organisation), 404);

        $previous = Organisation::current();
        $organisation->makeCurrent();

        try {
            return $next($request);
        } finally {
            if ($previous !== null) {
                $previous->makeCurrent();
            } else {
                Organisation::forgetCurrent();
            }
        }
    }
}
