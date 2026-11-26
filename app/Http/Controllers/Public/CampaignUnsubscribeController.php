<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Campaigns\CampaignSenderService;
use App\Services\Campaigns\RecipientLookupService;
use App\Services\Campaigns\SuppressionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class CampaignUnsubscribeController extends Controller
{
    public function __construct(
        private readonly RecipientLookupService $lookup,
        private readonly SuppressionService $suppression,
        private readonly CampaignSenderService $sender,
    ) {
    }

    public function show(string $token): View
    {
        abort_if($this->lookup->findByToken($token) === null, 404);

        return view('campaigns.unsubscribe', [
            'action' => URL::signedRoute('campaigns.unsubscribe.store', ['token' => $token]),
        ]);
    }

    public function store(Request $request, string $token): Response|View
    {
        $recipient = $this->lookup->findByToken($token);
        abort_if($recipient === null, 404);

        // Enter the recipient's tenant so scoped models and audit logs are correct.
        $handled = $this->lookup->runWithinOrganisation($recipient, function () use ($recipient): void {
            $this->suppression->suppress(
                $recipient->email,
                'unsubscribed',
                $recipient->campaign_id,
                $recipient->contact_id,
            );

            if ($recipient->unsubscribed_at === null) {
                $recipient->forceFill([
                    'status' => 'unsubscribed',
                    'unsubscribed_at' => now(),
                ])->save();

                $this->sender->refreshCounts($recipient->campaign);
            }
        });

        abort_unless($handled, 404);

        if ($this->isOneClick($request)) {
            return response()->noContent();
        }

        return view('campaigns.unsubscribed');
    }

    /**
     * RFC 8058: mail clients POST the body "List-Unsubscribe=One-Click".
     */
    private function isOneClick(Request $request): bool
    {
        return $request->input('List-Unsubscribe') === 'One-Click';
    }
}