<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait AuthorisesTokenAbility
{
    /**
     * Abort with a 403 if the request's token lacks the given ability.
     * Session/SPA requests carry a TransientToken and always pass.
     */
    protected function authoriseTokenAbility(Request $request, string $ability): void
    {
        if (! $request->user()->tokenCan($ability)) {
            throw new HttpException(403, "This token does not have the [{$ability}] ability.");
        }
    }
}
