<?php

declare(strict_types=1);

namespace App\Http\Requests\CampaignSegments;

use App\Http\Requests\CampaignSegments\Concerns\HasCriteriaRules;
use App\Models\Organisation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCampaignSegmentRequest extends FormRequest
{
    use HasCriteriaRules;

    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('campaignSegment')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Only validated keys reach the services, so audit columns and
     * system-managed fields can never be mass assigned from a request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => $this->nameRules(),
            'description' => $this->descriptionRules(),
            ...$this->criteriaRules(partial: true),
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a segment name.',
            'name.unique' => 'A segment with this name already exists.',
            'criteria.max' => 'A segment can have at most 20 criteria.',
            'criteria.*.array' => 'Each criterion may only contain a field, an operator and a value.',
        ];
    }

    /**
     * Rules for the name field, unique within the current organisation,
     * ignoring soft deleted rows and the segment being edited.
     *
     * @return array<int, mixed>
     */
    protected function nameRules(): array
    {
        return [
            'sometimes',
            'required',
            'string',
            'max:255',
            Rule::unique('campaign_segments', 'name')
                ->where(function (Builder $query): void {
                    $query->where('organisation_id', Organisation::current()?->id)
                        ->whereNull('deleted_at');
                })
                ->ignore($this->route('campaignSegment')),
        ];
    }

    /**
     * Rules for the description field.
     *
     * @return array<int, mixed>
     */
    protected function descriptionRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'string',
            'max:2000',
        ];
    }
}