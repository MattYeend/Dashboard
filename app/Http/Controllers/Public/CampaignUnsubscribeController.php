<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Actions\Campaigns\UnsubscribeRecipient;
use App\Http\Controllers\Controller;
use App\Services\Campaigns\RecipientLookupService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class CampaignUnsubscribeController extends Controller
{
    public function __construct(
        private readonly RecipientLookupService $lookup,
        private readonly UnsubscribeRecipient $unsubscribe,
    ) {}

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
        $handled = $this->lookup->runWithinOrganisation(
            $recipient,
            fn () => $this->unsubscribe->execute($recipient),
        );

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