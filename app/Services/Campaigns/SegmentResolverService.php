<?php

declare(strict_types=1);

namespace App\Services\Campaigns;

use App\Models\CampaignSegment;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

/**
 * Turns a saved segment into a tenant scoped Contact query.
 *
 * Only the fields and operators listed here can be used. Anything else is
 * rejected, so stored criteria can never become SQL injection.
 *
 * Every criterion must match (AND). A segment with no criteria matches
 * every contact that has an email address.
 */
class SegmentResolverService
{
    /**
     * Field => allowed operators.
     *
     * @var array<string, array<int, string>>
     */
    public const FIELDS = [
        'contactable_type' => ['equals'],
        'city' => ['equals', 'contains'],
        'country' => ['equals'],
        'postal_code' => ['starts_with'],
        'created_at' => ['after', 'before'],
    ];

    /**
     * Build the query for a segment. BelongsToOrganisation scopes it.
     *
     * @return Builder<Contact>
     *
     * @throws InvalidArgumentException When a criterion uses an unsupported field or operator.
     */
    public function query(CampaignSegment $segment): Builder
    {
        $query = Contact::query()->whereNotNull('email')->where('email', '!=', '');

        foreach ($segment->criteria ?? [] as $criterion) {
            $this->assertAllowed($criterion);
            $this->condition($query, $criterion);
        }

        return $query;
    }

    /**
     * @param  array{field?: string, operator?: string, value?: mixed}  $criterion
     */
    private function assertAllowed(array $criterion): void
    {
        $field = $criterion['field'] ?? '';
        $operator = $criterion['operator'] ?? '';

        if (! isset(self::FIELDS[$field]) || ! in_array($operator, self::FIELDS[$field], true)) {
            throw new InvalidArgumentException('Unsupported segment rule.');
        }
    }

    /**
     * @param  Builder<Contact>  $q
     * @param  array{field: string, operator: string, value: mixed}  $criterion
     */
    private function condition(Builder $q, array $criterion): void
    {
        $field = $criterion['field'];
        $value = (string) $criterion['value'];

        match ($criterion['operator']) {
            'equals' => $q->where($field, $value),
            'contains' => $q->where($field, 'like', '%'.addcslashes($value, '%_\\').'%'),
            'starts_with' => $q->where($field, 'like', addcslashes($value, '%_\\').'%'),
            'after' => $q->where($field, '>=', $value),
            'before' => $q->where($field, '<=', $value),
            default => throw new InvalidArgumentException('Unsupported segment rule.'),
        };
    }
}
