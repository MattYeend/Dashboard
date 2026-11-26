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
 * rejected, so stored filters can never become SQL injection.
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
     */
    public function query(CampaignSegment $segment): Builder
    {
        $query = Contact::query()->whereNotNull('email')->where('email', '!=', '');
        $filters = $segment->filters ?? [];

        $apply = function (Builder $q) use ($filters, $segment): void {
            foreach ($filters as $filter) {
                $this->assertAllowed($filter);
                $method = $segment->match === 'any' ? 'orWhere' : 'where';
                $this->condition($q, $method, $filter);
            }
        };

        // Group the rules so "any" never escapes the tenant scope or the email guard.
        return $filters === [] ? $query : $query->where($apply);
    }

    /**
     * @param  array{field?: string, operator?: string, value?: mixed}  $filter
     */
    private function assertAllowed(array $filter): void
    {
        $field = $filter['field'] ?? '';
        $operator = $filter['operator'] ?? '';

        if (! isset(self::FIELDS[$field]) || ! in_array($operator, self::FIELDS[$field], true)) {
            throw new InvalidArgumentException('Unsupported segment rule.');
        }
    }

    /**
     * @param  array{field: string, operator: string, value: mixed}  $filter
     */
    private function condition(Builder $q, string $method, array $filter): void
    {
        $field = $filter['field'];
        $value = (string) $filter['value'];

        match ($filter['operator']) {
            'equals' => $q->{$method}($field, $value),
            'contains' => $q->{$method}($field, 'like', '%'.addcslashes($value, '%_\\').'%'),
            'starts_with' => $q->{$method}($field, 'like', addcslashes($value, '%_\\').'%'),
            'after' => $q->{$method}($field, '>=', $value),
            'before' => $q->{$method}($field, '<=', $value),
        };
    }
}
