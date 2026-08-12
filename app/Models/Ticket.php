<?php

namespace App\Models;

use App\Contracts\Auditable;
use App\Traits\Labelable;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property int|null $ticket_status_id
 * @property int|null $ticket_priority_id
 * @property int|null $assigned_to
 * @property Carbon|null $due_date
 * @property Carbon|null $resolved_at
 * @property array|null $meta
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property int|null $restored_by
 * @property Carbon|null $restored_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read TicketStatus|null $status
 * @property-read TicketPriority|null $priority
 * @property-read User|null $assignee
 * @property-read User|null $creator
 * @property-read User|null $updater
 * @property-read User|null $deleter
 * @property-read User|null $restorer
 */
#[Fillable([
    'title',
    'description',
    'ticket_status_id',
    'ticket_priority_id',
    'assigned_to',
    'due_date',
    'resolved_at',
    'meta',
    'created_by',
    'updated_by',
    'deleted_by',
    'restored_by',
    'restored_at',
])]
class Ticket extends Model implements Auditable
{
    /**
     * @use HasFactory<TicketFactory>
     */
    use HasFactory,
        Labelable,
        SoftDeletes;

    /**
     * Get the status of this ticket.
     *
     * @return BelongsTo<TicketStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(TicketStatus::class, 'ticket_status_id');
    }

    /**
     * Get the priority of this ticket.
     *
     * @return BelongsTo<TicketPriority, $this>
     */
    public function priority(): BelongsTo
    {
        return $this->belongsTo(TicketPriority::class, 'ticket_priority_id');
    }

    /**
     * Get the user this ticket is assigned to.
     *
     * @return BelongsTo<User, $this>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who created this ticket.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this ticket.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this ticket.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user who restored this ticket.
     *
     * @return BelongsTo<User, $this>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get a snapshot of the ticket's auditable attributes.
     *
     * Used by the audit log to capture before/after state on create,
     * update, delete and restore actions.
     *
     * @return array<string, mixed>
     */
    public function auditSnapshot(): array
    {
        return $this->only([
            'id',
            'title',
            'description',
            'ticket_status_id',
            'ticket_priority_id',
            'assigned_to',
            'due_date',
            'resolved_at',
            'meta',
        ]);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'resolved_at' => 'immutable_datetime',
            'meta' => 'array',
            'deleted_at' => 'datetime',
            'restored_at' => 'immutable_datetime',
        ];
    }
}
