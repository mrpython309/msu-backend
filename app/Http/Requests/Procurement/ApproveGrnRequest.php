<?php

namespace App\Http\Requests\Procurement;

use Illuminate\Foundation\Http\FormRequest;

class ApproveGrnRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(['superadmin', 'Super Admin'])) {
            return true;
        }

        if ($user->can('grn.approve') || $user->can('grn.inspect') || $user->can('grns:approve')) {
            return true;
        }

        $allowedEmail = strtolower((string) config('procurement.grn_create_email', 'store123@gmail.com'));

        return $allowedEmail !== ''
            && strtolower((string) $user->email) === $allowedEmail;
    }

    public function rules(): array
    {
        return [
            'inspection_status' => 'required|in:passed,failed,pending',
            'inspection_remarks' => 'nullable|string|max:2000',
            'comments' => 'nullable|string|max:2000',
            'generate_assets' => 'nullable|boolean',
            'items' => 'required|array|min:1',
            'items.*.grn_item_id' => 'required|exists:grn_items,id',
            'items.*.received_quantity' => 'required|integer|min:0',
            'items.*.rejected_quantity' => 'required|integer|min:0',
            'items.*.accepted_quantity' => 'nullable|integer|min:0',
            'items.*.remarks' => 'nullable|string|max:2000',
        ];
    }
}
