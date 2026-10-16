<?php

namespace App\Http\Requests\Procurement;

class UpdatePurchaseOrderRequest extends StorePurchaseOrderRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && (
            $user->hasAnyRole(['superadmin', 'Super Admin'])
            || $user->can('purchase-order.edit')
            || $user->can('purchase-orders:edit')
        );
    }
}
