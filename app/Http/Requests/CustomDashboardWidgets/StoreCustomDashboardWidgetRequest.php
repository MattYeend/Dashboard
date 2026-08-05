<?php

namespace App\Http\Requests\CustomDashboardWidgets;

use App\Enums\DashboardDateRange;
use App\Support\DashboardMetricRegistry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomDashboardWidgetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'metric_key' => ['required', 'string', Rule::in(DashboardMetricRegistry::keys())],
            'date_range' => ['required', 'string', Rule::enum(DashboardDateRange::class)],
        ];
    }
}
