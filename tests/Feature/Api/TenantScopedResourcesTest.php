<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Order;
use App\Models\Organisation;
use App\Models\Task;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Api\Concerns\InteractsWithTenants;
use Tests\TestCase;

class TenantScopedResourcesTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithTenants;

    private const MARKER = 'Created through the API';

    private const MARKER_COLUMNS = [
        'tasks' => 'title',
        'companies' => 'name',
        'orders' => 'title',
        'contacts' => 'name',
    ];

    /**
     * @return iterable<string, array{0: string, 1: string}>
     */
    public static function resourcesByAuthMode(): iterable
    {
        foreach (['tasks', 'companies', 'orders', 'contacts'] as $segment) {
            foreach (['bearer', 'spa'] as $mode) {
                yield "{$segment} via {$mode}" => [$segment, $mode];
            }
        }
    }

    #[DataProvider('resourcesByAuthMode')]
    public function test_index_returns_only_the_active_organisations_records(
        string $segment,
        string $mode,
    ): void {
        $own = $this->makeOrganisation();
        $other = $this->makeOrganisation();
        $user = $this->makeMember($own);

        $this->makeRecord($segment, $own);
        $this->makeRecord($segment, $own);
        $this->makeRecord($segment, $other);
        $this->makeRecord($segment, $other);
        $this->makeRecord($segment, $other);

        $this->authenticateAs($user, $own, $mode)
            ->getJson("/api/{$segment}")
            ->assertOk()
            ->assertJsonPath("{$segment}.meta.total", 2);
    }

    #[DataProvider('resourcesByAuthMode')]
    public function test_member_can_read_a_record_in_their_organisation(
        string $segment,
        string $mode,
    ): void {
        $own = $this->makeOrganisation();
        $user = $this->makeMember($own);
        $record = $this->makeRecord($segment, $own);

        $this->authenticateAs($user, $own, $mode)
            ->getJson("/api/{$segment}/{$record->getKey()}")
            ->assertOk();
    }

    #[DataProvider('resourcesByAuthMode')]
    public function test_store_creates_in_the_active_organisation_and_ignores_a_foreign_organisation_id(
        string $segment,
        string $mode,
    ): void {
        $own = $this->makeOrganisation();
        $other = $this->makeOrganisation();
        $user = $this->makeMember($own);

        $this->authenticateAs($user, $own, $mode)
            ->postJson("/api/{$segment}", $this->payloadFor($segment, $own) + [
                'organisation_id' => $other->id,
            ])
            ->assertSuccessful();

        $this->assertDatabaseHas($segment, [
            self::MARKER_COLUMNS[$segment] => self::MARKER,
            'organisation_id' => $own->id,
        ]);
        $this->assertDatabaseMissing($segment, ['organisation_id' => $other->id]);
    }

    #[DataProvider('resourcesByAuthMode')]
    public function test_another_organisations_record_cannot_be_read_or_changed(
        string $segment,
        string $mode,
    ): void {
        $own = $this->makeOrganisation();
        $other = $this->makeOrganisation();
        $user = $this->makeMember($own);
        $record = $this->makeRecord($segment, $other);
        $id = $record->getKey();

        $client = $this->authenticateAs($user, $own, $mode);

        $client->getJson("/api/{$segment}/{$id}")->assertNotFound();
        $client->putJson("/api/{$segment}/{$id}", [])->assertNotFound();
        $client->deleteJson("/api/{$segment}/{$id}")->assertNotFound();

        $this->assertDatabaseHas($record->getTable(), [
            'id' => $id,
            'organisation_id' => $other->id,
        ]);
        $this->assertNotSoftDeleted($record->getTable(), ['id' => $id]);
    }

    /**
     * Create one record inside the organisation.
     *
     * Contacts and orders need an owner, so a company is created in the
     * same organisation first and attached with forModel().
     */
    private function makeRecord(string $segment, Organisation $organisation): Model
    {
        return $organisation->execute(fn (): Model => match ($segment) {
            'tasks' => Task::factory()->create(),
            'companies' => Company::factory()->create(),
            'contacts' => Contact::factory()
                ->forModel(Company::factory()->create())
                ->create(),
            'orders' => Order::factory()
                ->forModel(Company::factory()->create())
                ->create(),
        });
    }

    /**
     * Build a minimal valid creation payload for the resource.
     *
     * @return array<string, mixed>
     */
    private function payloadFor(string $segment, Organisation $organisation): array
    {
        $companyId = fn (): int => $organisation
            ->execute(fn () => Company::factory()->create())
            ->id;

        return match ($segment) {
            'tasks' => ['title' => self::MARKER],
            'companies' => ['name' => self::MARKER],
            'contacts' => [
                'contactable_type' => 'company',
                'contactable_id' => $companyId(),
                'name' => self::MARKER,
            ],
            'orders' => [
                'orderable_type' => 'company',
                'orderable_id' => $companyId(),
                'title' => self::MARKER,
                'subtotal' => 100,
                'total_amount' => 120,
                'currency' => 'GBP',
            ],
        };
    }
}
