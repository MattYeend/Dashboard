<?php

declare(strict_types=1);

namespace App\Services\Campaigns;

use App\Models\EmailSuppression;

class SuppressionService
{
    public function normalise(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    public function isSuppressed(string $email): bool
    {
        return EmailSuppression::query()
            ->where('email', $this->normalise($email))
            ->exists();
    }

    /**
     * Add or revive a suppression. Soft deleted rows are restored, not duplicated,
     * because (organisation_id, email) is unique.
     */
    public function suppress(string $email, string $reason, ?int $campaignId = null, ?int $contactId = null): EmailSuppression
    {
        $row = EmailSuppression::withTrashed()->firstOrNew(['email' => $this->normalise($email)]);
        $row->reason = $reason;
        $row->campaign_id = $campaignId ?? $row->campaign_id;
        $row->contact_id = $contactId ?? $row->contact_id;
        $row->suppressed_at = now();
        $row->deleted_at = null;
        $row->save();

        return $row;
    }
}
