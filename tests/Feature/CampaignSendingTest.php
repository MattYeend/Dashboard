<?php

declare(strict_types=1);

use App\Jobs\SendCampaignRecipientJob;
use App\Jobs\StartCampaignJob;
use App\Mail\CampaignMail;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\CampaignSegment;
use App\Models\CampaignStatus;
use App\Models\Contact;
use App\Models\EmailSuppression;
use App\Models\EmailTemplate;
use App\Models\Organisation;
use App\Models\User;
use App\Services\Campaigns\SegmentResolverService;
use Database\Seeders\CampaignStatusSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\Concerns\CreatesUsers;

uses(
    LazilyRefreshDatabase::class,
    CreatesUsers::class,
);

beforeEach(function () {
    config([
        'campaigns.postal_address' => '1 Test Street, Leicester',
        'campaigns.from_address' => 'hello@example.com',
    ]);

    // Tests that never create a user still need a current organisation and statuses.
    Organisation::factory()->create()->makeCurrent();

    if (! CampaignStatus::query()->where('system_key', CampaignStatus::KEY_DRAFT)->exists()) {
        $this->seed(CampaignStatusSeeder::class);
    }
});

if (! function_exists('makeSendableCampaign')) {
    /**
     * Build a campaign in the given status with a valid template and segment.
     *
     * @param  array<string, mixed>  $overrides
     */
    function makeSendableCampaign(string $statusKey, array $overrides = []): Campaign
    {
        $template = EmailTemplate::factory()->create([
            'is_active' => true,
            'subject' => 'Hello {{ first_name }}',
            'body_html' => '<p>Hi {{ first_name }}</p><a href="{{ unsubscribe_url }}">Unsubscribe</a>',
            'body_text' => 'Hi {{ first_name }}',
        ]);

        $segment = CampaignSegment::factory()->create([
            'criteria' => [],
        ]);

        return Campaign::factory()->create(array_merge([
            'status_id' => CampaignStatus::idForKey($statusKey),
            'email_template_id' => $template->id,
            'campaign_segment_id' => $segment->id,
            'scheduled_at' => now()->subMinute(),
        ], $overrides));
    }
}

if (! function_exists('makeRecipient')) {
    /**
     * Build a recipient for a campaign.
     *
     * @param  array<string, mixed>  $overrides
     */
    function makeRecipient(Campaign $campaign, string $status = 'pending', array $overrides = []): CampaignRecipient
    {
        return CampaignRecipient::factory()->create(array_merge([
            'campaign_id' => $campaign->id,
            'email' => Str::lower(Str::random(8)).'@example.com',
            'name' => 'Sam Jones',
            'status' => $status,
            'token' => Str::random(40),
        ], $overrides));
    }
}

if (! function_exists('makeContact')) {
    /**
     * Build a contact. Contacts are polymorphic, so a contactable is required.
     *
     * @param  array<string, mixed>  $overrides
     */
    function makeContact(array $overrides = []): Contact
    {
        return Contact::factory()->create(array_merge([
            'contactable_type' => User::class,
            // No foreign key exists on a polymorphic column, so a fixed id is enough here.
            'contactable_id' => 1,
        ], $overrides));
    }
}

if (! function_exists('runStartJob')) {
    /**
     * Run StartCampaignJob in process so Queue::fake() only captures the send jobs.
     */
    function runStartJob(Campaign $campaign): void
    {
        app()->call([new StartCampaignJob($campaign->id), 'handle']);
    }
}

if (! function_exists('runSendJob')) {
    /**
     * Run SendCampaignRecipientJob in process, bypassing the queue middleware.
     */
    function runSendJob(CampaignRecipient $recipient): void
    {
        app()->call([new SendCampaignRecipientJob($recipient->id), 'handle']);
    }
}

