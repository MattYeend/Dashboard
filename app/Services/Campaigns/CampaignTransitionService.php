<?php

declare(strict_types=1);

namespace App\Services\Campaigns;

use App\Exceptions\CampaignTransitionException;
use App\Jobs\SendCampaignRecipientJob;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\CampaignStatus;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Moves a campaign between states.
 *
 * Every change is a single compare and swap UPDATE on status_id, so two
 * clicks (or two requests) can never both succeed and double send.
 */
class CampaignTransitionService
{
    public function __construct(
        private readonly SegmentResolverService $segments,
        private readonly CampaignSenderService $sender,
    ) {
    }

    /**
     * Draft to Scheduled. The dispatch command starts it once scheduled_at has passed.
     */
    public function schedule(Campaign $campaign, CarbonInterface $scheduledAt): Campaign
    {
        $this->assertCanSchedule($campaign, $scheduledAt);

        $this->swap(
            $campaign,
            [CampaignStatus::KEY_DRAFT],
            CampaignStatus::KEY_SCHEDULED,
            'scheduled',
            ['scheduled_at' => $scheduledAt],
        );

        return $campaign->refresh();
    }

    /**
     * Sending to Paused. isSendable() becomes false, so queued jobs stop sending.
     */
    public function pause(Campaign $campaign): Campaign
    {
        $this->swap(
            $campaign,
            [CampaignStatus::KEY_SENDING],
            CampaignStatus::KEY_PAUSED,
            'paused',
        );

        return $campaign->refresh();
    }

    /**
     * Paused to Sending, then queue a job for every recipient still pending.
     */
    public function resume(Campaign $campaign): Campaign
    {
        $this->swap(
            $campaign,
            [CampaignStatus::KEY_PAUSED],
            CampaignStatus::KEY_SENDING,
            'resumed',
        );

        $dispatched = 0;

        CampaignRecipient::query()
            ->where('campaign_id', $campaign->getKey())
            ->where('status', 'pending')
            ->chunkById(500, function ($recipients) use (&$dispatched): void {
                foreach ($recipients as $recipient) {
                    SendCampaignRecipientJob::dispatch($recipient->getKey());
                    $dispatched++;
                }
            });

        $campaign->refresh();

        // Nothing left to send, so finish the campaign rather than leave it Sending.
        if ($dispatched === 0) {
            $this->sender->refreshCounts($campaign);
        }

        return $campaign->refresh();
    }

    /**
     * Draft, Scheduled or Paused to Cancelled. Pending recipients become skipped.
     */
    public function cancel(Campaign $campaign): Campaign
    {
        DB::transaction(function () use ($campaign): void {
            $this->swap(
                $campaign,
                [CampaignStatus::KEY_DRAFT, CampaignStatus::KEY_SCHEDULED, CampaignStatus::KEY_PAUSED],
                CampaignStatus::KEY_CANCELLED,
                'cancelled',
                ['cancelled_at' => now()],
            );

            CampaignRecipient::query()
                ->where('campaign_id', $campaign->getKey())
                ->where('status', 'pending')
                ->update(['status' => 'skipped']);
        });

        $campaign->refresh();
        $this->sender->refreshCounts($campaign);

        return $campaign->refresh();
    }

    /**
     * Atomic compare and swap on status_id.
     *
     * @param array<int, string> $fromKeys Status keys the campaign may currently have.
     * @param array<string, mixed> $attributes Extra columns to set in the same UPDATE.
     */
    private function swap(
        Campaign $campaign,
        array $fromKeys,
        string $toKey,
        string $action,
        array $attributes = [],
    ): void {
        $fromIds = array_map(
            static fn (string $key): int => CampaignStatus::idForKey($key),
            $fromKeys,
        );

        $updated = Campaign::query()
            ->whereKey($campaign->getKey())
            ->whereIn('status_id', $fromIds)
            ->update(['status_id' => CampaignStatus::idForKey($toKey)] + $attributes);

        if ($updated === 0) {
            throw CampaignTransitionException::notAllowed($action);
        }
    }

    /**
     * Checks that must pass before a campaign can be scheduled.
     */
    private function assertCanSchedule(Campaign $campaign, CarbonInterface $scheduledAt): void
    {
        // One minute of grace so "send now" is not rejected by clock drift.
        if ($scheduledAt->lt(now()->subMinute())) {
            throw CampaignTransitionException::invalid('The send time cannot be in the past.');
        }

        $template = $campaign->template;

        if ($template === null || ! $template->is_active) {
            throw CampaignTransitionException::invalid('The email template must exist and be active.');
        }

        if (trim((string) ($campaign->subject ?: $template->subject)) === '') {
            throw CampaignTransitionException::invalid('The campaign needs a subject.');
        }

        if (preg_match('/\{\{ ?unsubscribe_url ?\}\}/', (string) $template->body_html) !== 1) {
            throw CampaignTransitionException::invalid('The template must contain an unsubscribe link.');
        }

        if (trim((string) config('campaigns.postal_address')) === ''
            || trim((string) config('campaigns.from_address')) === '') {
            throw CampaignTransitionException::invalid('The sender and postal address are not configured.');
        }

        $segment = $campaign->segment;

        if ($segment === null) {
            throw CampaignTransitionException::invalid('The campaign needs a segment.');
        }

        try {
            $hasContacts = $this->segments->query($segment)->exists();
        } catch (InvalidArgumentException) {
            throw CampaignTransitionException::invalid('The segment contains unsupported rules.');
        }

        if (! $hasContacts) {
            throw CampaignTransitionException::invalid('The segment does not match any contacts with an email address.');
        }
    }
}