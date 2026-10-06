<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Api\Concerns\InteractsWithTenants;
use Tests\TestCase;

class MissingOrganisationTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithTenants;

    /**
     * @return iterable<string, array{0: string, 1: string, 2: bool}>
     */
    public static function scenarios(): iterable
    {
        foreach (['tasks', 'companies', 'orders', 'contacts'] as $segment) {
            foreach (['bearer', 'spa'] as $mode) {
                foreach ([true, false] as $wantsJson) {
                    $accept = $wantsJson ? 'with Accept: json' : 'without Accept header';

                    yield "{$segment} via {$mode} {$accept}" => [$segment, $mode, $wantsJson];
                }
            }
        }
    }

    #[DataProvider('scenarios')]
    public function test_user_without_an_organisation_receives_a_json_conflict(
        string $segment,
        string $mode,
        bool $wantsJson,
    ): void {
        $user = User::factory()->create();
        $client = $this->authenticateAs($user, null, $mode);

        $requests = [
            ['GET', "/api/{$segment}"],
            ['POST', "/api/{$segment}"],
            ['GET', "/api/{$segment}/1"],
            ['PUT', "/api/{$segment}/1"],
            ['DELETE', "/api/{$segment}/1"],
        ];

        foreach ($requests as [$method, $uri]) {
            $response = $wantsJson
                ? $client->json($method, $uri)
                : $client->{strtolower($method)}($uri);

            $response
                ->assertStatus(409)
                ->assertHeaderMissing('Location')
                ->assertExactJson(['message' => 'No organisation is currently selected.']);
        }
    }
}
