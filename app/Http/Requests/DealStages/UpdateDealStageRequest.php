<?php

namespace App\Http\Requests\DealStages;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDealStageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'stage_id' => [
                'required',
                'integer',
                Rule::exists('pipeline_stages', 'id')->where(
                    fn ($query) => $query->where('pipeline_id', $this->route('deal')->pipeline_id)
                ),
            ],
        ];
    }
}
