<?php

namespace App\Http\Requests\Attachments;

use App\Models\Attachment;
use App\Services\Attachments\AttachableTypeRegistryService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAttachmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Attachment::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'attachable_type' => $this->attachableTypeRules(),
            'attachable_id' => $this->attachableIdRules(),
            'file' => $this->fileRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'attachable_type.required' => 'The attachable type is required.',
            'attachable_id.required' => 'The attachable ID is required.',
            'file.required' => 'A file is required.',
            'file.mimetypes' => 'That file type is not permitted.',
            'file.max' => 'The file may not be larger than '.(config('attachments.max_size_kb') / 1024).'MB.',
        ];
    }

    /**
     * @return array<mixed>
     */
    protected function attachableTypeRules(): array
    {
        return [
            'required',
            'string',
            Rule::in(array_keys(app(AttachableTypeRegistryService::class)->all())),
        ];
    }

    /**
     * @return array<mixed>
     */
    protected function attachableIdRules(): array
    {
        return ['required', 'integer', 'min:1'];
    }

    /**
     * MIME type is checked against actual file content by Laravel's
     * 'mimetypes' rule (finfo-backed), not the client-supplied extension.
     *
     * @return array<mixed>
     */
    protected function fileRules(): array
    {
        return [
            'required',
            'file',
            'mimetypes:'.implode(',', array_keys(config('attachments.allowed_mime_types'))),
            'max:'.config('attachments.max_size_kb'),
        ];
    }

    /**
     * Confirm the target record actually exists before we accept the upload.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $type = $this->input('attachable_type');
                $id = $this->input('attachable_id');

                if (! $type || ! $id) {
                    return;
                }

                $modelClass = app(AttachableTypeRegistryService::class)->modelClassForKey($type);

                if (! $modelClass) {
                    return;
                }

                if (! $modelClass::whereKey($id)->exists()) {
                    $validator->errors()->add('attachable_id', 'The selected record does not exist.');
                }
            },
        ];
    }
}
