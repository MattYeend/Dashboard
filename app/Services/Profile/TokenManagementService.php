<?php

namespace App\Services\Profile;

use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class TokenManagementService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

    /**
     * List the user's tokens. The token hash and plain text are never returned.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function listFor(User $user): Collection
    {
        return $user->tokens()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($token): array => [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities,
                'last_used_at' => $token->last_used_at?->toIso8601String(),
                'expires_at' => $token->expires_at?->toIso8601String(),
                'created_at' => $token->created_at?->toIso8601String(),
            ]);
    }

    /**
     * Find one of the user's own tokens by id. Scoped to the user, so a
     * token id belonging to someone else simply matches nothing.
     */
    public function find(User $user, int $tokenId): ?PersonalAccessToken
    {
        return $user->tokens()->whereKey($tokenId)->first();
    }

    /**
     * Create a token and return its plain text value, shown once only.
     *
     * @param  array<int, string>  $abilities
     *
     * @throws ValidationException
     */
    public function create(
        User $user, 
        string $name, 
        array $abilities, 
        int $expiresInDays
    ): string {
        if ($user->tokens()->count() >= (int) config('profile.max_tokens_per_user', 10)) {
            throw ValidationException::withMessages([
                'name' => 'You have reached the maximum number of tokens. Revoke one first.',
            ]);
        }

        $allowed = array_values(array_intersect($abilities, config('profile.token_abilities', [])));

        $token = $user->createToken($name, $allowed, now()->addDays($expiresInDays));

        $this->auditLogService->record(
            Log::ACTION_CREATE_API_TOKEN, 
            $user, 
            $user, 
            [
                'after' => [
                    'token_id' => $token->accessToken->id,
                    'name' => $name,
                    'abilities' => $allowed,
                    'expires_in_days' => $expiresInDays,
                ],
            ]);

        return $token->plainTextToken;
    }

    /**
     * Revoke one of the user's own tokens.
     */
    public function revoke(User $user, PersonalAccessToken $token): void
    {
        $tokenId = $token->id;

        $token->delete();

        $this->auditLogService->record(
            Log::ACTION_REVOKE_API_TOKEN, 
            $user, 
            $user, 
            [
                'before' => ['token_id' => $tokenId],
            ]);
    }
}
