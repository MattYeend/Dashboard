<?php

namespace App\Models;

use App\Contracts\Auditable;
use Database\Factories\AttachmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $attachable_type
 * @property int $attachable_id
 * @property string $original_filename
 * @property string $disk_path
 * @property string $mime_type
 * @property int $size_bytes
 * @property array<string, mixed>|null $meta
 * @property Carbon|null $deleted_at
 * @property Carbon|null $restored_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Model $attachable
 */
#[Fillable([
    'attachable_type',
    'attachable_id',
    'original_filename',
    'disk_path',
    'mime_type',
    'size_bytes',
    'meta',
    'created_by',
    'created_at',
    'updated_by',
    'updated_at',
    'deleted_by',
    'deleted_at',
    'restored_by',
    'restored_at',
])]
class Attachment extends Model implements Auditable
{
    /**
     * @use HasFactory<AttachmentFactory>
     */
    use HasFactory, SoftDeletes;

    /**
     * The disk on which attachment files are stored.
     *
     * Deliberately private — never 'public'. Files are only ever
     * reachable via the authenticated, policy-checked download route.
     */
    public const DISK = 'attachments';

    /**
     * Get the parent attachable model (e.g. Company, Contact, Deal, Order).
     *
     * @return MorphTo<Model, $this>
     */
    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created this attachment.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this attachment.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this attachment.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user who restored this attachment.
     *
     * @return BelongsTo<User, $this>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get a snapshot of the attachment's auditable attributes.
     *
     * @return array<string, mixed>
     */
    public function auditSnapshot(): array
    {
        return $this->only([
            'id',
            'attachable_id',
            'attachable_type',
            'original_filename',
            'mime_type',
            'size_bytes',
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
            'meta' => 'array',
            'size_bytes' => 'integer',
            'deleted_at' => 'immutable_datetime',
            'restored_at' => 'immutable_datetime',
        ];
    }
}
