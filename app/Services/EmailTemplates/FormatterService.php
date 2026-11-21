<?php

declare(strict_types=1);

namespace App\Services\EmailTemplates;

use App\Models\EmailTemplate;

class FormatterService
{
    /**
     * Shape an email template for the frontend. Only whitelisted attributes
     * are returned, never the raw model.
     *
     * @return array<string, mixed>
     */
    public function format(EmailTemplate $emailTemplate): array
    {
        return [
            'id' => $emailTemplate->id,
            'name' => $emailTemplate->name,
            'subject' => $emailTemplate->subject,
            'preheader' => $emailTemplate->preheader,
            'body_html' => $emailTemplate->body_html,
            'body_text' => $emailTemplate->body_text,
            'is_active' => $emailTemplate->is_active,
            'meta' => $emailTemplate->meta,
            'created_at' => $emailTemplate->created_at,
            'updated_at' => $emailTemplate->updated_at,
            'deleted_at' => $emailTemplate->deleted_at,
            'restored_at' => $emailTemplate->restored_at,
            'created_by' => $emailTemplate->created_by,
            'updated_by' => $emailTemplate->updated_by,
            'deleted_by' => $emailTemplate->deleted_by,
            'restored_by' => $emailTemplate->restored_by,
            'creator' => $emailTemplate->creator ? ['id' => $emailTemplate->creator->id, 'name' => $emailTemplate->creator->name] : null,
            'updater' => $emailTemplate->updater ? ['id' => $emailTemplate->updater->id, 'name' => $emailTemplate->updater->name] : null,
            'deleter' => $emailTemplate->deleter ? ['id' => $emailTemplate->deleter->id, 'name' => $emailTemplate->deleter->name] : null,
            'restorer' => $emailTemplate->restorer ? ['id' => $emailTemplate->restorer->id, 'name' => $emailTemplate->restorer->name] : null,
        ];
    }
}