describe('schedule', function () {
    test('a draft campaign can be scheduled', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = makeSendableCampaign(CampaignStatus::KEY_DRAFT, ['scheduled_at' => null]);
        makeContact(['email' => 'one@example.com']);

        $this->actingAs($superAdmin)
            ->postJson("/campaigns/{$campaign->id}/schedule", [
                'scheduled_at' => now()->addDay()->toDateTimeString(),
            ])
            ->assertOk();

        expect($campaign->refresh()->status_id)
            ->toBe(CampaignStatus::idForKey(CampaignStatus::KEY_SCHEDULED));
    });

    test('scheduling twice is refused the second time', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = makeSendableCampaign(CampaignStatus::KEY_DRAFT, ['scheduled_at' => null]);
        makeContact(['email' => 'one@example.com']);
        $payload = ['scheduled_at' => now()->addDay()->toDateTimeString()];

        $this->actingAs($superAdmin)->postJson("/campaigns/{$campaign->id}/schedule", $payload)->assertOk();
        $this->actingAs($superAdmin)->postJson("/campaigns/{$campaign->id}/schedule", $payload)->assertStatus(422);
    });

    test('scheduling fails validation without a send time', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = makeSendableCampaign(CampaignStatus::KEY_DRAFT);

        $this->actingAs($superAdmin)
            ->postJson("/campaigns/{$campaign->id}/schedule", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['scheduled_at']);
    });

    test('scheduling fails validation when the send time is in the past', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = makeSendableCampaign(CampaignStatus::KEY_DRAFT);

        $this->actingAs($superAdmin)
            ->postJson("/campaigns/{$campaign->id}/schedule", [
                'scheduled_at' => now()->subDay()->toDateTimeString(),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['scheduled_at']);
    });

    test('scheduling is refused when the template is inactive', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = makeSendableCampaign(CampaignStatus::KEY_DRAFT);
        $campaign->template->update(['is_active' => false]);
        makeContact(['email' => 'one@example.com']);

        $this->actingAs($superAdmin)
            ->postJson("/campaigns/{$campaign->id}/schedule", [
                'scheduled_at' => now()->addDay()->toDateTimeString(),
            ])
            ->assertStatus(422);
    });

    test('scheduling is refused when the template has no unsubscribe link', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = makeSendableCampaign(CampaignStatus::KEY_DRAFT);
        $campaign->template->update(['body_html' => '<p>No link here</p>']);
        makeContact(['email' => 'one@example.com']);

        $this->actingAs($superAdmin)
            ->postJson("/campaigns/{$campaign->id}/schedule", [
                'scheduled_at' => now()->addDay()->toDateTimeString(),
            ])
            ->assertStatus(422);
    });

    test('scheduling is refused when the postal address is not configured', function () {
        config(['campaigns.postal_address' => '']);

        $superAdmin = $this->superAdminUser();
        $campaign = makeSendableCampaign(CampaignStatus::KEY_DRAFT);
        makeContact(['email' => 'one@example.com']);

        $this->actingAs($superAdmin)
            ->postJson("/campaigns/{$campaign->id}/schedule", [
                'scheduled_at' => now()->addDay()->toDateTimeString(),
            ])
            ->assertStatus(422);
    });

    test('scheduling is refused when the segment matches no contacts', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = makeSendableCampaign(CampaignStatus::KEY_DRAFT);

        $this->actingAs($superAdmin)
            ->postJson("/campaigns/{$campaign->id}/schedule", [
                'scheduled_at' => now()->addDay()->toDateTimeString(),
            ])
            ->assertStatus(422);
    });

    test('user without permission cannot schedule a campaign', function () {
        $user = $this->normalUser();
        $campaign = makeSendableCampaign(CampaignStatus::KEY_DRAFT);

        $this->actingAs($user)
            ->postJson("/campaigns/{$campaign->id}/schedule", [
                'scheduled_at' => now()->addDay()->toDateTimeString(),
            ])
            ->assertStatus(403);
    });

    test('unauthenticated user cannot schedule a campaign', function () {
        $campaign = makeSendableCampaign(CampaignStatus::KEY_DRAFT);

        $this->postJson("/campaigns/{$campaign->id}/schedule", [
            'scheduled_at' => now()->addDay()->toDateTimeString(),
        ])->assertStatus(401);
    });
});

