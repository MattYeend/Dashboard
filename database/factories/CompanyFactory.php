<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Industry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Company>
     */
    protected $model = Company::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'email' => $this->faker->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'website' => $this->faker->url(),
            'registration_number' => strtoupper($this->faker->bothify('#########')),
            'vat_number' => 'GB'.$this->faker->numerify('#########'),
            'description' => $this->faker->paragraph(),
            'industry_id' => null,
            'employee_count' => $this->faker->numberBetween(1, 5000),
            'founded_year' => $this->faker->numberBetween(1900, (int) date('Y')),
            'meta' => null,
        ];
    }

    /**
     * State for a company without a registered website.
     */
    public function withoutWebsite(): static
    {
        return $this->state(fn (array $attributes) => [
            'website' => null,
        ]);
    }

    /**
     * State for a soft-deleted company.
     */
    public function deleted(?User $deletedBy = null): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
            'deleted_by' => $deletedBy?->id,
        ]);
    }

    /**
     * Associate the company with a given industry.
     */
    public function forIndustry(Industry $industry): static
    {
        return $this->state(fn (array $attributes) => [
            'industry_id' => $industry->id,
        ]);
    }
}
