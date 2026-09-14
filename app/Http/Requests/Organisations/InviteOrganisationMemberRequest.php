<?php

namespace App\Http\Requests\Organisations;

use App\Models\Organisation;
use App\Services\Organisations\PolicyAuthorisationService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteOrganisationMemberRequest extends FormRequest
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
            'email' => $this->emailRules(),
            'invited_role' => $this->invitedRoleRules(),
        ];
    }

    /**
     * Rules for the invited email address.
     *
     * @return array<int, mixed>
     */
    protected function emailRules(): array
    {
        return ['required', 'email', 'max:255'];
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
