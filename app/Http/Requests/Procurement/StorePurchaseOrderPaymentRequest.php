<?php

namespace App\Http\Requests\Procurement;

use App\Models\PurchaseOrderPayment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseOrderPaymentRequest extends FormRequest
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

        if ($user->can('purchase-order.payment') || $user->can('purchase-orders:payment') || $user->can('purchase-order.pay')) {
            return true;
        }

        if ($user->hasAnyRole(['Accounts Department', 'Accounts', 'Finance & Accounts', 'Accounts Manager', 'Accounts Officer', 'L3 Approver', 'L3 Finance'])) {
            return true;
        }

        try {
            return $user->departments()->where(function ($q) {
                $q->where('department_name', 'like', '%Account%')
                  ->orWhere('department_name', 'like', '%Finance%');
            })->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    public function rules(): array
    {
        return [
            'payment_amount' => ['required', 'numeric', 'gt:0'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', Rule::in(PurchaseOrderPayment::METHODS)],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'attachment' => [
                'nullable',
                'file',
                'max:10240',
                'mimes:'.implode(',', PurchaseOrderPayment::ATTACHMENT_MIMES),
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'payment_amount.gt' => 'Payment amount must be greater than zero.',
            'attachment.mimes' => 'Invalid payment proof file type.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $po = $this->route('purchaseOrder');
            if ($po) {
                $amount = (float) $this->input('payment_amount');
                $remaining = $po->remainingAmount();
                if ($amount > $remaining + 0.001) {
                    $validator->errors()->add('payment_amount', 'Payment amount cannot exceed the remaining balance of ' . number_format($remaining, 2) . '.');
                }

                $isFirstPayment = $po->payments()->count() === 0;
                $minPercentage = (float) $po->minimum_advance_payment_percentage;
                $grandTotal = (float) $po->grand_total;

                if ($isFirstPayment && $minPercentage > 0 && $grandTotal > 0) {
                    $minRequiredAmount = round(($grandTotal * $minPercentage) / 100, 2);
                    if ($amount < $minRequiredAmount - 0.001) {
                        $validator->errors()->add('payment_amount', 'Minimum advance payment is required. Minimum required: ' . number_format($minRequiredAmount, 2));
                    }
                }
            }
        });
    }
}
