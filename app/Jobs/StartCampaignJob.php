<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\CampaignStatus;
use App\Services\Campaigns\SegmentResolverService;
use App\Services\Campaigns\SuppressionService;
use App\Services\Campaigns\TemplateRendererService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StartCampaignJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public function __construct(public int $campaignId) {}

    public function handle(
        SegmentResolverService $resolver,
        SuppressionService $suppression,
        TemplateRendererService $renderer,
    ): void {
        $campaign = Campaign::with(['template', 'segment'])->findOrFail($this->campaignId);

        // Only a campaign that is Scheduled may start, and only once.
        $started = Campaign::whereKey($campaign->id)
            ->where('status_id', CampaignStatus::idForKey(CampaignStatus::KEY_SCHEDULED))
            ->update(['status_id' => CampaignStatus::idForKey(CampaignStatus::KEY_SENDING), 'started_at' => now()]);

        if ($started === 0) {
            return;
        }

        $seen = [];

        DB::transaction(function () use ($campaign, $resolver, $suppression, &$seen): void {
            $resolver->query($campaign->segment)->orderBy('id')->chunkById(500, function ($contacts) use ($campaign, $suppression, &$seen): void {
                foreach ($contacts as $contact) {
                    $email = $suppression->normalise((string) $contact->email);

                    if (isset($seen[$email]) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        continue;
                    }

                    $seen[$email] = true;

                    CampaignRecipient::create([
                        'campaign_id' => $campaign->id,
                        'contact_id' => $contact->id,
                        'email' => $email,
                        'name' => $contact->name,
                        'status' => $suppression->isSuppressed($email) ? 'skipped' : 'pending',
                        'token' => Str::random(40),
                    ]);
                }
            });
        });

        $campaign->update([
            'recipients_count' => count($seen),
            'rendered_subject' => $campaign->subject ?: $campaign->template->subject,
            'rendered_body_html' => $campaign->template->body_html,
            'rendered_body_text' => $campaign->template->body_text,
        ]);

        CampaignRecipient::where('campaign_id', $campaign->id)->where('status', 'pending')
            ->orderBy('id')->each(fn (CampaignRecipient $r) => SendCampaignRecipientJob::dispatch($r->id));
    }
}
