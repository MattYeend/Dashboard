<?php

namespace Database\Factories;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Attachment>
 */
class AttachmentFactory extends Factory
{
    /**
     * @var class-string<Attachment>
     */
    protected $model = Attachment::class;

    public function definition(): array
    {
        return [
            'attachable_type' => null,
            'attachable_id' => null,
            'original_filename' => 'document.pdf',
            'disk_path' => sprintf('%s.pdf', $this->faker->uuid()),
            'mime_type' => 'application/pdf',
            'size_bytes' => 102400,
            'meta' => null,
        ];
    }

    /**
     * State for a soft-deleted attachment.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }

    /**
     * Associate the attachment with a given morphable model.
     */
    public function forModel(Model $model): static
    {
        return $this->state(fn (array $attributes) => [
            'attachable_type' => $model->getMorphClass(),
            'attachable_id' => $model->getKey(),
        ]);
    }
}
