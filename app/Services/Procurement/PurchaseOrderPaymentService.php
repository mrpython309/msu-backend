<?php

namespace App\Services\Procurement;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderPayment;
use App\Models\User;
use App\Services\Procurement\Exceptions\ProcurementException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PurchaseOrderPaymentService
{
    public function __construct(
        private readonly ProcurementActivityLogger $logger,
    ) {}

    public function recordPayment(PurchaseOrder $purchaseOrder, User $actor, array $data, ?UploadedFile $attachment = null): PurchaseOrderPayment
    {
        return DB::transaction(function () use ($purchaseOrder, $actor, $data, $attachment) {
            $locked = PurchaseOrder::query()
                ->whereKey($purchaseOrder->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $locked->allowsPayment()) {
                $status = $locked->statusEnum()?->label() ?? (string) $locked->status;
                throw new ProcurementException(
                    'Payment is not allowed until this Purchase Order is fully approved. Current status: '.$status.'.'
                );
            }

            $remaining = $locked->remainingAmount();
            $amount = round((float) $data['payment_amount'], 2);

            if ($amount <= 0) {
                throw new ProcurementException('Payment amount must be greater than zero.');
            }

            if ($remaining <= 0) {
                throw new ProcurementException('Purchase Order is fully paid. No further payments can be recorded.');
            }

            if ($amount > $remaining + 0.001) {
                throw new ProcurementException(
                    'Payment amount cannot exceed the remaining balance of '.number_format($remaining, 2).'.'
                );
            }

            $isFirstPayment = $locked->payments()->count() === 0;
            $minPercentage = (float) $locked->minimum_advance_payment_percentage;
            $grandTotal = (float) $locked->grand_total;
            
            if ($isFirstPayment && $minPercentage > 0 && $grandTotal > 0) {
                $minRequiredAmount = round(($grandTotal * $minPercentage) / 100, 2);
                if ($amount < $minRequiredAmount - 0.001) {
                    throw new ProcurementException("Minimum advance payment is required. Minimum required: " . number_format($minRequiredAmount, 2));
                }
            }

            if ($amount > $remaining + 0.001) {
                throw new ProcurementException(
                    'Payment amount cannot exceed the remaining balance of '.number_format($remaining, 2).'.'
                );
            }

            $path = null;
            if ($attachment) {
                $path = $attachment->store('purchase-order-payments/'.$locked->id, 'public');
            }

            $payment = PurchaseOrderPayment::create([
                'purchase_order_id' => $locked->id,
                'payment_amount' => $amount,
                'payment_method' => $data['payment_method'],
                'payment_date' => $data['payment_date'],
                'reference_number' => $data['reference_number'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'attachment' => $path,
                'created_by' => $actor->id,
            ]);

            $this->logger->log(
                $locked,
                'PO Payment Recorded',
                null,
                null,
                'Payment of '.number_format($amount, 2).' via '.$data['payment_method'],
                [
                    'payment_id' => $payment->id,
                    'payment_amount' => $amount,
                    'payment_method' => $data['payment_method'],
                    'remaining_after' => $locked->fresh()->remainingAmount(),
                ],
                $actor->id
            );

            return $payment->load('createdBy');
        });
    }

    /**
     * @return array{total: float, paid: float, remaining: float, status: string, status_badge: string, is_fully_paid: bool}
     */
    public function summary(PurchaseOrder $purchaseOrder): array
    {
        $total = $purchaseOrder->orderTotalAmount();
        $paid = $purchaseOrder->paidAmount();
        $remaining = max(0, round($total - $paid, 2));
        $status = $purchaseOrder->paymentStatusEnum();

        $minAdvanceAmount = 0.01;
        if ($purchaseOrder->payments()->count() === 0 && (float)$purchaseOrder->minimum_advance_payment_percentage > 0) {
            $minAdvanceAmount = round(($total * (float)$purchaseOrder->minimum_advance_payment_percentage) / 100, 2);
        }

        return [
            'total' => $total,
            'paid' => $paid,
            'remaining' => $remaining,
            'status' => $status->value,
            'status_badge' => $status->badgeClass(),
            'is_fully_paid' => $remaining <= 0,
            'allows_payment' => $purchaseOrder->allowsPayment(),
            'currency' => $purchaseOrder->currency ?: 'INR',
            'po_no' => $purchaseOrder->po_no,
            'vendor_name' => $purchaseOrder->vendor->name ?? '--',
            'min_advance_amount' => $minAdvanceAmount,
        ];
    }
}
