<?php

namespace App\Http\Requests\Activities;

use App\Enums\ActivityType;
use App\Services\Activities\ActivityableTypeRegistryService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('activity'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'activityable_type' => $this->activityableTypeRules(),
            'activityable_id' => $this->activityableIdRules(),
            'type' => $this->typeRules(),
            'description' => $this->descriptionRules(),
            'meta' => $this->metaRules(),
            'occurred_at' => $this->occurredAtRules(),
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
            'activityable_type.required' => 'The activityable type is required.',
            'activityable_type.string' => 'The activityable type must be a string.',
            'activityable_id.required' => 'The activityable ID is required.',
            'activityable_id.integer' => 'The activityable ID must be an integer.',
            'activityable_id.min' => 'The activityable ID must be at least 1.',
            'type.required' => 'The activity type is required.',
            'description.max' => 'The description may not exceed 5000 characters.',
        ];
    }

    /**
     * Get validation rules for the activityable_type field.
     *
     * @return array<mixed>
     */
    protected function activityableTypeRules(): array
    {
        return [
            'sometimes',
            'required',
            'string',
            Rule::in(array_keys(
                app(ActivityableTypeRegistryService::class)->all()
            )),
        ];
    }

    /**
     * Get validation rules for the activityable_id field.
     *
     * @return array<mixed>
     */
    protected function activityableIdRules(): array
    {
        return [
            'sometimes',
            'required',
            'integer',
            'min:1',
        ];
    }

    /**
     * Get validation rules for the type field.
     *
     * @return array<mixed>
     */
    protected function typeRules(): array
    {
        return [
            'sometimes',
            'required',
            Rule::enum(ActivityType::class),
        ];
    }

    /**
     * Get validation rules for the description field.
     *
     * @return array<mixed>
     */
    protected function descriptionRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'string',
            'max:5000',
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
            'sometimes',
            'nullable',
            'array',
        ];
    }

    /**
     * Get validation rules for the occurred_at field.
     *
     * @return array<mixed>
     */
    protected function occurredAtRules(): array
    {
        return [
            'sometimes',
            'nullable',
            'date',
        ];
    }

    /**
     * Perform additional validation after the standard rules have passed.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $type = $this->input('activityable_type');
                $id = $this->input('activityable_id');

                if (! $type || ! $id) {
                    return;
                }

                $modelClass = app(ActivityableTypeRegistryService::class)
                    ->modelClassForKey($type);

                if (! $modelClass) {
                    return;
                }

                if (! $modelClass::whereKey($id)->exists()) {
                    $validator->errors()->add(
                        'activityable_id',
                        'The selected activity owner does not exist.'
                    );
                }
            },
        ];
    }
}
