<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait AuthorisesTokenAbility
{
    /**
     * Authorise the current request against a required Sanctum token ability.
     *
     * Aborts with a 403 response if the authenticated request's token does not carry
     * the given ability. Session/SPA requests are authenticated via Sanctum's
     * TransientToken, whose tokenCan() always returns true, so this check only ever
     * restricts genuine personal access tokens, never session/SPA users.
     *
     * Intended to be called alongside (not instead of) policy authorisation via
     * $this->authorize(), so both ownership/role checks and token scope must pass.
     */
    protected function authoriseTokenAbility(
        Request $request,
        string $ability
    ): void {
        if (! $request->user()->tokenCan($ability)) {
            throw new HttpException(403, "This token does not have the [{$ability}] ability.");
        }
    }
}
