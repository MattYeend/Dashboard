<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Services\Campaigns\RecipientLookupService;
use Illuminate\Http\Response;

class CampaignTrackingController extends Controller
{
    private const PIXEL = 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

    public function __construct(
        private readonly RecipientLookupService $lookup,
    ) {
    }

    public function open(string $token): Response
    {
        if (config('campaigns.track_opens')) {
            $recipient = $this->lookup->findByToken($token);

            if ($recipient !== null) {
                $this->lookup->runWithinOrganisation($recipient, function () use ($recipient): void {
                    // Atomic: only the request that sets opened_at counts the open.
                    $firstOpen = CampaignRecipient::query()
                        ->whereKey($recipient->getKey())
                        ->whereNull('opened_at')
                        ->update(['opened_at' => now()]);

                    if ($firstOpen === 1) {
                        Campaign::query()
                            ->whereKey($recipient->campaign_id)
                            ->increment('opened_count');
                    }
                });
            }
        }

        return response(base64_decode(self::PIXEL), 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-store, private',
        ]);
    }
}