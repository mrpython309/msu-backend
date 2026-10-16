<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Grn;
use App\Models\Indent;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\Rfq;
use App\Models\VendorQuotation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProcurementDashboardController extends Controller
{
    public function index()
    {
        $user = request()->user();

        $metrics = [
            'indents' => Indent::visibleToUser($user)->count(),
            'pending_approvals' => Indent::visibleToUser($user)->pendingApproval()->count(),
            'requisitions' => PurchaseRequisition::count(),
            'orders' => PurchaseOrder::issued()->count(),
            'grns' => Grn::pendingInspection()->count(),
            'assets_created' => Asset::query()->whereNotNull('grn_item_id')->count(),
            'procurement_cost' => (float) PurchaseOrder::query()->sum('grand_total'),
            'open_rfqs' => Rfq::open()->count(),
        ];

        $recentIndents = Indent::visibleToUser($user)
            ->with('department')
            ->latest()
            ->take(8)
            ->get();

        $monthlyTrends = PurchaseOrder::query()
            ->selectRaw("DATE_FORMAT(purchase_date, '%Y-%m') as period, SUM(grand_total) as total")
            ->where('purchase_date', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(fn ($row) => [
                'label' => Carbon::createFromFormat('Y-m', $row->period)->format('M Y'),
                'total' => (float) $row->total,
            ])
            ->all();

        $vendorPerformance = VendorQuotation::query()
            ->select('vendor_id', DB::raw('COUNT(*) as quotes'), DB::raw('AVG(total_cost) as avg_cost'))
            ->with('vendor:id,name')
            ->groupBy('vendor_id')
            ->orderByDesc('quotes')
            ->limit(5)
            ->get();

        return view('procurement.dashboard', compact(
            'metrics',
            'recentIndents',
            'monthlyTrends',
            'vendorPerformance'
        ));
    }
}
