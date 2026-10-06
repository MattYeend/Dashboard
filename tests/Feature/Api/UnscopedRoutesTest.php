<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Api\Concerns\InteractsWithTenants;
use Tests\TestCase;

class UnscopedRoutesTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithTenants;

    /**
     * @return array<string, array{0: string}>
     */
    public static function authModes(): array
    {
        return [
            'bearer' => ['bearer'],
            'spa' => ['spa'],
        ];
    }

    #[DataProvider('authModes')]
    public function test_user_endpoint_does_not_require_an_organisation(string $mode): void
    {
        $user = User::factory()->create();

        $this->authenticateAs($user, null, $mode)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('id', $user->id);
    }

    #[DataProvider('authModes')]
    public function test_logout_endpoint_does_not_require_an_organisation(string $mode): void
    {
        $user = User::factory()->create();

        $this->authenticateAs($user, null, $mode)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJson(['message' => 'Logged out']);

        if ($mode === 'bearer') {
            $this->assertDatabaseCount('personal_access_tokens', 0);
        }
    }
}
