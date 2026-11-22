<?php

declare(strict_types=1);

namespace App\Http\Requests\Campaigns;

use App\Models\Campaign;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Multitenancy\Models\Tenant;

class StoreCampaignRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Campaign::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Only validated keys reach the services, so status, audit columns and
     * workflow timestamps can never be mass assigned from a request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => $this->nameRules(),
            'email_template_id' => $this->emailTemplateIdRules(),
            'campaign_segment_id' => $this->campaignSegmentIdRules(),
            'owner_id' => $this->ownerIdRules(),
            'subject' => $this->subjectRules(),
            'from_name' => $this->fromNameRules(),
            'reply_to' => $this->replyToRules(),
            'scheduled_at' => $this->scheduledAtRules(),
            'meta' => $this->metaRules(),
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
            'name.required' => 'The name is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not exceed 255 characters.',
            'email_template_id.required' => 'The email template is required.',
            'email_template_id.integer' => 'The email template must be a valid selection.',
            'email_template_id.exists' => 'The selected email template is not available.',
            'campaign_segment_id.required' => 'The campaign segment is required.',
            'campaign_segment_id.integer' => 'The campaign segment must be a valid selection.',
            'campaign_segment_id.exists' => 'The selected campaign segment is not available.',
            'owner_id.integer' => 'The owner must be a valid selection.',
            'owner_id.exists' => 'The selected owner is not available.',
            'subject.string' => 'The subject must be a string.',
            'subject.max' => 'The subject may not exceed 255 characters.',
            'from_name.string' => 'The from name must be a string.',
            'from_name.max' => 'The from name may not exceed 120 characters.',
            'reply_to.string' => 'The reply to address must be a string.',
            'reply_to.max' => 'The reply to address may not exceed 255 characters.',
            'reply_to.email' => 'The reply to address must be a valid email address.',
            'scheduled_at.date' => 'The scheduled date must be a valid date and time.',
        ];
    }

    /**
     * Get validation rules for the name field.
     *
     * @return array<mixed>
     */
    protected function nameRules(): array
    {
        return [
            'required',
            'string',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the email_template_id field.
     *
     * Scoped to the current organisation so another tenant's template can
     * never be attached.
     *
     * @return array<mixed>
     */
    protected function emailTemplateIdRules(): array
    {
        return [
            'required',
            'integer',
            Rule::exists('email_templates', 'id')
                ->where('organisation_id', Tenant::current()?->id)
                ->whereNull('deleted_at'),
        ];
    }

    /**
     * Get validation rules for the campaign_segment_id field.
     *
     * @return array<mixed>
     */
    protected function campaignSegmentIdRules(): array
    {
        return [
            'required',
            'integer',
            Rule::exists('campaign_segments', 'id')
                ->where('organisation_id', Tenant::current()?->id)
                ->whereNull('deleted_at'),
        ];
    }

    /**
     * Get validation rules for the owner_id field.
     *
     * @return array<mixed>
     */
    protected function ownerIdRules(): array
    {
        return [
            'nullable',
            'integer',
            Rule::exists('organisation_user', 'user_id')
                ->where('organisation_id', Tenant::current()?->id),
        ];
    }

    /**
     * Get validation rules for the subject field.
     *
     * @return array<mixed>
     */
    protected function subjectRules(): array
    {
        return [
            'nullable',
            'string',
            'max:255',
        ];
    }

    /**
     * Get validation rules for the from_name field.
     *
     * @return array<mixed>
     */
    protected function fromNameRules(): array
    {
        return [
            'nullable',
            'string',
            'max:120',
        ];
    }

    /**
     * Get validation rules for the reply_to field.
     *
     * @return array<mixed>
     */
    protected function replyToRules(): array
    {
        return [
            'nullable',
            'string',
            'max:255',
            'email:rfc',
        ];
    }

    /**
     * Get validation rules for the scheduled_at field.
     *
     * @return array<mixed>
     */
    protected function scheduledAtRules(): array
    {
        return [
            'nullable',
            'date',
        ];
    }

    /**
     * Get validation rules for the meta field.
     *
     * @return array<mixed>
     */
    protected function metaRules(): array
    {
        return [
            'nullable',
            'array',
        ];
    }
}
