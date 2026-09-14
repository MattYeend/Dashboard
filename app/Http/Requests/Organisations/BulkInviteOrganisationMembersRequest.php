<?php

namespace App\Http\Requests\Organisations;

use App\Models\Organisation;
use App\Services\Organisations\PolicyAuthorisationService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkInviteOrganisationMembersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('invite members');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'emails' => $this->emailsRules(),
            'emails.*' => $this->emailRules(),
            'invited_role' => $this->invitedRoleRules(),
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
            'emails.required' => 'Please provide at least one email address to invite.',
            'emails.array' => 'Emails must be provided as a list.',
            'emails.min' => 'Please provide at least one email address to invite.',
            'emails.max' => 'You may not invite more than 200 people at once.',
            'emails.*.max' => 'One of the provided email addresses is too long.',
            'invited_role.required' => 'Please select a role to invite members as.',
            'invited_role.in' => 'The selected role is not available for this organisation.',
        ];
    }

    /**
     * Rules for the list of emails to invite. Deliberately not validated
     * as `email` format here - malformed entries are classified as
     * 'invalid' by the service rather than failing the whole request.
     *
     * @return array<int, mixed>
     */
    protected function emailsRules(): array
    {
        return ['required', 'array', 'min:1', 'max:200'];
    }

    /**
     * Rules for each entry in the emails array.
     *
     * @return array<int, mixed>
     */
    protected function emailRules(): array
    {
        return ['string', 'max:255'];
    }

    /**
     * Rules for the invited_role field.
     *
     * @return array<int, mixed>
     */
    protected function invitedRoleRules(): array
    {
        /** @var Organisation $organisation */
        $organisation = $this->route('organisation');

        $availableRoles = app(PolicyAuthorisationService::class)
            ->assignableRolesFor($this->user(), $organisation)
            ->pluck('name');

        return ['required', 'string', Rule::in($availableRoles)];
    }
}
