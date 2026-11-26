<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\CampaignMail;
use App\Models\CampaignRecipient;
use App\Services\Campaigns\CampaignSenderService;
use App\Services\Campaigns\SuppressionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendCampaignRecipientJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /** @return array<int, int> */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function __construct(public int $recipientId)
    {
    }

    /** @return array<int, object> */
    public function middleware(): array
    {
        return [new RateLimited('campaign-emails')];
    }

    public function handle(SuppressionService $suppression, CampaignSenderService $sender): void
    {
        $recipient = CampaignRecipient::with('campaign')->findOrFail($this->recipientId);
        $campaign = $recipient->campaign;

        if ($recipient->status !== 'pending' || ! $sender->isSendable($campaign)) {
            return;
        }

        if ($suppression->isSuppressed($recipient->email)) {
            $recipient->update(['status' => 'skipped']);
            $sender->refreshCounts($campaign);

            return;
        }

        Mail::to($recipient->email)->send(new CampaignMail($recipient));

        $recipient->update(['status' => 'sent', 'sent_at' => now(), 'attempts' => $recipient->attempts + 1]);
        $sender->refreshCounts($campaign);
    }

    public function failed(Throwable $e): void
    {
        $recipient = CampaignRecipient::with('campaign')->find($this->recipientId);

        if ($recipient === null) {
            return;
        }

        $recipient->update([
            'status' => 'failed',
            'failed_at' => now(),
            // Never store the raw exception: it can contain SMTP credentials or addresses.
            'failure_reason' => 'Delivery failed after retries ('.class_basename($e).').',
        ]);
        app(CampaignSenderService::class)->refreshCounts($recipient->campaign);
    }
}