<?php

use App\Models\CustomDashboardWidget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot access dashboard widget endpoints', function () {
    $this->getJson(route('dashboard.custom-widgets.metrics'))
        ->assertUnauthorized();

    $this->postJson(route('dashboard.custom-widgets.store'), [])
        ->assertUnauthorized();

    $this->getJson(route('dashboard.widgets.index'))
        ->assertUnauthorized();
});

test('an authenticated user can view available widget metrics', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('dashboard.custom-widgets.metrics'))
        ->assertOk()
        ->assertJsonStructure([
            'metrics' => [
                '*' => ['key', 'label', 'model'],
            ],
        ])
        ->assertJsonFragment([
            'key' => 'companies_count',
            'label' => 'Companies',
        ]);
});

test('an authenticated user can create a custom dashboard widget', function () {
    $user = User::factory()->create();
    CustomDashboardWidget::factory()
        ->for($user)
        ->create(['position' => 2]);

    $payload = [
        'label' => 'Open deals this month',
        'description' => 'Tracks new opportunities.',
        'metric_key' => 'deals_count',
        'date_range' => 'this_month',
    ];

    $this->actingAs($user)
        ->postJson(route('dashboard.custom-widgets.store'), $payload)
        ->assertCreated()
        ->assertJsonPath('label', $payload['label'])
        ->assertJsonPath('description', $payload['description'])
        ->assertJsonPath('metric_key', $payload['metric_key'])
        ->assertJsonPath('date_range', $payload['date_range'])
        ->assertJsonPath('position', 3)
        ->assertJsonPath('is_visible', true);

    $this->assertDatabaseHas('custom_dashboard_widgets', [
        'user_id' => $user->id,
        ...$payload,
        'position' => 3,
        'is_visible' => true,
    ]);
});

test('creating a custom dashboard widget validates its payload', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('dashboard.custom-widgets.store'), [
            'label' => '',
            'description' => str_repeat('x', 1001),
            'metric_key' => 'not-a-metric',
            'date_range' => 'not-a-date-range',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'label',
            'description',
            'metric_key',
            'date_range',
        ]);
});

test('a user can update only their own custom dashboard widget', function () {
    $owner = User::factory()->create();
    $widget = CustomDashboardWidget::factory()
        ->for($owner)
        ->create([
            'label' => 'Old label',
            'position' => 1,
            'is_visible' => true,
        ]);

    $this->actingAs($owner)
        ->putJson(route('dashboard.custom-widgets.update', $widget), [
            'label' => 'New label',
            'description' => null,
            'position' => 4,
            'is_visible' => false,
        ])
        ->assertOk()
        ->assertJsonPath('label', 'New label')
        ->assertJsonPath('description', null)
        ->assertJsonPath('position', 4)
        ->assertJsonPath('is_visible', false);
});
