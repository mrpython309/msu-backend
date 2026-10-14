<?php

namespace App\Services\Procurement;

use App\Enums\Procurement\PurchaseRequisitionStatus;
use App\Enums\Procurement\RfqStatus;
use App\Models\PurchaseRequisition;
use App\Models\Rfq;
use App\Models\RfqAttachment;
use App\Models\RfqVendor;
use App\Models\User;
use App\Models\Vendor;
use App\Services\Procurement\Exceptions\ProcurementException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RfqService
{
    public function __construct(
        private readonly DocumentNumberGenerator $numbers,
        private readonly ProcurementActivityLogger $logger,
    ) {
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, int|Vendor>  $vendors
     * @param  array<int, UploadedFile>  $attachments
     */
    public function createForPurchaseRequisition(
        PurchaseRequisition $pr,
        array $vendors,
        ?User $actor = null,
        ?string $notes = null,
        ?\DateTimeInterface $closesAt = null,
        array $attributes = [],
        bool $send = true,
        array $attachments = [],
    ): Rfq {
        if ($send && $vendors === []) {
            throw new ProcurementException('At least one vendor is required to send an RFQ.');
        }

        return DB::transaction(function () use ($pr, $vendors, $actor, $notes, $closesAt, $attributes, $send, $attachments) {
            $lockedPr = PurchaseRequisition::query()
                ->whereKey($pr->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedPr->allowsProcurement()) {
                throw new ProcurementException('This purchase requisition is closed. RFQ creation is not allowed.');
            }

            if ($lockedPr->statusEnum()?->isAwaitingPurchaseOfficer()) {
                throw new ProcurementException('This purchase requisition has not been transferred to RFQ yet.');
            }

            if ($lockedPr->rfqs()->exists()) {
                throw new ProcurementException('An RFQ already exists for this purchase requisition. Another RFQ cannot be created.');
            }

            $status = $send ? RfqStatus::Open : RfqStatus::Draft;

            $rfq = Rfq::create(array_merge([
                'rfq_no' => $this->numbers->next('rfq', Rfq::class, 'rfq_no'),
                'pr_id' => $lockedPr->id,
                'status' => $status,
                'notes' => $notes,
                'created_by' => $actor?->id,
                'closes_at' => $closesAt,
                'rfq_date' => now()->toDateString(),
                'currency' => 'INR',
            ], $this->filterAttributes($attributes)));

            if ($vendors !== []) {
                $this->inviteVendors($rfq, $vendors, $send);
            }

            $this->storeAttachments($rfq, $attachments, $actor);

            if ($send && in_array($lockedPr->status, [
                PurchaseRequisitionStatus::Pending,
                PurchaseRequisitionStatus::PendingPurchase,
            ], true)) {
                $lockedPr->update([
                    'status' => PurchaseRequisitionStatus::InProgress,
                    'purchase_officer_id' => $actor?->id ?? $lockedPr->purchase_officer_id,
                    'processed_at' => $lockedPr->processed_at ?? now(),
                ]);
            }

            $this->logger->log($rfq, $send ? 'RFQ Sent' : 'RFQ Draft Saved', null, $status->value, $notes, [
                'pr_id' => $lockedPr->id,
                'vendor_ids' => $this->normalizeVendorIds($vendors),
            ], $actor?->id);

            return $rfq->load(['rfqVendors.vendor', 'purchaseRequisition', 'attachments']);
        });
    }

    /**
     * @param  array<int, int|Vendor>  $vendors
     */
    public function inviteVendors(Rfq $rfq, array $vendors, bool $markSent = true): Collection
    {
        return DB::transaction(function () use ($rfq, $vendors, $markSent) {
            $locked = Rfq::query()->whereKey($rfq->getKey())->lockForUpdate()->firstOrFail();

            $status = $locked->statusEnum();
            if ($status && ! $status->canInviteVendors()) {
                throw new ProcurementException('Vendors can only be invited to a draft or open RFQ.');
            }

            $created = collect();

            foreach ($this->normalizeVendorIds($vendors) as $vendorId) {
                $pivot = RfqVendor::query()->firstOrNew([
                    'rfq_id' => $locked->id,
                    'vendor_id' => $vendorId,
                ]);

                $pivot->status = $markSent ? 'Invited' : ($pivot->status ?: 'Selected');
                if ($markSent) {
                    $pivot->invited_at = $pivot->invited_at ?? now();
                }
                $pivot->save();

                $created->push($pivot);
            }

            if ($markSent && $locked->status === RfqStatus::Draft) {
                $locked->update(['status' => RfqStatus::Open]);

                $pr = $locked->purchaseRequisition;
                if ($pr && in_array($pr->status, [
                    PurchaseRequisitionStatus::Pending,
                    PurchaseRequisitionStatus::PendingPurchase,
                ], true)) {
                    $pr->update([
                        'status' => PurchaseRequisitionStatus::InProgress,
                        'processed_at' => $pr->processed_at ?? now(),
                    ]);
                }
            }

            if ($markSent) {
                $this->logger->log($locked, 'RFQ Sent', null, null, 'Vendors invited / RFQ sent.', [
                    'vendor_ids' => $this->normalizeVendorIds($vendors),
                ]);
            }

            return $created;
        });
    }

    /**
     * Send a draft RFQ to selected vendors (Draft → Open).
     *
     * @param  array<int, int|Vendor>  $vendors
     */
    public function send(Rfq $rfq, array $vendors, ?User $actor = null): Rfq
    {
        if ($vendors === []) {
            throw new ProcurementException('Select at least one vendor to send the RFQ.');
        }

        if ($rfq->statusEnum() !== RfqStatus::Draft && $rfq->statusEnum() !== RfqStatus::Open) {
            throw new ProcurementException('Only draft or open RFQs can be sent to vendors.');
        }

        $this->inviteVendors($rfq, $vendors, true);

        return $rfq->fresh(['rfqVendors.vendor', 'purchaseRequisition']);
    }

    public function close(Rfq $rfq, ?User $actor = null, ?string $comments = null): Rfq
    {
        return DB::transaction(function () use ($rfq, $actor, $comments) {
            $locked = Rfq::query()->whereKey($rfq->getKey())->lockForUpdate()->firstOrFail();
            $from = $this->statusValue($locked->status);

            $locked->update(['status' => RfqStatus::Closed]);

            $this->logger->log($locked, 'rfq.closed', $from, RfqStatus::Closed->value, $comments, [], $actor?->id);

            return $locked->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, int|Vendor>  $vendors
     * @param  array<int, UploadedFile>  $attachments
     */
    public function update(Rfq $rfq, array $attributes, array $vendors = [], ?User $actor = null, array $attachments = []): Rfq
    {
        return DB::transaction(function () use ($rfq, $attributes, $vendors, $actor, $attachments) {
            $locked = Rfq::query()->whereKey($rfq->getKey())->lockForUpdate()->firstOrFail();
            $status = $locked->statusEnum();

            if (! $status || ! in_array($status, [RfqStatus::Draft, RfqStatus::Open], true)) {
                throw new ProcurementException('Only draft or open RFQs can be edited.');
            }

            if ($status === RfqStatus::Open && $locked->quotations()->exists()) {
                // Still allow header updates, but not vendor list changes that remove quoted vendors.
            }

            $payload = $this->filterAttributes($attributes);
            if (array_key_exists('notes_to_vendor', $payload) && ! array_key_exists('notes', $payload)) {
                $payload['notes'] = $payload['notes_to_vendor'];
            }

            $locked->update($payload);

            $vendorIds = $this->normalizeVendorIds($vendors);

            if ($status === RfqStatus::Open) {
                $quotedVendorIds = $locked->quotations()->pluck('vendor_id')->map(fn ($id) => (int) $id)->all();
                $missingQuoted = array_diff($quotedVendorIds, $vendorIds);
                if ($missingQuoted !== []) {
                    throw new ProcurementException('Cannot remove vendors that already have quotations.');
                }
            }

            if ($vendorIds === []) {
                RfqVendor::query()->where('rfq_id', $locked->id)->delete();
            } else {
                RfqVendor::query()
                    ->where('rfq_id', $locked->id)
                    ->whereNotIn('vendor_id', $vendorIds)
                    ->delete();

                $this->inviteVendors($locked, $vendorIds, $status === RfqStatus::Open);
            }

            $this->storeAttachments($locked, $attachments, $actor);

            $this->logger->log($locked, 'RFQ Updated', null, $this->statusValue($locked->status), null, [
                'vendor_ids' => $this->normalizeVendorIds($vendors),
            ], $actor?->id);

            return $locked->fresh(['rfqVendors.vendor', 'purchaseRequisition', 'attachments']);
        });
    }

    public function delete(Rfq $rfq, ?User $actor = null): void
    {
        DB::transaction(function () use ($rfq, $actor) {
            $locked = Rfq::query()
                ->withCount('quotations')
                ->whereKey($rfq->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $status = $locked->statusEnum();

            if ($status === RfqStatus::Awarded) {
                throw new ProcurementException('Awarded RFQs cannot be deleted.');
            }

            if ($status === RfqStatus::Closed) {
                throw new ProcurementException('Closed RFQs cannot be deleted.');
            }

            if ($locked->quotations_count > 0) {
                throw new ProcurementException('RFQs with recorded quotations cannot be deleted.');
            }

            $this->logger->log($locked, 'RFQ Deleted', $this->statusValue($locked->status), null, 'RFQ deleted.', [
                'rfq_no' => $locked->rfq_no,
            ], $actor?->id);

            $locked->rfqVendors()->delete();
            $locked->attachments()->delete();
            $locked->delete();
        });
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function storeAttachments(Rfq $rfq, array $files, ?User $actor = null): void
    {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $path = $file->store('rfq-attachments/'.$rfq->id, 'public');

            RfqAttachment::create([
                'rfq_id' => $rfq->id,
                'original_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'uploaded_by' => $actor?->id,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function filterAttributes(array $attributes): array
    {
        return collect($attributes)
            ->only([
                'rfq_date',
                'priority',
                'expected_delivery_date',
                'delivery_store',
                'currency',
                'payment_terms',
                'delivery_terms',
                'warranty_required',
                'amc_required',
                'tax_included',
                'freight_included',
                'notes_to_vendor',
                'notes',
                'closes_at',
            ])
            ->all();
    }

    /**
     * @param  array<int, int|Vendor>  $vendors
     * @return list<int>
     */
    private function normalizeVendorIds(array $vendors): array
    {
        return collect($vendors)
            ->map(fn ($vendor) => $vendor instanceof Vendor ? (int) $vendor->id : (int) $vendor)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function statusValue(mixed $status): ?string
    {
        return $status instanceof \BackedEnum ? $status->value : ($status !== null ? (string) $status : null);
    }
}
