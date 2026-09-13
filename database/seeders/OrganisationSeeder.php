<?php

namespace Database\Seeders;

use App\Models\Organisation;
use App\Models\OrganisationMembership;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrganisationSeeder extends Seeder
{
    public function run(): void
    {
        $organisations = [
            'Yeend Web Development',
            'Bangor Digital Services',
            'Leicester Trade Supplies',
        ];

        if (Organisation::whereIn('slug', array_map(Str::slug(...), $organisations))->exists()) {
            $this->command->info('Organisations already seeded, skipping...');

            return;
        }

        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found, skipping organisation seeding...');

            return;
        }

        foreach ($organisations as $name) {
            $organisation = Organisation::create([
                'name' => $name,
                'slug' => Str::slug($name),
            ]);

            $organisation->users()->attach(
                $users->mapWithKeys(fn (User $user, int $index) => [
                    $user->id => [
                        'status' => OrganisationMembership::STATUS_ACTIVE,
                        'role' => $index === 0 ? 'owner' : 'member',
                        'joined_at' => now(),
                    ],
                ])->all()
            );
        }
    }
}
