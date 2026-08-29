<?php

namespace App\Services\Contacts;

use App\Models\Contact;
use Illuminate\Support\Collection;

class DuplicateDetectionService
{
    /**
     * Maximum number of candidate pairs returned per request.
     */
    protected const MAX_CANDIDATES = 50;

    /**
     * Find likely duplicate contact pairs based on normalised email
     * and phone matches.
     */
    public function findCandidates(): Collection
    {
        $contacts = Contact::query()
            ->with('contactable')
            ->select(['id', 'contactable_id', 'contactable_type', 'email', 'phone'])
            ->orderBy('id')
            ->get();

        $pairs = collect();

        foreach ($contacts as $contact) {
            foreach ($contacts as $other) {
                if ($contact->id >= $other->id) {
                    continue;
                }

                $reason = $this->matchReason($contact, $other);

                if ($reason !== null) {
                    $pairs->push([
                        'contact' => $contact,
                        'duplicate' => $other,
                        'reason' => $reason,
                    ]);
                }
            }
        }

        return $pairs->take(self::MAX_CANDIDATES)->values();
    }

    /**
     * Determine the match reason between two contacts, or null if
     * they are not considered likely duplicates.
     */
    protected function matchReason(Contact $a, Contact $b): ?string
    {
        if ($a->email && $b->email && $this->normaliseEmail($a->email) === $this->normaliseEmail($b->email)) {
            return 'Matching email address';
        }

        if ($a->phone && $b->phone && $this->normalisePhone($a->phone) === $this->normalisePhone($b->phone)) {
            return 'Matching phone number';
        }

        return null;
    }

    /**
     * Normalise an email address for comparison.
     */
    protected function normaliseEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    /**
     * Normalise a phone number for comparison, stripping all
     * non-numeric characters.
     */
    protected function normalisePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }
}
