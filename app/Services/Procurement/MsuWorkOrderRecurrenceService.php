<?php

namespace App\Services\Procurement;

use App\Enums\Procurement\MsuWorkOrderStatus;
use App\Models\MsuWorkOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MsuWorkOrderRecurrenceService
{
    public function syncAndGenerate(MsuWorkOrder $workOrder): int
    {
        $workOrder->refresh();
        $this->refreshSchedule($workOrder);
        $workOrder->refresh();

        return $this->generateDue($workOrder);
    }

    public function refreshSchedule(MsuWorkOrder $workOrder): void
    {
        if ($workOrder->recurrence_source_id) {
            return;
        }

        $frequency = $workOrder->frequencyEnum();
        if (! $frequency || $workOrder->statusEnum() === MsuWorkOrderStatus::Cancelled) {
            $workOrder->forceFill(['next_occurrence_date' => null])->save();

            return;
        }

        $latestChildDate = MsuWorkOrder::query()
            ->where('recurrence_source_id', $workOrder->id)
            ->max('wo_date');

        $anchor = $latestChildDate
            ? Carbon::parse($latestChildDate)->startOfDay()
            : Carbon::parse($workOrder->wo_date)->startOfDay();

        $next = $frequency->advance($anchor);
        if ($this->isAfterContractEnd($workOrder, $next)) {
            $next = null;
        }

        $workOrder->forceFill([
            'next_occurrence_date' => $next?->toDateString(),
        ])->save();
    }

    public function generateDue(MsuWorkOrder $workOrder, ?Carbon $asOf = null): int
    {
        $asOf = ($asOf ?? now())->copy()->startOfDay();
        $created = 0;

        for ($guard = 0; $guard < 120; $guard++) {
            $workOrder->refresh();

            if ($workOrder->recurrence_source_id || ! $workOrder->frequencyEnum()) {
                break;
            }

            if ($workOrder->statusEnum() === MsuWorkOrderStatus::Cancelled) {
                $workOrder->forceFill(['next_occurrence_date' => null])->save();
                break;
            }

            $nextDate = $workOrder->next_occurrence_date
                ? Carbon::parse($workOrder->next_occurrence_date)->startOfDay()
                : null;

            if (! $nextDate || $nextDate->gt($asOf) || $this->isAfterContractEnd($workOrder, $nextDate)) {
                if ($nextDate && $this->isAfterContractEnd($workOrder, $nextDate)) {
                    $workOrder->forceFill(['next_occurrence_date' => null])->save();
                }
                break;
            }

            $this->createOccurrence($workOrder, $nextDate);
            $created++;

            $following = $workOrder->frequencyEnum()->advance($nextDate);
            $workOrder->forceFill([
                'next_occurrence_date' => $this->isAfterContractEnd($workOrder, $following)
                    ? null
                    : $following->toDateString(),
            ])->save();
        }

        return $created;
    }

    public function generateAllDue(): int
    {
        $sources = MsuWorkOrder::query()
            ->whereNull('recurrence_source_id')
            ->whereNotNull('frequency')
            ->whereNotNull('next_occurrence_date')
            ->whereDate('next_occurrence_date', '<=', now()->toDateString())
            ->where('status', '!=', MsuWorkOrderStatus::Cancelled->value)
            ->orderBy('id')
            ->get();

        $created = 0;
        foreach ($sources as $source) {
            $created += $this->generateDue($source);
        }

        return $created;
    }

    private function createOccurrence(MsuWorkOrder $source, Carbon $occurrenceDate): MsuWorkOrder
    {
        $alreadyExists = MsuWorkOrder::query()
            ->where('recurrence_source_id', $source->id)
            ->whereDate('wo_date', $occurrenceDate->toDateString())
            ->exists();

        if ($alreadyExists) {
            return MsuWorkOrder::query()
                ->where('recurrence_source_id', $source->id)
                ->whereDate('wo_date', $occurrenceDate->toDateString())
                ->first();
        }

        return DB::transaction(function () use ($source, $occurrenceDate) {
            $source->loadMissing(['items', 'charges', 'branch.company']);

            $branch = $source->branch;
            $branchCode = $branch ? ($branch->code ?: $branch->name) : 'BRN';
            $companyCode = $branch?->company?->code ?: 'MSULLP';
            $woNumber = app(DocumentNumberGenerator::class)->nextMsuWoNumber(
                $companyCode,
                $branchCode,
                $occurrenceDate
            );

            $copy = MsuWorkOrder::create([
                'wo_number' => $woNumber,
                'wo_date' => $occurrenceDate->toDateString(),
                'contract_period_from' => $source->contract_period_from,
                'contract_period_to' => $source->contract_period_to,
                'frequency' => null,
                'recurrence_source_id' => $source->id,
                'next_occurrence_date' => null,
                'subject' => $source->subject,
                'vendor_id' => $source->vendor_id,
                'branch_id' => $source->branch_id,
                'employee_master_id' => $source->employee_master_id,
                'department_id' => $source->department_id,
                'shipping_detail_id' => $source->shipping_detail_id,
                'shipping_address' => $source->shipping_address,
                'shipping_contact_person' => $source->shipping_contact_person,
                'shipping_gst' => $source->shipping_gst,
                'remarks' => $source->remarks,
                'payment_term_id' => $source->payment_term_id,
                'miscellaneous_charge_id' => $source->miscellaneous_charge_id,
                'miscellaneous_charges' => 0,
                'miscellaneous_tax' => 0,
                'sgst' => 0,
                'cgst' => 0,
                'igst' => 0,
                'status' => MsuWorkOrderStatus::WoCreated,
                'created_by' => $source->created_by,
                'total_value' => 0,
                'grand_total' => 0,
            ]);

            foreach ($source->items as $index => $item) {
                $copy->items()->create([
                    'work_description' => $item->work_description,
                    'scope_of_work' => $item->scope_of_work,
                    'hsn' => $item->hsn,
                    'unit_id' => $item->unit_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'tax' => $item->tax,
                    'net_value' => $item->net_value,
                    'received_quantity' => 0,
                    'sort_order' => $item->sort_order ?: ($index + 1),
                ]);
            }

            foreach ($source->charges as $charge) {
                $copy->charges()->create([
                    'charge_name' => $charge->charge_name,
                    'amount' => $charge->amount,
                    'tax_percentage' => $charge->tax_percentage,
                ]);
            }

            $copy->recalculateTotals();

            return $copy;
        });
    }

    private function isAfterContractEnd(MsuWorkOrder $workOrder, Carbon $date): bool
    {
        if (! $workOrder->contract_period_to) {
            return false;
        }

        return $date->gt(Carbon::parse($workOrder->contract_period_to)->startOfDay());
    }
}
