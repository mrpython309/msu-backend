<?php

namespace App\Http\Requests\Assets;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Asset;

class AssetReplacementStoreRequest extends FormRequest
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
      
         $assetId = $this->asset_id;

    $serialRule = Rule::unique('assets', 'serial_no');

    // If updating, ignore current record
    if ($this->isMethod('put') || $this->isMethod('patch')) {
        $serialRule = $serialRule->ignore($assetId);
    }

    return [

        'asset_id' => [
            'required',
            'exists:assets,id'
        ],

        'replacement_date' => [
            'required',
            'date',
            'before_or_equal:today'
        ],

        'new_serial_no' => [
            'required',
            'string',
            'max:255',
            $serialRule
        ],

        'new_condition' => [
            'required',
            'string',
            'max:50'
        ],

        'new_operational_status' => [
            'required',
            'string',
            'max:50'
        ],

        'replacement_reason' => [
            'required',
            'string',
            'max:1000'
        ],
    ];
    
    }


    /**
     * Custom Messages
     */
    public function messages(): array
    {
        return [

            'asset_id.required' => 'Invalid asset selected.',
            'asset_id.exists'   => 'Asset does not exist.',

            'replacement_date.required' => 'Replacement date is required.',
            'replacement_date.before_or_equal' => 'Replacement date cannot be future date.',

            'new_serial_no.required' => 'New serial number is required.',
            'new_serial_no.unique'   => 'This serial number already exists.',

            'new_condition.required' => 'Please select new condition.',
            'new_operational_status.required' => 'Please select operational status.',

            'replacement_reason.required' => 'Replacement reason is required.',
        ];
    }

    /**
     * Prepare Data Before Validation
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'asset_id' => $this->asset_id ?? request()->route('asset'),
        ]);
    }
}
