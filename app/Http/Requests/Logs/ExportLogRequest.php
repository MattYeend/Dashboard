<?php

namespace App\Http\Requests\Logs;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ExportLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
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
            'action' => $this->actionRules(),
            'logged_in_user_id' => $this->loggedInUserIdRules(),
            'related_to_user_id' => $this->relatedToUserIdRules(),
            'date_from' => $this->dateFromRules(),
            'date_to' => $this->dateToRules(),
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
            'date_from.required' => 'A start date is required to export the audit trail.',
            'date_to.required' => 'An end date is required to export the audit trail.',
            'date_to.after_or_equal' => 'The end date must be on or after the start date.',
        ];
    }

    /**
     * Enforce the maximum export window.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $days = CarbonImmutable::parse($this->input('date_from'))->diffInDays(CarbonImmutable::parse($this->input('date_to')));
                $max = (int) config('audit.export_max_days', 92);

                if ($days > $max) {
                    $validator->errors()->add('date_to', "Exports are limited to {$max} days at a time.");
                }
            },
        ];
    }

    /**
     * Get validation rules for the action field.
     *
     * @return array<mixed>
     */
    protected function actionRules(): array
    {
        return [
            'nullable',
            'integer',
            'min:0',
        ];
    }

    /**
     * Get validation rules for the logged_in_user_id field.
     *
     * @return array<mixed>
     */
    protected function loggedInUserIdRules(): array
    {
        return [
            'nullable',
            'integer',
            'exists:users,id',
        ];
    }

    /**
     * Get validation rules for the related_to_user_id field.
     *
     * @return array<mixed>
     */
    protected function relatedToUserIdRules(): array
    {
        return [
            'nullable',
            'integer',
            'exists:users,id',
        ];
    }

    /**
     * Get validation rules for the date_from field.
     *
     * @return array<mixed>
     */
    protected function dateFromRules(): array
    {
        return [
            'required',
            'date',
        ];
    }

    /**
     * Get validation rules for the date_to field.
     *
     * @return array<mixed>
     */
    protected function dateToRules(): array
    {
        return [
            'required',
            'date',
            'after_or_equal:date_from',
        ];
    }
}
