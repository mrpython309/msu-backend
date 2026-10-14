<?php

namespace App\Http\Requests\Branches;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LocationRequest extends FormRequest
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

         $locationId = $this->route('location');

        // If route-model binding is used
        if (is_object($locationId)) {
            $locationId = $locationId->id;
        }

        $locationNameRules = ['required', 'string', 'max:255'];
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');
        $currentLocation = $this->route('location');
        $currentName = is_object($currentLocation) ? (string) $currentLocation->location_name : null;
        $incomingName = (string) $this->input('location_name');

        if ($isUpdate) {
            // Allow saving when the name is unchanged for the same record/group.
            if ($currentName !== $incomingName) {
                $locationNameRules[] = Rule::unique('branch_locations', 'location_name')->ignore($locationId);
            }
        } else {
            $locationNameRules[] = Rule::unique('branch_locations', 'location_name')
                ->where(function ($query) {
                    return $query->where('branch_id', $this->branch_id);
                });
        }

        return [
            'location_name' => $locationNameRules,
            'branch_id' => 'required|exists:branches,id',
            'status'    => 'required|in:active,inactive',
        ];
    }
}
