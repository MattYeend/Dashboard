<?php

namespace App\Http\Middleware;

use App\Services\Impersonation\ManagementService;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shares impersonation state with every Inertia response, so the
 * banner can render regardless of which page is being viewed.
 */
class ShareImpersonationStatus
{
    public function __construct(
        private readonly ManagementService $impersonationService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        Inertia::share([
            'isImpersonating' => fn () => $this->impersonationService->isImpersonating(),
            'impersonatorName' => fn () => $this->impersonationService->originalActor()?->name,
        ]);

        return $next($request);
    }
}
