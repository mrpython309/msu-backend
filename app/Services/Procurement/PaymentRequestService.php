<?php

namespace App\Services\Procurement;

use App\Models\PaymentRequest;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Services\Procurement\Exceptions\ProcurementException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PaymentRequestService
{
    public function __construct(
        private readonly DocumentNumberGenerator $numbers,
        private readonly PurchaseOrderPaymentService $poPaymentService,
        private readonly ProcurementActivityLogger $logger,
    ) {}

    public function createFromPurchaseOrder(PurchaseOrder $purchaseOrder, User $actor, ?string $remarks = null, ?float $requestedAmount = null): PaymentRequest
    {
        return DB::transaction(function () use ($purchaseOrder, $actor, $remarks, $requestedAmount) {
            $locked = PurchaseOrder::query()
                ->whereKey($purchaseOrder->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $locked->allowsPayment() && ! $locked->isCompletedStatus()) {
                $status = $locked->statusEnum()?->label() ?? (string) $locked->status;
                throw new ProcurementException(
                    'Payment request cannot be raised until the Purchase Order is approved or completed. Current status: '.$status.'.'
                );
            }

            if ($locked->activePaymentRequest()) {
                throw new ProcurementException(
                    'A Purchase Order can have only one active payment request at a time.'
                );
            }

            $remaining = $locked->remainingAmount();
            if ($remaining <= 0) {
                throw new ProcurementException(
                    'This Purchase Order is already fully paid.'
                );
            }

            $amountToRequest = ($requestedAmount !== null && $requestedAmount > 0) ? (float) $requestedAmount : (float) $remaining;
            if ($amountToRequest > $remaining + 0.01) {
                throw new ProcurementException(
                    'Requested payment amount (' . number_format($amountToRequest, 2) . ') cannot exceed the remaining payable balance (' . number_format($remaining, 2) . ').'
                );
            }

            // Check minimum advance payment percentage requirement
            $grandTotal = (float) $locked->grand_total;
            $minPercentage = (float) $locked->minimum_advance_payment_percentage;
            if ($grandTotal > 0 && $minPercentage > 0) {
                $minRequiredAmount = round(($grandTotal * $minPercentage) / 100, 2);
                $threshold = min($remaining, $minRequiredAmount);
                if ($amountToRequest < $threshold - 0.01) {
                    $actualPercentage = round(($amountToRequest / $grandTotal) * 100, 2);
                    throw new ProcurementException(
                        "Payment request percentage ({$actualPercentage}%) is below the configured minimum advance payment ({$minPercentage}% / " . number_format($minRequiredAmount, 2) . "). Please request at least " . number_format($threshold, 2) . "."
                    );
                }
            }

            $requestNo = $this->numbers->next('payreq', PaymentRequest::class, 'payment_request_no');

            $paymentRequest = PaymentRequest::create([
                'payment_request_no' => $requestNo,
                'purchase_order_id' => $locked->id,
                'requested_by' => $actor->id,
                'request_date' => now(),
                'amount' => round($amountToRequest, 2),
                'status' => PaymentRequest::STATUS_PENDING,
                'remarks' => $remarks,
            ]);

            $this->logger->log(
                $locked,
                'Payment Request Created',
                null,
                null,
                "Payment request {$paymentRequest->payment_request_no} created for amount ".number_format($amountToRequest, 2),
                ['payment_request_id' => $paymentRequest->id],
                $actor->id
            );

            return $paymentRequest->load(['purchaseOrder.vendor', 'requestedBy']);
        });
    }

    public function approve(PaymentRequest $paymentRequest, User $actor, ?string $remarks = null): PaymentRequest
    {
        return DB::transaction(function () use ($paymentRequest, $actor, $remarks) {
            $locked = PaymentRequest::query()->whereKey($paymentRequest->getKey())->lockForUpdate()->firstOrFail();

            if (! $locked->isPending()) {
                throw new ProcurementException('Only pending payment requests can be approved.');
            }

            $locked->update([
                'status' => PaymentRequest::STATUS_APPROVED,
                'approved_by' => $actor->id,
                'remarks' => $remarks !== null ? ($locked->remarks ? $locked->remarks."\nApproval Remarks: ".$remarks : "Approval Remarks: ".$remarks) : $locked->remarks,
            ]);

            if ($locked->purchaseOrder) {
                $this->logger->log(
                    $locked->purchaseOrder,
                    'Payment Request Approved',
                    null,
                    null,
                    "Payment request {$locked->payment_request_no} approved by {$actor->name}",
                    ['payment_request_id' => $locked->id],
                    $actor->id
                );
            }

            return $locked->fresh(['purchaseOrder.vendor', 'requestedBy', 'approvedBy']);
        });
    }

    public function reject(PaymentRequest $paymentRequest, User $actor, string $remarks): PaymentRequest
    {
        if (empty(trim($remarks))) {
            throw new ProcurementException('Rejection remarks are mandatory when rejecting a payment request.');
        }

        return DB::transaction(function () use ($paymentRequest, $actor, $remarks) {
            $locked = PaymentRequest::query()->whereKey($paymentRequest->getKey())->lockForUpdate()->firstOrFail();

            if (! $locked->isPending()) {
                throw new ProcurementException('Only pending payment requests can be rejected.');
            }

            $locked->update([
                'status' => PaymentRequest::STATUS_REJECTED,
                'rejected_by' => $actor->id,
                'remarks' => $locked->remarks ? $locked->remarks."\nRejection Reason: ".$remarks : "Rejection Reason: ".$remarks,
            ]);

            if ($locked->purchaseOrder) {
                $this->logger->log(
                    $locked->purchaseOrder,
                    'Payment Request Rejected',
                    null,
                    null,
                    "Payment request {$locked->payment_request_no} rejected by {$actor->name}. Reason: {$remarks}",
                    ['payment_request_id' => $locked->id],
                    $actor->id
                );
            }

            return $locked->fresh(['purchaseOrder.vendor', 'requestedBy', 'rejectedBy']);
        });
    }

    public function recordPayment(PaymentRequest $paymentRequest, User $actor, array $data, ?UploadedFile $attachment = null): PaymentRequest
    {
        return DB::transaction(function () use ($paymentRequest, $actor, $data, $attachment) {
            $locked = PaymentRequest::query()->whereKey($paymentRequest->getKey())->lockForUpdate()->firstOrFail();

            if (! $locked->isApproved() && ! $locked->isPartiallyPaid()) {
                throw new ProcurementException('Only approved or partially paid payment requests can be processed for payment.');
            }

            // Record actual payment against PO
            $poData = [
                'payment_amount' => !empty($data['payment_amount']) ? (float) $data['payment_amount'] : $locked->amount,
                'payment_method' => $data['payment_method'],
                'payment_date' => $data['payment_date'],
                'reference_number' => $data['reference_number'] ?? null,
                'remarks' => $data['remarks'] ?? null,
            ];

            $poPayment = $this->poPaymentService->recordPayment($locked->purchaseOrder, $actor, $poData, $attachment);

            $purchaseOrder = $locked->purchaseOrder?->fresh();
            $newStatus = ($purchaseOrder && $purchaseOrder->remainingAmount() <= 0)
                || (! $purchaseOrder && $poData['payment_amount'] >= $locked->amount)
                ? PaymentRequest::STATUS_PAID
                : PaymentRequest::STATUS_PARTIALLY_PAID;

            $locked->update([
                'status' => $newStatus,
                'paid_by' => $actor->id,
                'payment_date' => $data['payment_date'],
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?? null,
                'remarks' => $data['remarks'] ?? $locked->remarks,
                'attachment' => $poPayment->attachment ?? null,
            ]);

            return $locked->fresh(['purchaseOrder.vendor', 'requestedBy', 'approvedBy', 'paidBy']);
        });
    }
}
