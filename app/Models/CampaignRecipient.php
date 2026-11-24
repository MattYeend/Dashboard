<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Auditable;
use App\Traits\BelongsToOrganisation;
use Carbon\CarbonImmutable;
use Database\Factories\CampaignRecipientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $campaign_id
 * @property int|null $contact_id
 * @property string $email
 * @property string|null $name
 * @property string $status
 * @property string $token
 * @property int $attempts
 * @property Carbon|null $sent_at
 * @property Carbon|null $opened_at
 * @property Carbon|null $clicked_at
 * @property Carbon|null $unsubscribed_at
 * @property Carbon|null $failed_at
 * @property string|null $failure_reason
 * @property array<string, mixed>|null $meta
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property int|null $restored_by
 * @property CarbonImmutable|null $restored_at
 * @property CarbonImmutable|null $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Campaign|null $campaign
 * @property-read Contact|null $contact
 * @property-read User|null $creator
 * @property-read User|null $updater
 * @property-read User|null $deleter
 * @property-read User|null $restorer
 */
#[Fillable([
    'campaign_id',
    'contact_id',
    'email',
    'name',
    'status',
    'token',
    'attempts',
    'sent_at',
    'opened_at',
    'clicked_at',
    'unsubscribed_at',
    'failed_at',
    'failure_reason',
    'meta',
    'created_by',
    'created_at',
    'updated_by',
    'updated_at',
])]
class CampaignRecipient extends Model implements Auditable
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SKIPPED = 'skipped';

    public const STATUS_UNSUBSCRIBED = 'unsubscribed';

    /**
     * Allow-list of valid recipient statuses.
     *
     * @var array<int, string>
     */
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_SENT,
        self::STATUS_FAILED,
        self::STATUS_SKIPPED,
        self::STATUS_UNSUBSCRIBED,
    ];

    /**
     * @use HasFactory<CampaignRecipientFactory>
     */
    use BelongsToOrganisation,
        HasFactory,
        SoftDeletes;

    /**
     * The token is a credential and must never be serialised.
     *
     * @var array<int, string>
     */
    protected $hidden = ['token'];

    /**
     * Get the campaign.
     *
     * @return BelongsTo<Campaign, $this>
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    /**
     * Get the contact.
     *
     * @return BelongsTo<Contact, $this>
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    /**
     * Get the user who created this campaign recipient.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this campaign recipient.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this campaign recipient.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user who restored this campaign recipient.
     *
     * @return BelongsTo<User, $this>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get a snapshot of the campaign recipient's auditable attributes.
     *
     * Used by the audit log to capture before/after state on create,
     * update, delete and restore actions. The token is deliberately
     * excluded: it is a credential and must not be written to the audit trail.
     *
     * @return array<string, mixed>
     */
    public function auditSnapshot(): array
    {
        return $this->only([
            'id',
            'campaign_id',
            'contact_id',
            'email',
            'name',
            'status',
            'attempts',
            'sent_at',
            'opened_at',
            'clicked_at',
            'unsubscribed_at',
            'failed_at',
            'failure_reason',
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
            'sent_at' => 'immutable_datetime',
            'opened_at' => 'immutable_datetime',
            'clicked_at' => 'immutable_datetime',
            'unsubscribed_at' => 'immutable_datetime',
            'failed_at' => 'immutable_datetime',
            'meta' => 'array',
            'restored_at' => 'immutable_datetime',
            'deleted_at' => 'immutable_datetime',
        ];
    }
}