<?php

namespace App\Http\Middleware;

use App\Services\Impersonation\ManagementService;
use Closure;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class EnforceImpersonationLimit
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
        if (! $this->impersonationService->isImpersonating()
            || ! $this->impersonationService->hasExpired()) {
            return $next($request);
        }

        try {
            $this->impersonationService->stop($request->user());
        } catch (RuntimeException) {
            return redirect()->route('login');
        }

        return redirect()
            ->route('users.index')
            ->with('error', 'The impersonation session expired.');
    }
}
