<?php

namespace App\Http\Requests\Assets;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssetTypeRequest extends FormRequest
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
         $assetTypeId = $this->route('asset_type');

        // If route-model binding is used
        if (is_object($assetTypeId)) {
            $assetTypeId = $assetTypeId->id;
        }

 

        return [
            'asset_type_name' => [
                'required',
                'string',
                'max:255',
                // Unique: ignore current record if updating
                Rule::unique('asset_types', 'asset_type_name')->ignore($assetTypeId),
            ],
            'category_id' => 'required|exists:asset_categories,id',
            'sub_category_id' => 'required|exists:asset_categories,id',
            'asset_type_description' => 'nullable|string',
            'status' => ['required', Rule::in(['active','inactive'])],
        ];
    }
}
