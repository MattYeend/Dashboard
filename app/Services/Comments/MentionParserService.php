<?php

namespace App\Services\Comments;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MentionParserService
{
    /**
     * Extract mentioned users from a comment body.
     *
     * Matches @token patterns against a slugified version of each user's
     * name (e.g. "John Smith" becomes "johnsmith"). A token that matches
     * more than one user (ambiguous) or no user at all is left as plain
     * text and excluded from the result.
     *
     * @return Collection<int, User>
     */
    public function extractMentions(string $body): Collection
    {
        $tokens = $this->matchTokens($body);

        if ($tokens->isEmpty()) {
            return collect();
        }

        $slugToUsers = User::query()
            ->select(['id', 'name'])
            ->get()
            ->groupBy(fn (User $user): string => $this->slugify($user->name));

        return $tokens
            ->unique()
            ->map(fn (string $token): ?Collection => $slugToUsers->get($token))
            ->filter(fn (?Collection $matches): bool => $matches !== null && $matches->count() === 1)
            ->map(fn (Collection $matches): User => $matches->first())
            ->values();
    }

    /**
     * Pull raw @token strings out of the body.
     *
     * @return Collection<int, string>
     */
    protected function matchTokens(string $body): Collection
    {
        preg_match_all('/@([a-zA-Z0-9]+)/', $body, $matches);

        return collect($matches[1] ?? [])
            ->map(fn (string $token): string => strtolower($token));
    }

    /**
     * Convert a user's name into the slug used for mention matching.
     */
    protected function slugify(string $name): string
    {
        return Str::lower(Str::slug($name, ''));
    }
}
