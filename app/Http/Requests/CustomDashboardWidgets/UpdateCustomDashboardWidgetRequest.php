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
     * Determine if the user is authorized to make this request.
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
            'label' => ['sometimes', 'required', 'string', 'max:255'],
            'metric_key' => ['sometimes', 'required', 'string', Rule::in(DashboardMetricRegistry::keys())],
            'date_range' => ['sometimes', 'required', 'string', Rule::enum(DashboardDateRange::class)],
            'position' => ['sometimes', 'required', 'integer', 'min:0'],
            'is_visible' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
