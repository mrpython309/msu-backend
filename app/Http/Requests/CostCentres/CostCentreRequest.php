<?php

namespace App\Http\Requests\CostCentres;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CostCentreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $costCentreId = $this->route('costCentre');

        if (is_object($costCentreId)) {
            $costCentreId = $costCentreId->id;
        }

        return [
            'cost_centre_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cost_centres', 'cost_centre_name')->ignore($costCentreId),
            ],
            'cost_centre_description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ];
    }
}
