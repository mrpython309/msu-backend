<?php

namespace App\Http\Requests\Assets;

use Illuminate\Foundation\Http\FormRequest;

class SaveAssetCategoryRequest extends FormRequest
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

         $assetCategoryId = $this->route('asset_category');

        // If route-model binding is used
        if (is_object($assetCategoryId)) {
            $assetCategoryId = $assetCategoryId->id;
        }


        return [
            'category_name' => [
                'required',
                'string',
                'max:255',
                'unique:asset_categories,category_name,' . $assetCategoryId,
            ],
            'parent_id' => [
                'nullable',
                'exists:asset_categories,id',
                'not_in:' . $assetCategoryId, // prevent self-parent
            ],
            'status' => [
                'required',
                'in:active,inactive',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'category_name.required' => 'Category name is required.',
            'category_name.unique'   => 'This category name already exists.',
            'parent_id.exists'       => 'Selected parent category is invalid.',
            'parent_id.not_in'       => 'A category cannot be its own parent.',
            'status.in'              => 'Status must be active or inactive.',
        ];
    }
}
