<?php

declare(strict_types=1);

namespace App\Http\Requests\Campaigns;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ScheduleCampaignRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('schedule', $this->route('campaign')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * The one minute allowance matches CampaignTransitionService, so a
     * "send now" request is not rejected because of a few seconds delay.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'scheduled_at' => [
                'required', 
                'date', 
                'after_or_equal:-1 minute'
            ],
        ];
    }
}