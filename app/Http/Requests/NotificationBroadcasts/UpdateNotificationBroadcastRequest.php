<?php

declare(strict_types=1);

namespace App\Http\Requests\NotificationBroadcasts;

use App\Models\NotificationBroadcast;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotificationBroadcastRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('notification_broadcast'));
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
            'audience_ids.required_if' => 'The audience must be specified when targeting roles or specific users.',
            'audience_ids.prohibited_if' => 'An audience list cannot be provided when targeting all users.',
            'audience_ids.max' => 'You may not select more than 1000 audience entries.',
            'audience_ids.*.exists' => 'One or more selected audience entries do not exist.',
            'audience_ids.*.distinct' => 'The audience list contains duplicate entries.',
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
            'sometimes',
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
            'sometimes',
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
            'sometimes',
            'required',
            'string',
            Rule::in(NotificationBroadcast::AUDIENCES),
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
            'max:1000',
            'required_if:audience_type,' . NotificationBroadcast::AUDIENCE_ROLE . ',' . NotificationBroadcast::AUDIENCE_USERS,
            'prohibited_if:audience_type,' . NotificationBroadcast::AUDIENCE_ALL,
        ];
    }

    /**
     * Get validation rules for the audience_ids.* field.
     *
     * @return array<mixed>
     */
    protected function audienceIdRules(): array
    {
        $type = $this->input(
            'audience_type',
            $this->route('notification_broadcast')?->audience_type,
        );

        return match ($type) {
            NotificationBroadcast::AUDIENCE_USERS => [
                'integer',
                'distinct',
                Rule::exists('users', 'id')->whereNull('deleted_at'),
            ],
            NotificationBroadcast::AUDIENCE_ROLE => [
                'string',
                'distinct',
                Rule::exists('roles', 'name')->where('guard_name', 'web'),
            ],
            default => ['string'],
        };
    }
}
