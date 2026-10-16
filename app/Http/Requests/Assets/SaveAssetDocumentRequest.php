<?php

namespace App\Http\Requests\Assets;

use Illuminate\Foundation\Http\FormRequest;

class SaveAssetDocumentRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
      $rules = [
        'asset_id' => ['required', 'exists:assets,id'],
        'document_name' => ['required', 'string', 'max:255'],
        'document_notes' => ['nullable', 'string', 'max:5000'],
    ];

    if ($this->isMethod('post')) {
        $rules['filename'] = ['required', 'file', 'max:2048', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx'];
    }

    if ($this->isMethod('put') || $this->isMethod('patch')) {
        $rules['filename'] = ['nullable', 'file', 'max:2048', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx'];
    }

    return $rules;
    }
}
