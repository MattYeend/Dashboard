<?php

namespace App\Http\Requests\Addresses;

use App\Models\Address;
use Illuminate\Foundation\Http\FormRequest;

class ImportAddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('import', Address::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'max:5120', // 5MB
            ],
        ];
    }
}
