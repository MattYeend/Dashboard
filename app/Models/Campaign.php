<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Auditable;
use App\Traits\BelongsToOrganisation;
use Carbon\CarbonImmutable;
use Database\Factories\CampaignFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property int $email_template_id
 * @property int $campaign_segment_id
 * @property int|null $owner_id
 * @property string|null $subject
 * @property string|null $from_name
 * @property string|null $reply_to
 * @property Carbon|null $scheduled_at
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $cancelled_at
 * @property string|null $rendered_subject
 * @property string|null $rendered_body_html
 * @property string|null $rendered_body_text
 * @property int|null $recipients_count
 * @property int|null $sent_count
 * @property int|null $failed_count
 * @property int|null $skipped_count
 * @property int|null $opened_count
 * @property int|null $unsubscribed_count
 * @property int|null $status_id
 * @property array<string, mixed>|null $meta
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property int|null $restored_by
 * @property CarbonImmutable|null $restored_at
 * @property CarbonImmutable|null $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read EmailTemplate|null $template
 * @property-read CampaignSegment|null $segment
 * @property-read User|null $owner
 * @property-read User|null $creator
 * @property-read User|null $updater
 * @property-read User|null $deleter
 * @property-read User|null $restorer
 */
#[Fillable([
    'name',
    'email_template_id',
    'campaign_segment_id',
    'owner_id',
    'subject',
    'from_name',
    'reply_to',
    'scheduled_at',
    'meta',
    'created_by',
    'created_at',
    'updated_by',
    'updated_at',
])]
class Campaign extends Model implements Auditable
{
    /**
     * @use HasFactory<CampaignFactory>
     */
    use BelongsToOrganisation,
        HasFactory,
        SoftDeletes;

    /**
     * Whether the campaign currently holds the given system status key.
     *
     * Uses loadMissing() so it is safe under Model::shouldBeStrict().
     */
    public function isStatus(string $systemKey): bool
    {
        $this->loadMissing('status');

        return $this->status?->system_key === $systemKey;
    }

    /**
     * Get the email template.
     *
     * @return BelongsTo<EmailTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    /**
     * Get the segment.
     *
     * @return BelongsTo<CampaignSegment, $this>
     */
    public function segment(): BelongsTo
    {
        return $this->belongsTo(CampaignSegment::class, 'campaign_segment_id');
    }

    /**
     * Get the owning user.
     *
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the user who created this campaign.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this campaign.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this campaign.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user who restored this campaign.
     *
     * @return BelongsTo<User, $this>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get a snapshot of the campaign's auditable attributes.
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
            'name',
            'email_template_id',
            'campaign_segment_id',
            'owner_id',
            'subject',
            'from_name',
            'reply_to',
            'scheduled_at',
            'started_at',
            'completed_at',
            'cancelled_at',
            'rendered_subject',
            'rendered_body_html',
            'rendered_body_text',
            'recipients_count',
            'sent_count',
            'failed_count',
            'skipped_count',
            'opened_count',
            'unsubscribed_count',
            'status_id',
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
            'scheduled_at' => 'immutable_datetime',
            'started_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
            'cancelled_at' => 'immutable_datetime',
            'meta' => 'array',
            'restored_at' => 'immutable_datetime',
            'deleted_at' => 'immutable_datetime',
        ];
    }
}
