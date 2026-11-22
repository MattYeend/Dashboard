<?php

declare(strict_types=1);

namespace App\Services\Campaigns;

use App\Models\Campaign;

class FormatterService
{
    /**
     * Shape a campaign for the frontend. Only whitelisted attributes
     * are returned, never the raw model.
     *
     * rendered_body_html is sanitised on the way in, but the frontend must
     * still treat it as untrusted if it is ever output as raw HTML.
     *
     * @return array<string, mixed>
     */
    public function format(Campaign $campaign): array
    {
        return [
            'id' => $campaign->id,
            'name' => $campaign->name,
            'email_template_id' => $campaign->email_template_id,
            'campaign_segment_id' => $campaign->campaign_segment_id,
            'owner_id' => $campaign->owner_id,
            'subject' => $campaign->subject,
            'from_name' => $campaign->from_name,
            'reply_to' => $campaign->reply_to,
            'scheduled_at' => $campaign->scheduled_at?->format('Y-m-d\TH:i'),
            'started_at' => $campaign->started_at?->format('Y-m-d\TH:i'),
            'completed_at' => $campaign->completed_at?->format('Y-m-d\TH:i'),
            'cancelled_at' => $campaign->cancelled_at?->format('Y-m-d\TH:i'),
            'rendered_subject' => $campaign->rendered_subject,
            'rendered_body_html' => $campaign->rendered_body_html,
            'rendered_body_text' => $campaign->rendered_body_text,
            'recipients_count' => $campaign->recipients_count,
            'sent_count' => $campaign->sent_count,
            'failed_count' => $campaign->failed_count,
            'skipped_count' => $campaign->skipped_count,
            'opened_count' => $campaign->opened_count,
            'unsubscribed_count' => $campaign->unsubscribed_count,
            'status_id' => $campaign->status_id,
            'meta' => $campaign->meta,
            'created_at' => $campaign->created_at,
            'updated_at' => $campaign->updated_at,
            'deleted_at' => $campaign->deleted_at,
            'restored_at' => $campaign->restored_at,
            'created_by' => $campaign->created_by,
            'updated_by' => $campaign->updated_by,
            'deleted_by' => $campaign->deleted_by,
            'restored_by' => $campaign->restored_by,
            'template' => $campaign->template ? ['id' => $campaign->template->id, 'name' => $campaign->template->name] : null,
            'segment' => $campaign->segment ? ['id' => $campaign->segment->id, 'name' => $campaign->segment->name] : null,
            'owner' => $campaign->owner ? ['id' => $campaign->owner->id, 'name' => $campaign->owner->name] : null,
            'status' => $campaign->status ? [
                'id' => $campaign->status->id,
                'title' => $campaign->status->title,
                'system_key' => $campaign->status->system_key,
                'background_colour' => $campaign->status->background_colour,
                'text_colour' => $campaign->status->text_colour,
            ] : null,
            'creator' => $campaign->creator ? ['id' => $campaign->creator->id, 'name' => $campaign->creator->name] : null,
            'updater' => $campaign->updater ? ['id' => $campaign->updater->id, 'name' => $campaign->updater->name] : null,
            'deleter' => $campaign->deleter ? ['id' => $campaign->deleter->id, 'name' => $campaign->deleter->name] : null,
            'restorer' => $campaign->restorer ? ['id' => $campaign->restorer->id, 'name' => $campaign->restorer->name] : null,
        ];
    }
}