<?php

namespace App\Services\InteractionLogs;

use App\Models\InteractionLog;

class FormatterService
{
    /**
     * Format an interaction log into a flat associative array for Inertia/JSON output.
     *
     * @return array<string, mixed>
     */
    public function format(InteractionLog $interactionLog): array
    {
        return [
            'id' => $interactionLog->id,
            'interactable_type' => $interactionLog->interactable_type,
            'interactable_id' => $interactionLog->interactable_id,
            'type' => $interactionLog->type->value,
            'subject' => $interactionLog->subject,
            'outcome' => $interactionLog->outcome,
            'notes' => $interactionLog->notes,
            'occurred_at' => $interactionLog->occurred_at?->toIso8601String(),
            'contact' => $interactionLog->contact ? [
                'id' => $interactionLog->contact->id,
                'name' => $interactionLog->contact->name,
            ] : null,
            'created_at' => $interactionLog->created_at?->toIso8601String(),
            'updated_at' => $interactionLog->updated_at?->toIso8601String(),
            'creator' => $interactionLog->creator ? ['name' => $interactionLog->creator->name] : null,
            'updater' => $interactionLog->updater ? ['name' => $interactionLog->updater->name] : null,
        ];
    }
}
