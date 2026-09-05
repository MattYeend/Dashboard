<?php

namespace App\Http\Requests\Reports;

use App\Models\Report;
use Illuminate\Foundation\Http\FormRequest;

class ImportReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('import', Report::class);
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
