<?php

namespace App\Models;

use App\Contracts\Auditable;
use App\Enums\InteractionLogType;
use Database\Factories\InteractionLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $interactable_id
 * @property string $interactable_type
 * @property InteractionLogType $type
 * @property string $subject
 * @property string|null $outcome
 * @property string|null $notes
 * @property Carbon $occurred_at
 * @property int|null $contact_id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property int|null $restored_by
 * @property Carbon|null $restored_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Model $interactable
 * @property-read Contact|null $contact
 * @property-read User|null $creator
 * @property-read User|null $updater
 * @property-read User|null $deleter
 * @property-read User|null $restorer
 */
#[Fillable([
    'interactable_id',
    'interactable_type',
    'type',
    'subject',
    'outcome',
    'notes',
    'occurred_at',
    'contact_id',
    'created_by',
    'updated_by',
    'deleted_by',
    'restored_by',
    'restored_at',
])]
class InteractionLog extends Model implements Auditable
{
    /**
     * @use HasFactory<InteractionLogFactory>
     */
    use HasFactory;

    use SoftDeletes;

    /**
     * Get the parent interactable model (Company, Contact, or Deal).
     */
    public function interactable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the contact this interaction was logged against, if any.
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Get the user who created the interaction log.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the interaction log.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the interaction log.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user who restored the interaction log.
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get a snapshot of the model's auditable attributes.
     */
    public function auditSnapshot(): array
    {
        return $this->only([
            'id',
            'interactable_id',
            'interactable_type',
            'type',
            'subject',
            'outcome',
            'notes',
            'occurred_at',
            'contact_id',
        ]);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'type' => InteractionLogType::class,
            'occurred_at' => 'immutable_datetime',
            'restored_at' => 'immutable_datetime',
        ];
    }
}
