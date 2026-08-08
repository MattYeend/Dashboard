<?php

namespace App\Services\TicketStatuses;

use App\Models\TicketStatus;

class FormatterService
{
    /**
     * Format a single task status with all data.
     *
     * @return array<string, mixed>
     */
    public function format(TicketStatus $ticketStatus): array
    {
        return [
            'id' => $ticketStatus->id,
            'title' => $ticketStatus->title,
            'description' => $ticketStatus->description,
            'background_colour' => $ticketStatus->background_colour,
            'text_colour' => $ticketStatus->text_colour,
            'meta' => $ticketStatus->meta,
            'created_at' => $ticketStatus->created_at,
            'updated_at' => $ticketStatus->updated_at,
            'deleted_at' => $ticketStatus->deleted_at,
            'restored_at' => $ticketStatus->restored_at,
            'created_by' => $ticketStatus->created_by,
            'updated_by' => $ticketStatus->updated_by,
            'deleted_by' => $ticketStatus->deleted_by,
            'restored_by' => $ticketStatus->restored_by,
            'creator' => $ticketStatus->creator ? ['id' => $ticketStatus->creator->id, 'name' => $ticketStatus->creator->name] : null,
            'updater' => $ticketStatus->updater ? ['id' => $ticketStatus->updater->id, 'name' => $ticketStatus->updater->name] : null,
            'deleter' => $ticketStatus->deleter ? ['id' => $ticketStatus->deleter->id, 'name' => $ticketStatus->deleter->name] : null,
            'restorer' => $ticketStatus->restorer ? ['id' => $ticketStatus->restorer->id, 'name' => $ticketStatus->restorer->name] : null,
        ];
    }
}
