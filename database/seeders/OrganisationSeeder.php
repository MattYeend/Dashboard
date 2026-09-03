<?php

namespace Database\Seeders;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrganisationSeeder extends Seeder
{
    public function run(): void
    {
        if (Organisation::exists()) {
            $this->command->info('Organisations already seeded, skipping...');

            return;
        }

        $organisations = [
            'Yeend Web Development',
            'Bangor Digital Services',
            'Leicester Trade Supplies',
        ];

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

            // Attach every existing user to every seeded organisation so
            // local development has a working multi-organisation setup.
            $organisation->users()->attach($users->pluck('id'));
        }
    }
}
