<?php

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Concerns\CreatesUsers;

uses(
    LazilyRefreshDatabase::class,
    CreatesUsers::class,
);

beforeEach(function () {
    setPermissionsTeamId(1);

    Role::firstOrCreate(['name' => 'Admin']);
    Role::firstOrCreate(['name' => 'Super Admin']);
    Role::firstOrCreate(['name' => 'User']);
});

describe('index', function () {
    test('authenticated user can search across modules', function () {
        $superAdmin = $this->superAdminUser();

        $company = Company::factory()->create([
            'name' => 'Acme Search Target',
        ]);

        $this->actingAs($superAdmin)
            ->getJson('/search?q=Acme')
            ->assertOk()
            ->assertJsonPath('companies.0.id', $company->id);
    });

    test('unauthenticated user cannot search', function () {
        $this->getJson('/search?q=Acme')
            ->assertUnauthorized();
    });

    test('user without company permission receives no company results', function () {
        $user = $this->userWithNoPermissions();

        Company::factory()->create([
            'name' => 'Acme Search Target',
        ]);

        $this->actingAs($user)
            ->getJson('/search?q=Acme')
            ->assertOk()
            ->assertJsonPath('companies', []);
    });

    test('user with company permission receives company results', function () {
        $role = Role::create([
            'name' => 'company-viewer',
        ]);

        $role->givePermissionTo('view any companies');

        $user = User::factory()->create();
        $user->assignRole($role);

        $company = Company::factory()->create([
            'name' => 'Acme Search Target',
        ]);

        $this->actingAs($user)
            ->getJson('/search?q=Acme')
            ->assertOk()
            ->assertJsonPath('companies.0.id', $company->id);
    });

    test('does not search when query is shorter than two characters', function () {
        $user = $this->superAdminUser();

        $this->actingAs($user)
            ->getJson('/search?q=a')
            ->assertOk()
            ->assertJson([
                'users' => [],
                'companies' => [],
                'contacts' => [],
                'orders' => [],
                'deals' => [],
            ]);
    });

    test('returns empty results when query is omitted', function () {
        $user = $this->superAdminUser();

        $this->actingAs($user)
            ->getJson('/search')
            ->assertOk()
            ->assertJson([
                'users' => [],
                'companies' => [],
                'contacts' => [],
                'orders' => [],
                'deals' => [],
            ]);
    });

    test('trims whitespace from the search query', function () {
        $user = $this->superAdminUser();

        $company = Company::factory()->create([
            'name' => 'Acme Search Target',
        ]);

        $this->actingAs($user)
            ->getJson('/search?q=%20Acme%20')
            ->assertOk()
            ->assertJsonPath('companies.0.id', $company->id);
    });

    test('search query cannot exceed 100 characters', function () {
        $user = $this->superAdminUser();

        $this->actingAs($user)
            ->getJson('/search?q='.str_repeat('a', 101))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['q']);
    });

    test('search query must be a string', function () {
        $user = $this->superAdminUser();

        $this->actingAs($user)
            ->getJson('/search?q[]=Acme')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['q']);
    });
});

describe('users', function () {
    test('returns matching users when actor has permission', function () {
        $user = $this->superAdminUser();

        $target = User::factory()->create([
            'name' => 'Acme User',
        ]);

        $this->actingAs($user)
            ->getJson('/search?q=Acme')
            ->assertOk()
            ->assertJsonPath('users.0.id', $target->id)
            ->assertJsonPath('users.0.label', 'Acme User');
    });

    test('does not return users when actor lacks permission', function () {
        $user = $this->userWithNoPermissions();

        User::factory()->create([
            'name' => 'Acme User',
        ]);

        $this->actingAs($user)
            ->getJson('/search?q=Acme')
            ->assertOk()
            ->assertJsonPath('users', []);
    });
});

describe('companies', function () {
    test('returns matching companies when actor has permission', function () {
        $user = $this->superAdminUser();

        $company = Company::factory()->create([
            'name' => 'Acme Company',
        ]);

        $this->actingAs($user)
            ->getJson('/search?q=Acme')
            ->assertOk()
            ->assertJsonPath('companies.0.id', $company->id)
            ->assertJsonPath('companies.0.label', 'Acme Company');
    });

    test('does not return companies when actor lacks permission', function () {
        $user = $this->userWithNoPermissions();

        Company::factory()->create([
            'name' => 'Acme Company',
        ]);

        $this->actingAs($user)
            ->getJson('/search?q=Acme')
            ->assertOk()
            ->assertJsonPath('companies', []);
    });
});

describe('contacts', function () {
    test('returns matching contacts when actor has permission', function () {
        $user = $this->superAdminUser();

        $contact = Contact::factory()->create([
            'email' => 'acme@example.com',
        ]);

        $this->actingAs($user)
            ->getJson('/search?q=acme@example.com')
            ->assertOk()
            ->assertJsonPath('contacts.0.id', $contact->id);
    });

    test('does not return contacts when actor lacks permission', function () {
        $user = $this->userWithNoPermissions();

        Contact::factory()->create([
            'email' => 'acme@example.com',
        ]);

        $this->actingAs($user)
            ->getJson('/search?q=acme@example.com')
            ->assertOk()
            ->assertJsonPath('contacts', []);
    });
});

describe('orders', function () {
    test('returns matching orders when actor has permission', function () {
        $user = $this->superAdminUser();

        $order = Order::factory()->create([
            'order_number' => 'ACME-1001',
        ]);

        $this->actingAs($user)
            ->getJson('/search?q=ACME-1001')
            ->assertOk()
            ->assertJsonPath('orders.0.id', $order->id)
            ->assertJsonPath('orders.0.label', 'ACME-1001');
    });

    test('does not return orders when actor lacks permission', function () {
        $user = $this->userWithNoPermissions();

        Order::factory()->create([
            'order_number' => 'ACME-1001',
        ]);

        $this->actingAs($user)
            ->getJson('/search?q=ACME-1001')
            ->assertOk()
            ->assertJsonPath('orders', []);
    });
});

describe('deals', function () {
    test('returns matching deals when actor has permission', function () {
        $user = $this->superAdminUser();

        $deal = Deal::factory()->create([
            'title' => 'Acme Deal',
        ]);

        $this->actingAs($user)
            ->getJson('/search?q=Acme')
            ->assertOk()
            ->assertJsonPath('deals.0.id', $deal->id)
            ->assertJsonPath('deals.0.label', 'Acme Deal');
    });

    test('does not return deals when actor lacks permission', function () {
        $user = $this->userWithNoPermissions();

        Deal::factory()->create([
            'title' => 'Acme Deal',
        ]);

        $this->actingAs($user)
            ->getJson('/search?q=Acme')
            ->assertOk()
            ->assertJsonPath('deals', []);
    });
});

describe('result limits', function () {
    test('limits results to five per module', function () {
        $user = $this->superAdminUser();

        Company::factory()
            ->count(10)
            ->create([
                'name' => 'Acme Company',
            ]);

        $response = $this->actingAs($user)
            ->getJson('/search?q=Acme')
            ->assertOk();

        expect($response->json('companies'))
            ->toHaveCount(5);
    });
});
