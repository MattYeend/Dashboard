<?php

namespace App\Http\Requests\Logs;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexLogRequest extends FormRequest
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
            'search' => $this->searchRules(),
            'action' => $this->actionRules(),
            'logged_in_user_id' => $this->loggedInUserIdRules(),
            'related_to_user_id' => $this->relatedToUserIdRules(),
            'date_from' => $this->dateFromRules(),
            'date_to' => $this->dateToRules(),
            'sort_by' => $this->sortByRules(),
            'sort_direction' => $this->sortDirectionRules(),
            'per_page' => $this->perPageRules(),
            'page' => $this->pageRules(),
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
            'date_to.after_or_equal' => 'The end date must be on or after the start date.',
            'related_to_user_id.exists' => 'The related user could not be found.',
            'logged_in_user_id.exists' => 'The actor could not be found.',
        ];
    }

    /**
     * Get validation rules for the search field.
     *
     * @return array<mixed>
     */
    protected function searchRules(): array
    {
        return [
            'nullable',
            'string',
            'max:255',
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
            'nullable',
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
            'nullable',
            'date',
            'after_or_equal:date_from',
        ];
    }

    /**
     * Get validation rules for the sort_by field.
     *
     * @return array<mixed>
     */
    protected function sortByRules(): array
    {
        return [
            'nullable',
            Rule::in([
                'created_at',
                'action_id',
            ]),
        ];
    }

    /**
     * Get validation rules for the sort_direction field.
     *
     * @return array<mixed>
     */
    protected function sortDirectionRules(): array
    {
        return [
            'nullable',
            Rule::in([
                'asc',
                'desc',
            ]),
        ];
    }

    /**
     * Get validation rules for the per_page field.
     *
     * @return array<mixed>
     */
    protected function perPageRules(): array
    {
        return [
            'nullable',
            'integer',
            'min:1',
            'max:100',
        ];
    }

    /**
     * Get validation rules for the page field.
     *
     * @return array<mixed>
     */
    protected function pageRules(): array
    {
        return [
            'nullable',
            'integer',
            'min:1',
        ];
    }
}
