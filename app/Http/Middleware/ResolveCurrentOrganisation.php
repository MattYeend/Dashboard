<?php

namespace App\Http\Middleware;

use App\Multitenancy\SessionOrganisationFinder;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveCurrentOrganisation
{
    /**
     * @param  SessionOrganisationFinder  $organisationFinder  Resolves the current organisation from the session.
     */
    public function __construct(
        protected SessionOrganisationFinder $organisationFinder,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $organisation = $this->organisationFinder->findForRequest($request);

        if (! $organisation) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'No organisation is currently selected.',
                ], Response::HTTP_CONFLICT);
            }

            return redirect()->route('organisations.select');
        }

        $organisation->makeCurrent();

        return $next($request);
    }
}
