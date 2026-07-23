<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Authenticate a user via the API.
     *
     * Supports two flows from a single endpoint:
     * - Token clients (mobile/3rd party): pass 'device_name', receive a plain-text Sanctum token once.
     * - SPA clients: omit 'device_name', receive a stateful session cookie instead.
     *
     * Throws a validation exception on incorrect credentials rather than leaking which field was wrong.
     */
    public function login(
        Request $request
    ): JsonResponse {
        $credentials = $request->validate([
            'email' => [
                'required',
                'email',
            ],
            'password' => [
                'required',
            ],
            'device_name' => [
                'nullable',
                'string',
            ],
        ]);

        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => [
                    'The provided credentials are incorrect.',
                ],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        if ($request->filled('device_name')) {
            $token = $user->createToken($credentials['device_name'])->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
            ]);
        }

        $request->session()->regenerate();

        return response()->json(['user' => $user]);
    }

    /**
     * Log the authenticated user out.
     *
     * Revokes the current access token for token clients, and invalidates the session for SPA clients.
     *
     * Safe to call from either flow; only acts on whichever authentication method was actually used.
     */
    public function logout(
        Request $request
    ): JsonResponse {
        if ($token = $request->user()?->currentAccessToken()) {
            $token->delete();
        }

        Auth::guard('web')->logout();

        return response()->json([
            'message' => 'Logged out',
        ]);
    }

    /**
     * Return the currently authenticated user.
     *
     * Used by SPA and token clients alike to confirm identity and refresh local user state.
     */
    public function user(
        Request $request
    ): JsonResponse {
        return response()->json($request->user());
    }
}