describe('pause', function () {
    test('a sending campaign can be paused', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = makeSendableCampaign(CampaignStatus::KEY_SENDING);

        $this->actingAs($superAdmin)
            ->postJson("/campaigns/{$campaign->id}/pause")
            ->assertOk();

        expect($campaign->refresh()->status_id)
            ->toBe(CampaignStatus::idForKey(CampaignStatus::KEY_PAUSED));
    });

    test('a draft campaign cannot be paused', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = makeSendableCampaign(CampaignStatus::KEY_DRAFT);

        $this->actingAs($superAdmin)
            ->postJson("/campaigns/{$campaign->id}/pause")
            ->assertStatus(422);
    });

    test('user without permission cannot pause a campaign', function () {
        $user = $this->normalUser();
        $campaign = makeSendableCampaign(CampaignStatus::KEY_SENDING);

        $this->actingAs($user)
            ->postJson("/campaigns/{$campaign->id}/pause")
            ->assertStatus(403);
    });

    test('a paused campaign does not send queued emails', function () {
        $campaign = makeSendableCampaign(CampaignStatus::KEY_PAUSED);
        $recipient = makeRecipient($campaign);

        runSendJob($recipient);

        Mail::assertNothingSent();
        expect($recipient->refresh()->status)->toBe('pending');
    });
});

describe('resume', function () {
    test('a paused campaign can be resumed and only pending recipients are queued', function () {
        Queue::fake();

        $superAdmin = $this->superAdminUser();
        $campaign = makeSendableCampaign(CampaignStatus::KEY_PAUSED);
        makeRecipient($campaign, 'pending');
        makeRecipient($campaign, 'pending');
        makeRecipient($campaign, 'sent');
        makeRecipient($campaign, 'skipped');

        $this->actingAs($superAdmin)
            ->postJson("/campaigns/{$campaign->id}/resume")
            ->assertOk();

        expect($campaign->refresh()->status_id)
            ->toBe(CampaignStatus::idForKey(CampaignStatus::KEY_SENDING));

        Queue::assertPushed(SendCampaignRecipientJob::class, 2);
    });

    test('a campaign that is not paused cannot be resumed', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = makeSendableCampaign(CampaignStatus::KEY_DRAFT);

        $this->actingAs($superAdmin)
            ->postJson("/campaigns/{$campaign->id}/resume")
            ->assertStatus(422);
    });

    test('user without permission cannot resume a campaign', function () {
        $user = $this->normalUser();
        $campaign = makeSendableCampaign(CampaignStatus::KEY_PAUSED);

        $this->actingAs($user)
            ->postJson("/campaigns/{$campaign->id}/resume")
            ->assertStatus(403);
    });
});

describe('cancel', function () {
    test('draft, scheduled and paused campaigns can be cancelled', function (string $statusKey) {
        $superAdmin = $this->superAdminUser();
        $campaign = makeSendableCampaign($statusKey);

        $this->actingAs($superAdmin)
            ->postJson("/campaigns/{$campaign->id}/cancel")
            ->assertOk();

        $campaign->refresh();

        expect($campaign->status_id)->toBe(CampaignStatus::idForKey(CampaignStatus::KEY_CANCELLED))
            ->and($campaign->cancelled_at)->not->toBeNull();
    })->with([
        'draft' => CampaignStatus::KEY_DRAFT,
        'scheduled' => CampaignStatus::KEY_SCHEDULED,
        'paused' => CampaignStatus::KEY_PAUSED,
    ]);

    test('cancelling skips pending recipients and leaves sent ones alone', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = makeSendableCampaign(CampaignStatus::KEY_PAUSED);
        $pending = makeRecipient($campaign, 'pending');
        $sent = makeRecipient($campaign, 'sent');

        $this->actingAs($superAdmin)
            ->postJson("/campaigns/{$campaign->id}/cancel")
            ->assertOk();

        expect($pending->refresh()->status)->toBe('skipped')
            ->and($sent->refresh()->status)->toBe('sent');
    });

    test('a sending campaign cannot be cancelled', function () {
        $superAdmin = $this->superAdminUser();
        $campaign = makeSendableCampaign(CampaignStatus::KEY_SENDING);

        $this->actingAs($superAdmin)
            ->postJson("/campaigns/{$campaign->id}/cancel")
            ->assertStatus(422);
    });

    test('user without permission cannot cancel a campaign', function () {
        $user = $this->normalUser();
        $campaign = makeSendableCampaign(CampaignStatus::KEY_DRAFT);

        $this->actingAs($user)
            ->postJson("/campaigns/{$campaign->id}/cancel")
            ->assertStatus(403);
    });
});

