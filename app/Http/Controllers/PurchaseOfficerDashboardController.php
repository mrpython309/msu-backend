<?php

namespace App\Http\Controllers;

use App\Enums\Procurement\PurchaseOrderStatus;
use App\Enums\Procurement\PurchaseRequisitionStatus;
use App\Enums\Procurement\RfqStatus;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\Rfq;
use App\Models\VendorQuotation;
use Illuminate\Support\Facades\Auth;

class PurchaseOfficerDashboardController extends Controller
{
    public function index()
    {
        $pendingPrs = PurchaseRequisition::query()
            ->whereIn('status', [
                PurchaseRequisitionStatus::Pending->value,
                PurchaseRequisitionStatus::PendingPurchase->value,
            ])
            ->count();

        $rfqsAwaitingResponse = Rfq::query()
            ->where('status', RfqStatus::Open->value)
            ->count();

        $quotationsReceived = VendorQuotation::query()->count();

        $pendingPurchaseOrders = PurchaseOrder::query()
            ->whereIn('status', [
                PurchaseOrderStatus::Draft->value,
                PurchaseOrderStatus::Sent->value,
                PurchaseOrderStatus::Issued->value,
                PurchaseOrderStatus::Accepted->value,
            ])
            ->count();

        $completedPurchaseOrders = PurchaseOrder::query()
            ->whereIn('status', [
                PurchaseOrderStatus::Completed->value,
                PurchaseOrderStatus::Received->value,
                PurchaseOrderStatus::Closed->value,
            ])
            ->count();

        $recentPrs = PurchaseRequisition::query()
            ->with(['indent.department', 'purchaseOfficer'])
            ->latest()
            ->take(8)
            ->get();

        $openRfqs = Rfq::query()
            ->with(['purchaseRequisition', 'rfqVendors'])
            ->where('status', RfqStatus::Open->value)
            ->latest()
            ->take(8)
            ->get();

        $metrics = [
            'pending_prs' => $pendingPrs,
            'rfqs_awaiting' => $rfqsAwaitingResponse,
            'quotations_received' => $quotationsReceived,
            'pending_pos' => $pendingPurchaseOrders,
            'completed_pos' => $completedPurchaseOrders,
        ];

        return view('purchase-management.dashboard', compact(
            'metrics',
            'recentPrs',
            'openRfqs'
        ));
    }
}
