<?php

namespace App\Models;

use App\Contracts\Auditable;
use Database\Factories\ReportFactory;
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
 * @property string $type
 * @property string $format
 * @property array<string, mixed>|null $filters
 * @property bool $is_scheduled
 * @property string|null $schedule_frequency
 * @property string|null $schedule_time
 * @property array<int, string>|null $recipients
 * @property Carbon|null $last_run_at
 * @property Carbon|null $next_run_at
 * @property array<string, mixed>|null $meta
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property int|null $restored_by
 * @property Carbon|null $restored_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User|null $creator
 * @property-read User|null $updater
 * @property-read User|null $deleter
 * @property-read User|null $restorer
 */
#[Fillable([
    'title',
    'description',
    'type',
    'format',
    'filters',
    'is_scheduled',
    'schedule_frequency',
    'schedule_time',
    'recipients',
    'last_run_at',
    'next_run_at',
    'meta',
    'created_by',
    'updated_by',
    'deleted_by',
    'restored_by',
    'restored_at',
])]
class Report extends Model implements Auditable
{
    /**
     * @use HasFactory<ReportFactory>
     */
    use HasFactory, SoftDeletes;

    /**
     * Get the user who created this report.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this report.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this report.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user who restored this report.
     *
     * @return BelongsTo<User, $this>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get a snapshot of the report's auditable attributes.
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
            'type',
            'format',
            'filters',
            'is_scheduled',
            'schedule_frequency',
            'schedule_time',
            'recipients',
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
            'filters' => 'array',
            'recipients' => 'array',
            'meta' => 'array',
            'is_scheduled' => 'boolean',
            'last_run_at' => 'immutable_datetime',
            'next_run_at' => 'immutable_datetime',
            'restored_at' => 'immutable_datetime',
        ];
    }
}
