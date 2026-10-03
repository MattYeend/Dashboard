<?php

namespace App\Http\Middleware;

use App\Services\Impersonation\ManagementService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NotImpersonating
{
    public function __construct(
        private readonly ManagementService $impersonationService,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->impersonationService->isImpersonating()) {
            abort(403, 'This action is not available while impersonating a user.');
        }

        return $next($request);
    }
}
