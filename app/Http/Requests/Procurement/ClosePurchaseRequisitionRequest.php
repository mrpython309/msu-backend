<?php

namespace App\Http\Requests\Procurement;

use Illuminate\Foundation\Http\FormRequest;

class ClosePurchaseRequisitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && (
            $user->hasAnyRole(['superadmin', 'Super Admin'])
            || $user->can('purchase-requisition.process')
            || $user->can('purchase-requisitions:process')
        );
    }

    public function rules(): array
    {
        return [
            'close_reason' => ['required', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'close_reason' => 'close reason',
        ];
    }
}
