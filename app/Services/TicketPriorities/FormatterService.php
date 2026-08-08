<?php

namespace App\Services\TicketPriorities;

use App\Models\TicketPriority;

class FormatterService
{
    /**
     * Format a single ticket priority with all data.
     *
     * @return array<string, mixed>
     */
    public function format(TicketPriority $ticketPriority): array
    {
        return [
            'id' => $ticketPriority->id,
            'title' => $ticketPriority->title,
            'level' => $ticketPriority->level,
            'background_colour' => $ticketPriority->background_colour,
            'text_colour' => $ticketPriority->text_colour,
            'meta' => $ticketPriority->meta,
            'created_at' => $ticketPriority->created_at,
            'updated_at' => $ticketPriority->updated_at,
            'deleted_at' => $ticketPriority->deleted_at,
            'restored_at' => $ticketPriority->restored_at,
            'created_by' => $ticketPriority->created_by,
            'updated_by' => $ticketPriority->updated_by,
            'deleted_by' => $ticketPriority->deleted_by,
            'restored_by' => $ticketPriority->restored_by,
            'creator' => $ticketPriority->creator ? ['id' => $ticketPriority->creator->id, 'name' => $ticketPriority->creator->name] : null,
            'updater' => $ticketPriority->updater ? ['id' => $ticketPriority->updater->id, 'name' => $ticketPriority->updater->name] : null,
            'deleter' => $ticketPriority->deleter ? ['id' => $ticketPriority->deleter->id, 'name' => $ticketPriority->deleter->name] : null,
            'restorer' => $ticketPriority->restorer ? ['id' => $ticketPriority->restorer->id, 'name' => $ticketPriority->restorer->name] : null,
        ];
    }
}
