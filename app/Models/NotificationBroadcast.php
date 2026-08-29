<?php

namespace App\Models;

use App\Contracts\Auditable;
use Database\Factories\NotificationBroadcastFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $body
 * @property string $audience_type
 * @property array|null $audience_ids
 * @property Carbon|null $sent_at
 * @property int|null $sent_by
 * @property array|null $meta
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property int|null $restored_by
 * @property Carbon|null $restored_at
 * @property Carbon|null $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User|null $sender
 * @property-read User|null $creator
 * @property-read User|null $updater
 * @property-read User|null $deleter
 * @property-read User|null $restorer
 */
#[Fillable([
    'title',
    'body',
    'audience_type',
    'audience_ids',
    'sent_at',
    'sent_by',
    'meta',
    'created_by',
    'created_at',
    'updated_by',
    'updated_at',
    'deleted_by',
    'restored_by',
    'restored_at',
    'deleted_at',
])]
class NotificationBroadcast extends Model implements Auditable
{
    /**
     * @use HasFactory<NotificationBroadcastFactory>
     */
    use HasFactory,
        SoftDeletes;

    /**
     * Get the user who triggered the send action, if the broadcast has been sent.
     *
     * @return BelongsTo<User, $this>
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    /**
     * Get the user who created this notification broadcast.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this notification broadcast.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this notification broadcast.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user who restored this notification broadcast.
     *
     * @return BelongsTo<User, $this>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get a snapshot of the notification broadcast's auditable attributes.
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
            'body',
            'audience_type',
            'audience_ids',
            'sent_at',
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
            'audience_ids' => 'array',
            'meta' => 'array',
            'sent_at' => 'immutable_datetime',
            'restored_at' => 'immutable_datetime',
            'deleted_at' => 'immutable_datetime',
        ];
    }
}
