<?php

declare(strict_types=1);

namespace App\Http\Requests\CampaignSegments\Concerns;

use App\Support\CampaignSegments\CriteriaRules;
use Closure;
use Illuminate\Validation\Rule;

/**
 * Validation rules for the criteria array, shared by the store and update requests.
 */
trait HasCriteriaRules
{
    /**
     * Build the criteria validation rules.
     *
     * @param  bool  $partial  Prefix the top-level rule with 'sometimes' (update requests).
     * @return array<string, array<int, mixed>>
     */
    protected function criteriaRules(bool $partial): array
    {
        return [
            'criteria' => [
                ...($partial ? ['sometimes'] : []),
                'nullable',
                'array',
                'list',
                'max:'.CriteriaRules::MAX_CRITERIA,
            ],
            'criteria.*' => ['array:field,operator,value'],
            'criteria.*.field' => [
                'required',
                'string',
                Rule::in(CriteriaRules::fieldNames()),
            ],
            'criteria.*.operator' => [
                'required',
                'string',
                Rule::in(CriteriaRules::operatorNames()),
                $this->operatorMatchesField(),
            ],
            'criteria.*.value' => [
                'required',
                $this->valueMatchesField(),
            ],
        ];
    }

    /**
     * Reject an operator that the selected field does not accept.
     */
    private function operatorMatchesField(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $field = $this->input(substr($attribute, 0, -strlen('operator')).'field');

            if (! is_string($field) || ! is_string($value)) {
                return;
            }

            $allowed = CriteriaRules::operatorsFor($field);

            if ($allowed !== [] && ! in_array($value, $allowed, true)) {
                $fail('The selected operator is not valid for the selected field.');
            }
        };
    }

    /**
     * Reject a value of the wrong type or range for the selected field.
     */
    private function valueMatchesField(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $field = $this->input(substr($attribute, 0, -strlen('value')).'field');

            if (! is_string($field) || CriteriaRules::typeFor($field) === null) {
                return;
            }

            if (! CriteriaRules::isValidValue($field, $value)) {
                $fail('The value is not valid for the selected field.');
            }
        };
    }
}