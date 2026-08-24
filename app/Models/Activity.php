<?php

namespace App\Models;

use App\Contracts\Auditable;
use App\Enums\ActivityType;
use Database\Factories\ActivityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $activityable_id
 * @property string $activityable_type
 * @property ActivityType $type
 * @property string|null $description
 * @property array|null $meta
 * @property Carbon $occurred_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property int|null $restored_by
 * @property Carbon|null $restored_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Model $activityable
 * @property-read User|null $creator
 * @property-read User|null $updater
 * @property-read User|null $deleter
 * @property-read User|null $restorer
 */
#[Fillable([
    'activityable_id',
    'activityable_type',
    'type',
    'description',
    'meta',
    'occurred_at',
    'created_by',
    'updated_by',
    'deleted_by',
    'restored_by',
    'restored_at',
])]
class Activity extends Model implements Auditable
{
    /**
     * @use HasFactory<ActivityFactory>
     */
    use HasFactory;

    use SoftDeletes;

    /**
     * Get the parent activityable model (Company, Contact, Deal, or Order).
     */
    public function activityable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created the activity.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the activity.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the activity.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user who restored the activity.
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
            'activityable_id',
            'activityable_type',
            'type',
            'description',
            'meta',
            'occurred_at',
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
            'type' => ActivityType::class,
            'meta' => 'array',
            'occurred_at' => 'immutable_datetime',
            'restored_at' => 'immutable_datetime',
        ];
    }
}
