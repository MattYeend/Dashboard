<?php

namespace App\Http\Requests\CustomDashboardWidgets;

use App\Enums\DashboardDateRange;
use App\Models\CustomDashboardWidget;
use App\Support\DashboardMetricRegistry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomDashboardWidgetRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        /** @var CustomDashboardWidget|null $widget */
        $widget = $this->route('customDashboardWidget');

        return $widget !== null && $widget->user_id === $this->user()?->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'label' => $this->labelRules(),
            'description' => $this->descriptionRules(),
            'metric_key' => $this->metricKeyRules(),
            'date_range' => $this->dateRangeRules(),
            'position' => $this->positionRules(),
            'is_visible' => $this->isVisibleRules(),
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'label.required' => 'The label is required.',
            'label.max' => 'The label may not exceed 255 characters.',
            'description.max' => 'The description may not exceed 1000 characters.',
            'metric_key.required' => 'Please choose a metric.',
            'metric_key.in' => 'The selected metric is not valid.',
            'date_range.required' => 'Please choose a date range.',
            'date_range.enum' => 'The selected date range is not valid.',
            'position.min' => 'The position cannot be negative.',
        ];
    }

    /**
     * Get validation rules for the label field.
     *
     * @return array<mixed>
     */
    protected function labelRules(): array
    {
        return [
            'sometimes',
            'required',
            'string',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the description field.
     *
     * @return array<mixed>
     */
    protected function descriptionRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'string',
            'max:1000',
        ];
    }

    /**
     * Get validation rules for the metric_key field.
     *
     * @return array<mixed>
     */
    protected function metricKeyRules(): array
    {
        return [
            'sometimes',
            'required',
            'string',
            Rule::in(DashboardMetricRegistry::keys()),
        ];
    }

    /**
     * Get validation rules for the date_range field.
     *
     * @return array<mixed>
     */
    protected function dateRangeRules(): array
    {
        return [
            'sometimes',
            'required',
            'string',
            Rule::enum(DashboardDateRange::class),
        ];
    }

    /**
     * Get validation rules for the position field.
     *
     * @return array<mixed>
     */
    protected function positionRules(): array
    {
        return [
            'sometimes',
            'required',
            'integer',
            'min:0',
        ];
    }

    /**
     * Get validation rules for the is_visible field.
     *
     * @return array<mixed>
     */
    protected function isVisibleRules(): array
    {
        return [
            'sometimes',
            'required',
            'boolean',
        ];
    }
}
