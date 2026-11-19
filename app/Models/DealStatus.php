<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Auditable;
use App\Traits\BelongsToOrganisation;
use Carbon\CarbonImmutable;
use Database\Factories\DealStatusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property bool $is_closing
 * @property string|null $description
 * @property string $background_colour
 * @property string $text_colour
 * @property string|null $system_key
 * @property array<string, mixed>|null $meta
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property int|null $restored_by
 * @property CarbonImmutable|null $restored_at
 * @property CarbonImmutable|null $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User|null $creator
 * @property-read User|null $updater
 * @property-read User|null $deleter
 * @property-read User|null $restorer
 */
#[Fillable([
    'title',
    'is_closing',
    'description',
    'background_colour',
    'text_colour',
    'meta',
    'created_by',
    'created_at',
    'updated_by',
    'updated_at',
])]
class DealStatus extends Model implements Auditable
{
    /**
     * @use HasFactory<DealStatusFactory>
     */
    use BelongsToOrganisation,
        HasFactory,
        SoftDeletes;

    public const KEY_NEW = 'new';

    public const KEY_WON = 'won';

    public const KEY_LOST = 'lost';

    /**
     * The keys the Deals workflow depends on. Statuses carrying one of
     * these keys cannot be deleted or renamed.
     *
     * @var array<int, string>
     */
    public const SYSTEM_KEYS = [
        self::KEY_NEW,
        self::KEY_WON,
        self::KEY_LOST,
    ];

    /**
     * Resolve the ID of a system status for the current organisation.
     *
     * The BelongsToOrganisation global scope limits the lookup to the
     * current tenant. A missing row means the seeder has not been run,
     * which is a configuration fault, so it fails loudly.
     *
     * @throws \RuntimeException
     */
    public static function idForKey(string $key): int
    {
        $id = static::query()->where('system_key', $key)->value('id');

        if ($id === null) {
            throw new \RuntimeException("Deal status '{$key}' is missing. Run DealStatusSeeder.");
        }

        return (int) $id;
    }

    /**
     * Whether this status is a protected system status.
     */
    public function isSystem(): bool
    {
        return $this->system_key !== null;
    }

    /**
     * Get the deals with this status.
     *
     * @return HasMany<Deal, $this>
     */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'status_id');
    }

    /**
     * Get the pipeline stages that close a deal into this status.
     *
     * @return HasMany<PipelineStage, $this>
     */
    public function stages(): HasMany
    {
        return $this->hasMany(PipelineStage::class, 'deal_status_id');
    }

    /**
     * Get the user who created this deal status.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this deal status.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this deal status.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user who restored this deal status.
     *
     * @return BelongsTo<User, $this>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get a snapshot of the deal status's auditable attributes.
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
            'is_closing',
            'description',
            'background_colour',
            'text_colour',
            'system_key',
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
            'is_closing' => 'boolean',
            'meta' => 'array',
            'restored_at' => 'immutable_datetime',
            'deleted_at' => 'immutable_datetime',
        ];
    }
}