describe('start campaign job', function () {
    test('creates a recipient for each unique contact with an email address', function () {
        Queue::fake();

        $campaign = makeSendableCampaign(CampaignStatus::KEY_SCHEDULED);
        makeContact(['email' => 'one@example.com']);
        makeContact(['email' => 'two@example.com']);
        makeContact(['email' => 'ONE@example.com']);
        makeContact(['email' => 'not-an-email']);
        makeContact(['email' => null]);

        runStartJob($campaign);

        expect(CampaignRecipient::where('campaign_id', $campaign->id)->count())->toBe(2)
            ->and($campaign->refresh()->status_id)->toBe(CampaignStatus::idForKey(CampaignStatus::KEY_SENDING));

        Queue::assertPushed(SendCampaignRecipientJob::class, 2);
    });

    test('only contacts matching the segment criteria are selected', function () {
        Queue::fake();

        $campaign = makeSendableCampaign(CampaignStatus::KEY_SCHEDULED);
        $campaign->segment->update([
            'criteria' => [
                ['field' => 'name', 'operator' => 'contains', 'value' => 'Leicester'],
            ],
        ]);

        makeContact(['email' => 'in@example.com', 'name' => 'Leicester Customer']);
        makeContact(['email' => 'out@example.com', 'name' => 'Bangor Customer']);

        runStartJob($campaign);

        expect(CampaignRecipient::where('campaign_id', $campaign->id)->pluck('email')->all())
            ->toBe(['in@example.com']);
    });

    test('a segment with an unsupported rule is rejected', function () {
        $campaign = makeSendableCampaign(CampaignStatus::KEY_SCHEDULED);
        $campaign->segment->update([
            'criteria' => [
                ['field' => 'password', 'operator' => 'equals', 'value' => 'x'],
            ],
        ]);

        app(SegmentResolverService::class)->query($campaign->segment->refresh());
    })->throws(InvalidArgumentException::class);

    test('a suppressed address is skipped and not queued', function () {
        Queue::fake();

        $campaign = makeSendableCampaign(CampaignStatus::KEY_SCHEDULED);
        makeContact(['email' => 'blocked@example.com']);
        makeContact(['email' => 'allowed@example.com']);
        EmailSuppression::factory()->create(['email' => 'blocked@example.com']);

        runStartJob($campaign);

        $this->assertDatabaseHas('campaign_recipients', [
            'campaign_id' => $campaign->id,
            'email' => 'blocked@example.com',
            'status' => 'skipped',
        ]);

        Queue::assertPushed(SendCampaignRecipientJob::class, 1);
    });

    test('a second run does nothing', function () {
        Queue::fake();

        $campaign = makeSendableCampaign(CampaignStatus::KEY_SCHEDULED);
        makeContact(['email' => 'one@example.com']);

        runStartJob($campaign);
        runStartJob($campaign);

        expect(CampaignRecipient::where('campaign_id', $campaign->id)->count())->toBe(1);

        Queue::assertPushed(SendCampaignRecipientJob::class, 1);
    });

    test('a draft campaign is never started', function () {
        Queue::fake();

        $campaign = makeSendableCampaign(CampaignStatus::KEY_DRAFT);
        makeContact(['email' => 'one@example.com']);

        runStartJob($campaign);

        expect(CampaignRecipient::where('campaign_id', $campaign->id)->count())->toBe(0);

        Queue::assertNothingPushed();
    });

    test('contacts from another organisation are never selected', function () {
        Queue::fake();

        $campaign = makeSendableCampaign(CampaignStatus::KEY_SCHEDULED);
        makeContact(['email' => 'mine@example.com']);

        $otherOrganisation = Organisation::factory()->create();
        $otherOrganisation->makeCurrent();
        makeContact(['email' => 'theirs@example.com']);
        Organisation::forgetCurrent();

        // Return to the organisation the campaign belongs to before running the job.
        Organisation::withoutGlobalScopes()->findOrFail($campaign->organisation_id)->makeCurrent();

        runStartJob($campaign);

        $emails = CampaignRecipient::where('campaign_id', $campaign->id)->pluck('email')->all();

        expect($emails)->toBe(['mine@example.com']);
    });
});

