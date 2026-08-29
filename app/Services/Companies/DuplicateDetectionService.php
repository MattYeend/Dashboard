<?php

namespace App\Services\Companies;

use App\Models\Company;
use Illuminate\Support\Collection;

class DuplicateDetectionService
{
    /**
     * Maximum number of candidate pairs returned per request.
     */
    protected const MAX_CANDIDATES = 50;

    /**
     * Find likely duplicate company pairs based on normalised email,
     * phone, and name matches.
     *
     * Note: this compares every non-trashed company against every
     * other, so cost grows quadratically with the dataset. Fine for
     * a CRM-scale table; revisit with blocking (e.g. by first letter
     * of normalised name) if this ever becomes slow.
     */
    public function findCandidates(): Collection
    {
        $companies = Company::query()
            ->select(['id', 'name', 'email', 'phone'])
            ->orderBy('id')
            ->get();

        $pairs = collect();

        foreach ($companies as $company) {
            foreach ($companies as $other) {
                if ($company->id >= $other->id) {
                    continue;
                }

                $reason = $this->matchReason($company, $other);

                if ($reason !== null) {
                    $pairs->push([
                        'company' => $company,
                        'duplicate' => $other,
                        'reason' => $reason,
                    ]);
                }
            }
        }

        return $pairs->take(self::MAX_CANDIDATES)->values();
    }

    /**
     * Determine the match reason between two companies, or null if
     * they are not considered likely duplicates.
     */
    protected function matchReason(Company $a, Company $b): ?string
    {
        if ($a->email && $b->email && $this->normaliseEmail($a->email) === $this->normaliseEmail($b->email)) {
            return 'Matching email address';
        }

        if ($a->phone && $b->phone && $this->normalisePhone($a->phone) === $this->normalisePhone($b->phone)) {
            return 'Matching phone number';
        }

        if ($this->namesAreSimilar($a->name, $b->name)) {
            return 'Similar company name';
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

    /**
     * Determine whether two company names are similar enough to
     * flag as a likely duplicate, using normalised equality and a
     * Levenshtein distance threshold.
     */
    protected function namesAreSimilar(string $a, string $b): bool
    {
        $normalisedA = $this->normaliseName($a);
        $normalisedB = $this->normaliseName($b);

        if ($normalisedA === $normalisedB) {
            return true;
        }

        $maxLength = max(strlen($normalisedA), strlen($normalisedB));

        if ($maxLength === 0) {
            return false;
        }

        return (levenshtein($normalisedA, $normalisedB) / $maxLength) <= 0.15;
    }

    /**
     * Normalise a company name for comparison: lowercase, strip
     * punctuation and common legal-entity suffixes.
     */
    protected function normaliseName(string $name): string
    {
        $name = strtolower(trim($name));
        $name = preg_replace('/[^a-z0-9 ]+/', '', $name) ?? $name;
        $name = preg_replace('/\b(ltd|limited|llc|inc|plc|co)\b/', '', $name) ?? $name;

        return trim(preg_replace('/\s+/', ' ', $name) ?? $name);
    }
}
