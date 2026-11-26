<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\CampaignRecipient;
use App\Services\Campaigns\TemplateRendererService;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Support\Facades\URL;

class CampaignMail extends Mailable
{
    /**
     * Create a new message instance.
     */
    public function __construct(public CampaignRecipient $recipient) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $campaign = $this->recipient->campaign;

        return new Envelope(
            from: new Address((string) config('campaigns.from_address'), $campaign->from_name ?: config('app.name')),
            replyTo: $campaign->reply_to ? [new Address($campaign->reply_to)] : [],
            subject: $this->renderTemplate($campaign->rendered_subject, false),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $campaign = $this->recipient->campaign;

        return new Content(
            htmlString: $this->renderTemplate($campaign->rendered_body_html, true),
            text: null,
        );
    }

    /**
     * Get the message headers, including one click unsubscribe.
     */
    public function headers(): Headers
    {
        $url = $this->unsubscribeUrl();

        return new Headers(text: [
            'List-Unsubscribe' => '<'.$url.'>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Build the signed, per recipient unsubscribe URL.
     */
    private function unsubscribeUrl(): string
    {
        return URL::signedRoute('campaigns.unsubscribe', ['token' => $this->recipient->token]);
    }

    /**
     * Replace merge tags in a template for this recipient.
     *
     * Named renderTemplate() because Mailable already defines a public render().
     */
    private function renderTemplate(string $template, bool $html): string
    {
        return app(TemplateRendererService::class)->render($template, [
            'first_name' => explode(' ', (string) $this->recipient->name)[0],
            'name' => (string) $this->recipient->name,
            'email' => $this->recipient->email,
            'company' => (string) config('app.name'),
            'unsubscribe_url' => $this->unsubscribeUrl(),
        ], $html);
    }
}
