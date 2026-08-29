<?php

namespace App\Http\Requests\Notifications;

use App\Models\NotificationBroadcast;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNotificationBroadcastRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', NotificationBroadcast::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => $this->titleRules(),
            'body' => $this->bodyRules(),
            'audience_type' => $this->audienceTypeRules(),
            'audience_ids' => $this->audienceIdsRules(),
            'audience_ids.*' => $this->audienceIdRules(),
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
            'title.required' => 'The title is required.',
            'title.max' => 'The title may not exceed 255 characters.',
            'body.required' => 'The body is required.',
            'body.max' => 'The body may not exceed 5000 characters.',
            'audience_type.required' => 'The audience type is required.',
            'audience_type.in' => 'The selected audience type is invalid.',
            'audience_ids.required_unless' => 'The audience must be specified unless targeting all users.',
        ];
    }

    /**
     * Get validation rules for the title field.
     *
     * @return array<mixed>
     */
    protected function titleRules(): array
    {
        return [
            'required',
            'string',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the body field.
     *
     * @return array<mixed>
     */
    protected function bodyRules(): array
    {
        return [
            'required',
            'string',
            'max:5000',
        ];
    }

    /**
     * Get validation rules for the audience_type field.
     *
     * @return array<mixed>
     */
    protected function audienceTypeRules(): array
    {
        return [
            'required',
            Rule::in(['all', 'role', 'users']),
        ];
    }

    /**
     * Get validation rules for the audience_ids field.
     *
     * @return array<mixed>
     */
    protected function audienceIdsRules(): array
    {
        return [
            'nullable',
            'array',
            'required_unless:audience_type,all',
        ];
    }

    /**
     * Get validation rules for the audience_ids.* field.
     *
     * @return array<mixed>
     */
    protected function audienceIdRules(): array
    {
        return [
            'string',
        ];
    }
}
