<?php

namespace Database\Factories;

use App\Models\OrganisationDataExport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrganisationDataExport>
 */
class OrganisationDataExportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<OrganisationDataExport>
     */
    protected $model = OrganisationDataExport::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'requested_by' => User::factory(),
            'disk_path' => 'organisations/exports/'.fake()->uuid().'.zip',
            'completed_at' => now(),
        ];
    }
}
