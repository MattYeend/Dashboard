<?php

namespace App\Services\Tickets;

use App\Models\Ticket;

class FormatterService
{
    /**
     * Format a single ticket with all data.
     *
     * @return array<string, mixed>
     */
    public function format(Ticket $ticket): array
    {
        return [
            'id' => $ticket->id,
            'title' => $ticket->title,
            'description' => $ticket->description,
            'due_date' => $ticket->due_date,
            'resolved_at' => $ticket->resolved_at,
            'meta' => $ticket->meta,
            'status' => $ticket->status ? [
                'id' => $ticket->status->id,
                'title' => $ticket->status->title,
            ] : null,
            'priority' => $ticket->priority ? [
                'id' => $ticket->priority->id,
                'title' => $ticket->priority->title,
            ] : null,
            'assignee' => $ticket->assignee ? [
                'id' => $ticket->assignee->id,
                'name' => $ticket->assignee->name,
            ] : null,
            'labels' => $ticket->relationLoaded('labels')
                ? $ticket->labels->map(fn ($label) => [
                    'id' => $label->id,
                    'name' => $label->name,
                ])->all()
                : [],
            'created_at' => $ticket->created_at,
            'updated_at' => $ticket->updated_at,
            'deleted_at' => $ticket->deleted_at,
            'restored_at' => $ticket->restored_at,
            'created_by' => $ticket->created_by,
            'updated_by' => $ticket->updated_by,
            'deleted_by' => $ticket->deleted_by,
            'restored_by' => $ticket->restored_by,
            'creator' => $ticket->creator ? ['id' => $ticket->creator->id, 'name' => $ticket->creator->name] : null,
            'updater' => $ticket->updater ? ['id' => $ticket->updater->id, 'name' => $ticket->updater->name] : null,
            'deleter' => $ticket->deleter ? ['id' => $ticket->deleter->id, 'name' => $ticket->deleter->name] : null,
            'restorer' => $ticket->restorer ? ['id' => $ticket->restorer->id, 'name' => $ticket->restorer->name] : null,
        ];
    }
}
