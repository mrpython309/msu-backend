<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetType;
use App\Models\AssetMaintenance;
use App\Models\AssetWarranty;
use Carbon\Carbon;
use App\Traits\ApiResponse;

class AssetDashboardController extends Controller
{
    use ApiResponse;

    public function assets(){

        $dashboard=array();

        $assetBuckets = Asset::aggregateChartStatusBuckets(Asset::VisibleToUser(auth()->user()));

        $dashboard['total_assets_count'] = (int) array_sum($assetBuckets);

        $dashboard['available_assets_count'] = (int) ($assetBuckets['available'] ?? 0);

        $dashboard['assigned_assets_count'] = (int) ($assetBuckets['assigned'] ?? 0);

        $dashboard['under_maintenance_assets_count'] = (int) ($assetBuckets['under_maintenance'] ?? 0);

        $dashboard['asset_summary_purchase_value']=Asset::VisibleToUser(auth()->user())->sum('purchase_cost');

        $totalDepreciation=0;

        foreach(Asset::VisibleToUser(auth()->user())->get() as $asset){
            $totalDepreciation+=$asset->total_depreciation;
        }

        $dashboard['asset_summary_total_depreciation']=$totalDepreciation;

        $dashboard['asset_summary_total_current_value']=$dashboard['asset_summary_purchase_value']-$totalDepreciation;

        $dashboard['asset_summary_depreciation_percentage'] = $dashboard['asset_summary_purchase_value'] > 0 ? round(($dashboard['asset_summary_total_depreciation'] / $dashboard['asset_summary_purchase_value']) * 100,2): 0;

        $dashboard['asset_types_with_count'] = AssetType::withCount([
    'assets as assets_count' => function ($q) {
        $q->VisibleToUser(auth()->user());
    }
])->get();

        $dashboard['recent_assignments'] = AssetAssignment::with(['asset' => function ($q) {
        $q->VisibleToUser(auth()->user());
    }])
    ->whereHas('asset', function ($q) {
        $q->VisibleToUser(auth()->user());
    })->latest()->take(10)->get();

        $dashboard['recent_maintenances'] = AssetMaintenance::with(['asset' => function ($q) {
        $q->VisibleToUser(auth()->user());
    }])
    ->whereHas('asset', function ($q) {
        $q->VisibleToUser(auth()->user());
    })->latest()->take(10)->get();

        $dashboard['expiring_warranties'] = AssetWarranty::with(['asset' => function ($q) {
        $q->VisibleToUser(auth()->user());
    }])
    ->whereHas('asset', function ($q) {
        $q->VisibleToUser(auth()->user());
    })
    ->whereDate('warranty_expire_date', '>=', Carbon::today())
    ->whereDate('warranty_expire_date', '<=', Carbon::today()->addDays(30))
    ->orderBy('warranty_expire_date', 'asc')
    ->take(10)
    ->get();

       
    $colors = [
    'bg-label-primary',
    'bg-label-success',
    'bg-label-info',
    'bg-label-warning',
    'bg-label-danger',
    'bg-label-secondary',
    'bg-label-dark'
    ];


    $dashboard['assets_by_status'] = $assetBuckets;

     return $this->success($dashboard); 

    }
}