describe('send recipient job', function () {
    test('sends the email and marks the recipient as sent', function () {
        $campaign = makeSendableCampaign(CampaignStatus::KEY_SENDING, [
            'rendered_subject' => 'Hello {{ first_name }}',
            'rendered_body_html' => '<p>Hi {{ first_name }}</p><a href="{{ unsubscribe_url }}">Unsubscribe</a>',
        ]);
        $recipient = makeRecipient($campaign, 'pending', ['email' => 'one@example.com']);

        runSendJob($recipient);

        Mail::assertSent(CampaignMail::class, 1);

        expect($recipient->refresh()->status)->toBe('sent')
            ->and($recipient->sent_at)->not->toBeNull();
    });

    test('a suppressed address is skipped even if it was suppressed after queuing', function () {
        $campaign = makeSendableCampaign(CampaignStatus::KEY_SENDING);
        $recipient = makeRecipient($campaign, 'pending', ['email' => 'late@example.com']);
        EmailSuppression::factory()->create(['email' => 'late@example.com']);

        runSendJob($recipient);

        Mail::assertNothingSent();

        expect($recipient->refresh()->status)->toBe('skipped');
    });

    test('a recipient that is not pending is not emailed again', function () {
        $campaign = makeSendableCampaign(CampaignStatus::KEY_SENDING);
        $recipient = makeRecipient($campaign, 'sent');

        runSendJob($recipient);

        Mail::assertNothingSent();
    });

    test('the campaign is marked as sent once nothing is pending', function () {
        $campaign = makeSendableCampaign(CampaignStatus::KEY_SENDING, [
            'rendered_subject' => 'Hello',
            'rendered_body_html' => '<a href="{{ unsubscribe_url }}">Unsubscribe</a>',
        ]);
        $recipient = makeRecipient($campaign, 'pending');

        runSendJob($recipient);

        $campaign->refresh();

        expect($campaign->status_id)->toBe(CampaignStatus::idForKey(CampaignStatus::KEY_SENT))
            ->and($campaign->sent_count)->toBe(1)
            ->and($campaign->completed_at)->not->toBeNull();
    });
});

describe('dispatch due command', function () {
    test('starts only scheduled campaigns that are due', function () {
        Queue::fake();

        $due = makeSendableCampaign(CampaignStatus::KEY_SCHEDULED, ['scheduled_at' => now()->subMinute()]);
        makeSendableCampaign(CampaignStatus::KEY_SCHEDULED, ['scheduled_at' => now()->addDay()]);
        makeSendableCampaign(CampaignStatus::KEY_DRAFT, ['scheduled_at' => now()->subMinute()]);

        $this->artisan('campaigns:dispatch-due')->assertSuccessful();

        Queue::assertPushed(StartCampaignJob::class, 1);
        Queue::assertPushed(StartCampaignJob::class, fn ($job) => $job->campaignId === $due->id);
    });
});

