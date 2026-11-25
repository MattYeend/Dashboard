<?php

declare(strict_types=1);

namespace App\Services\EmailSuppressions;

use App\Models\EmailSuppression;

class FormatterService
{
    /**
     * Shape an email suppression for the frontend. Only whitelisted
     * attributes are returned, never the raw model.
     *
     * @return array<string, mixed>
     */
    public function format(EmailSuppression $emailSuppression): array
    {
        return [
            'id' => $emailSuppression->id,
            'email' => $emailSuppression->email,
            'reason' => $emailSuppression->reason,
            'campaign_id' => $emailSuppression->campaign_id,
            'contact_id' => $emailSuppression->contact_id,
            'suppressed_at' => $emailSuppression->suppressed_at?->format('Y-m-d\TH:i'),
            'notes' => $emailSuppression->notes,
            'meta' => $emailSuppression->meta,
            'created_at' => $emailSuppression->created_at,
            'updated_at' => $emailSuppression->updated_at,
            'deleted_at' => $emailSuppression->deleted_at,
            'restored_at' => $emailSuppression->restored_at,
            'created_by' => $emailSuppression->created_by,
            'updated_by' => $emailSuppression->updated_by,
            'deleted_by' => $emailSuppression->deleted_by,
            'restored_by' => $emailSuppression->restored_by,
            'campaign' => $emailSuppression->campaign ? ['id' => $emailSuppression->campaign->id, 'name' => $emailSuppression->campaign->name] : null,
            'contact' => $emailSuppression->contact ? ['id' => $emailSuppression->contact->id] : null,
            'creator' => $emailSuppression->creator ? ['id' => $emailSuppression->creator->id, 'name' => $emailSuppression->creator->name] : null,
            'updater' => $emailSuppression->updater ? ['id' => $emailSuppression->updater->id, 'name' => $emailSuppression->updater->name] : null,
            'deleter' => $emailSuppression->deleter ? ['id' => $emailSuppression->deleter->id, 'name' => $emailSuppression->deleter->name] : null,
            'restorer' => $emailSuppression->restorer ? ['id' => $emailSuppression->restorer->id, 'name' => $emailSuppression->restorer->name] : null,
        ];
    }
}