<?php

use App\Models\Log;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('deletes log records older than 30 days by default', function () {
    $user = User::factory()->create();

    Log::insert([
        [
            'action_id' => 1,
            'data' => 'User logged in',
            'logged_in_user_id' => $user->id,
            'related_to_user_id' => null,
            'created_at' => Carbon::now()->subDays(45),
            'updated_at' => Carbon::now()->subDays(45),
        ],
        [
            'action_id' => 2,
            'data' => 'User updated profile',
            'logged_in_user_id' => $user->id,
            'related_to_user_id' => null,
            'created_at' => Carbon::now()->subDays(10),
            'updated_at' => Carbon::now()->subDays(10),
        ],
    ]);

    $this->artisan('logs:prune')
        ->expectsOutputToContain('Deleted 1 log record(s) older than 30 day(s).')
        ->assertExitCode(0);

    expect(Log::count())->toBe(1);
});

test('accepts a custom retention period via the --days option', function () {
    $user = User::factory()->create();

    Log::insert([
        [
            'action_id' => 3,
            'data' => 'System backup completed',
            'logged_in_user_id' => $user->id,
            'related_to_user_id' => null,
            'created_at' => Carbon::now()->subDays(8),
            'updated_at' => Carbon::now()->subDays(8),
        ],
    ]);

    $this->artisan('logs:prune', ['--days' => 7])
        ->assertExitCode(0);

    expect(Log::count())->toBe(0);
});