describe('unsubscribe', function () {
    test('an unsigned link is rejected', function () {
        $recipient = makeRecipient(makeSendableCampaign(CampaignStatus::KEY_SENDING));

        $this->get("/campaigns/unsubscribe/{$recipient->token}")
            ->assertStatus(403);
    });

    test('an unknown token returns 404', function () {
        $url = URL::signedRoute('campaigns.unsubscribe', ['token' => Str::random(40)]);

        $this->get($url)->assertStatus(404);
    });

    test('viewing the confirmation page changes nothing', function () {
        $recipient = makeRecipient(makeSendableCampaign(CampaignStatus::KEY_SENDING), 'pending');
        $url = URL::signedRoute('campaigns.unsubscribe', ['token' => $recipient->token]);

        $this->get($url)->assertOk();

        expect($recipient->refresh()->status)->toBe('pending');
        $this->assertDatabaseMissing('email_suppressions', ['email' => $recipient->email]);
    });

    test('confirming suppresses the address and marks the recipient unsubscribed', function () {
        $recipient = makeRecipient(makeSendableCampaign(CampaignStatus::KEY_SENDING), 'pending');
        $url = URL::signedRoute('campaigns.unsubscribe', ['token' => $recipient->token]);

        $this->post($url)->assertOk();

        expect($recipient->refresh()->status)->toBe('unsubscribed');
        $this->assertDatabaseHas('email_suppressions', ['email' => $recipient->email]);
    });

    test('a one click request returns no content', function () {
        $recipient = makeRecipient(makeSendableCampaign(CampaignStatus::KEY_SENDING), 'pending');
        $url = URL::signedRoute('campaigns.unsubscribe', ['token' => $recipient->token]);

        $this->post($url, ['List-Unsubscribe' => 'One-Click'])->assertNoContent();

        expect($recipient->refresh()->status)->toBe('unsubscribed');
    });

    test('unsubscribing twice is safe', function () {
        $recipient = makeRecipient(makeSendableCampaign(CampaignStatus::KEY_SENDING), 'pending');
        $url = URL::signedRoute('campaigns.unsubscribe', ['token' => $recipient->token]);

        $this->post($url)->assertOk();
        $this->post($url)->assertOk();

        expect(EmailSuppression::where('email', $recipient->email)->count())->toBe(1);
    });

    test('an unsubscribe that arrives before sending stops the email', function () {
        $campaign = makeSendableCampaign(CampaignStatus::KEY_SENDING);
        $recipient = makeRecipient($campaign, 'pending');
        $url = URL::signedRoute('campaigns.unsubscribe', ['token' => $recipient->token]);

        $this->post($url)->assertOk();

        runSendJob($recipient->refresh());

        Mail::assertNothingSent();
    });

    test('contact names are escaped in the html body', function () {
        $campaign = makeSendableCampaign(CampaignStatus::KEY_SENDING, [
            'rendered_subject' => 'Hello',
            'rendered_body_html' => '<p>Hi {{ name }}</p><a href="{{ unsubscribe_url }}">Unsubscribe</a>',
        ]);
        $recipient = makeRecipient($campaign, 'pending', ['name' => '<script>alert(1)</script>']);

        $html = (new CampaignMail($recipient))->render();

        expect($html)->not->toContain('<script>')
            ->and($html)->toContain('&lt;script&gt;');
    });

    test('the email carries one click unsubscribe headers', function () {
        $campaign = makeSendableCampaign(CampaignStatus::KEY_SENDING, [
            'rendered_subject' => 'Hello',
            'rendered_body_html' => '<a href="{{ unsubscribe_url }}">Unsubscribe</a>',
        ]);
        $recipient = makeRecipient($campaign);

        $mail = new CampaignMail($recipient);

        $mail->assertHasSubject('Hello');
        expect($mail->headers()->text)->toHaveKeys(['List-Unsubscribe', 'List-Unsubscribe-Post']);
    });
});
