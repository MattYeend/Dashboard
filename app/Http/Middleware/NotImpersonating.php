<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NotImpersonating
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Replace with the same check ShareImpersonationStatus uses
        if ($request->session()->has('impersonator_id')) {
            abort(403, 'This action is not available while impersonating a user.');
        }

        return $next($request);
    }
}
