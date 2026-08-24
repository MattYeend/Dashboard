<?php

namespace App\Services\Activities;

use App\Models\Activity;

class FormatterService
{
    public function __construct(
        private readonly ActivityableTypeRegistryService $registry,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function format(Activity $activity): array
    {
        return [
            'id' => $activity->id,
            'activityable_type' => $activity->activityable_type,
            'activityable_id' => $activity->activityable_id,
            'type' => $activity->type->value,
            'type_label' => $activity->type->label(),
            'description' => $activity->description,
            'meta' => $activity->meta,
            'occurred_at' => $activity->occurred_at,
            'created_at' => $activity->created_at,
            'deleted_at' => $activity->deleted_at,
            'restored_at' => $activity->restored_at,
            'creator' => $activity->creator ? ['id' => $activity->creator->id, 'name' => $activity->creator->name] : null,
            'updater' => $activity->updater ? ['id' => $activity->updater->id, 'name' => $activity->updater->name] : null,
            'deleter' => $activity->deleter ? ['id' => $activity->deleter->id, 'name' => $activity->deleter->name] : null,
            'restorer' => $activity->restorer ? ['id' => $activity->restorer->id, 'name' => $activity->restorer->name] : null,
            'activityable_type_label' => $activity->activityable_type
                ? ($this->registry->labelForModel($activity->activityable_type) ?? class_basename($activity->activityable_type))
                : null,
            'activityable_type_key' => $this->registry->keyForModel($activity->activityable_type),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function forExport(Activity $activity): array
    {
        return [
            'ID' => $activity->id,
            'Type' => $activity->type->label(),
            'Description' => $activity->description,
            'Occurred at' => $activity->occurred_at?->format('d/m/Y H:i'),
            'Logged by' => $activity->creator?->name ?? 'System',
        ];
    }
}
