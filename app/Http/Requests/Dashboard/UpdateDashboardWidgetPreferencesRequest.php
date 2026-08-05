<?php

namespace App\Http\Requests\Dashboard;

use App\Support\DashboardWidgetRegistry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDashboardWidgetPreferencesRequest extends FormRequest
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
            'widgets' => ['required', 'array', 'min:1'],
            'widgets.*.key' => ['required', 'string', Rule::in(DashboardWidgetRegistry::keys())],
            'widgets.*.position' => ['required', 'integer', 'min:0'],
            'widgets.*.is_visible' => ['required', 'boolean'],
        ];
    }
}
