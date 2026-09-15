<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PlatformAdmin
{
    /**
     * Reject any request from a non-platform-admin user.
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(
            $request->user()?->is_platform_admin === true,
            403,
        );

        return $next($request);
    }
}
