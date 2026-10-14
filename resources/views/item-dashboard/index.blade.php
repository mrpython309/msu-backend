@extends('layouts/contentNavbarLayout')

@section('title', 'Item Dashboard')

@section('page-heading')
<h4 class="mb-0">Item Dashboard</h4>
@endsection

@section('page-style')
<style>
.dashboard-card {
    border-radius: 12px;
    border: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    transition: transform 0.2s;
}
.dashboard-card:hover {
    transform: translateY(-2px);
}
.icon-box {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.cat-bullet {
    width: 10px;
    height: 10px;
    display: inline-block;
    border-radius: 3px;
    margin-right: 8px;
}
.progress-slim {
    height: 6px;
    width: 100px;
    border-radius: 4px;
    background-color: #f1f1f1;
}
.recent-activity-row {
    border-bottom: 1px solid #f0f0f0;
}
.recent-activity-row:last-child {
    border-bottom: none;
}
.circle-number {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}
.low-stock-card {
    border: 1px solid rgba(255, 171, 0, 0.45) !important;
}
.low-stock-card .low-stock-link:hover {
    background: rgba(255, 171, 0, 0.08);
}
</style>
@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 py-3 mb-2">
    <h4 class="fw-bold mb-0">Item Dashboard</h4>
    <div class="d-flex flex-wrap gap-2">
        @php
            $dashboardExportQuery = request()->only(['from_date', 'to_date']);
        @endphp
        <a href="{{ route('item-dashboard.export', $dashboardExportQuery) }}" class="btn btn-outline-success">
            <i class="bx bx-export me-1"></i> Excel
        </a>
        <a href="{{ route('item-dashboard.export-pdf', $dashboardExportQuery) }}" class="btn btn-outline-danger">
            <i class="bx bxs-file-pdf me-1"></i> PDF
        </a>
    </div>
</div>
@php
    $totalOpening = 0;
    $totalReceived = 0;
    $totalIssued = 0;
    $totalClosing = 0;
    $totalClosingQty = 0;
    $categoryCount = count($categorySummaries);
    foreach($categorySummaries as $summary) {
        $totalOpening += $summary['opening_val'];
        $totalReceived += $summary['received_val'];
        $totalIssued += $summary['issued_val'];
        $totalClosing += $summary['closing_val'];
        $totalClosingQty += $summary['closing_qty'];
    }
    
    $vsOpening = 0;
    if ($totalOpening > 0) {
        $vsOpening = (($totalClosing - $totalOpening) / $totalOpening) * 100;
    }
@endphp

<!-- 4 Cards Row -->
<div class="row g-4 mb-4">
    <!-- Opening Stock -->
    <div class="col-sm-6 col-xl-3">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-2 text-uppercase" style="font-size: 0.75rem; font-weight: 600;">OPENING STOCK VALUE</p>
                        <h3 class="mb-2 fw-bold">₹{{ number_format($totalOpening, 2) }}</h3>
                        <small class="text-muted">Across {{ $categoryCount }} categories</small>
                    </div>
                    <div class="icon-box bg-label-secondary">
                        <i class="bx bx-package text-secondary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Received -->
    <div class="col-sm-6 col-xl-3">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-2 text-uppercase" style="font-size: 0.75rem; font-weight: 600;">RECEIVED VALUE</p>
                        <h3 class="mb-2 fw-bold text-dark">₹{{ number_format($totalReceived, 2) }}</h3>
                        @if($totalReceived > 0)
                            <small class="text-success"><i class="bx bx-up-arrow-alt"></i> Stock received</small>
                        @else
                            <small class="text-muted">No stock received</small>
                        @endif
                    </div>
                    <div class="icon-box bg-label-info">
                        <i class="bx bx-down-arrow-alt text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Issued -->
    <div class="col-sm-6 col-xl-3">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-2 text-uppercase" style="font-size: 0.75rem; font-weight: 600;">ISSUED VALUE</p>
                        <h3 class="mb-2 fw-bold text-dark">₹{{ number_format($totalIssued, 2) }}</h3>
                        @if($totalIssued > 0)
                            <small class="text-danger"><i class="bx bx-down-arrow-alt"></i> Stock issued</small>
                        @else
                            <small class="text-muted">No stock issued today</small>
                        @endif
                    </div>
                    <div class="icon-box bg-label-danger">
                        <i class="bx bx-up-arrow-alt text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Closing Stock -->
    <div class="col-sm-6 col-xl-3">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-2 text-uppercase" style="font-size: 0.75rem; font-weight: 600;">CLOSING STOCK VALUE</p>
                        <h3 class="mb-2 fw-bold text-dark">₹{{ number_format($totalClosing, 2) }}</h3>
                        @if($vsOpening > 0)
                            <small class="text-success">▲ {{ number_format($vsOpening, 1) }}% vs opening</small>
                        @elseif($vsOpening < 0)
                            <small class="text-danger">▼ {{ number_format(abs($vsOpening), 1) }}% vs opening</small>
                        @else
                            <small class="text-muted">No change vs opening</small>
                        @endif
                    </div>
                    <div class="icon-box bg-label-success">
                        <i class="bx bx-check text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card dashboard-card low-stock-card">
            <a href="{{ route('item-stocks.index', ['stock_status' => 'low']) }}" class="card-header low-stock-link d-flex align-items-center justify-content-between text-decoration-none py-3">
                <h5 class="mb-0 fw-bold text-warning">
                    <i class="bx bx-bell me-1"></i> Low Stock Alert
                </h5>
                <span class="d-flex align-items-center gap-2">
                    <span class="badge bg-label-warning rounded-pill">{{ $lowStockItems->count() }} {{ \Illuminate\Support\Str::plural('item', $lowStockItems->count()) }}</span>
                    <small class="text-warning fw-semibold">View in Item Stock <i class="bx bx-chevron-right"></i></small>
                </span>
            </a>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 360px;">
                    <table class="table table-hover mb-0">
                        <thead class="border-bottom" style="border-bottom-color: #f0f0f0 !important;">
                            <tr>
                                <th class="text-muted text-uppercase" style="font-size: 0.75rem;">Item</th>
                                <th class="text-muted text-uppercase" style="font-size: 0.75rem;">Item Code</th>
                                <th class="text-muted text-uppercase text-end" style="font-size: 0.75rem;">Current Stock</th>
                                <th class="text-muted text-uppercase text-end" style="font-size: 0.75rem;">Threshold</th>
                                <th class="text-muted text-uppercase" style="font-size: 0.75rem;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lowStockItems as $item)
                                <tr>
                                    <td><strong>{{ $item->item_name }}</strong></td>
                                    <td class="text-muted">{{ $item->item_code }}</td>
                                    <td class="text-end text-danger fw-semibold">{{ number_format((float) $item->current_stock, 3) }}</td>
                                    <td class="text-end">{{ number_format((float) $item->threshold_quantity, 3) }}</td>
                                    <td>
                                        <a href="{{ route('item-stocks.index', ['stock_status' => 'low', 'item_code' => $item->item_code]) }}" class="text-decoration-none">
                                            <span class="badge bg-label-warning">Low Stock</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No items are at or below their threshold quantity.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card dashboard-card">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center py-3">
                <h5 class="mb-3 mb-md-0 fw-bold text-primary">Category Stock Summary</h5>
                <form method="GET" action="{{ route('item-dashboard.index') }}" class="d-flex align-items-center m-0">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white text-muted border-end-0">From</span>
                        <input type="date" name="from_date" class="form-control border-start-0" value="{{ request('from_date', now()->toDateString()) }}" title="From Date" style="max-width: 130px;">
                        <span class="input-group-text bg-white text-muted border-start-0 border-end-0">To</span>
                        <input type="date" name="to_date" class="form-control border-start-0" value="{{ request('to_date', now()->toDateString()) }}" title="To Date" style="max-width: 130px;">
                        <button type="submit" class="btn btn-primary ms-2 rounded"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                        <a href="{{ route('item-dashboard.index') }}" class="btn btn-outline-secondary ms-2 rounded"><i class="bx bx-reset me-1"></i> Clear</a>
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <thead class="border-bottom" style="border-bottom-color: #f0f0f0 !important;">
                            <tr>
                                <th class="text-muted text-uppercase" style="font-size: 0.75rem;">CATEGORY</th>
                                <th class="text-muted text-uppercase" style="font-size: 0.75rem;">OPENING STOCKS</th>
                                <th class="text-muted text-uppercase" style="font-size: 0.75rem;">RECEIVED STOCKS</th>
                                <th class="text-muted text-uppercase" style="font-size: 0.75rem;">ISSUED STOCKS</th>
                                <th class="text-muted text-uppercase" style="font-size: 0.75rem;">STOCK LEVEL</th>
                                <th class="text-muted text-uppercase text-end" style="font-size: 0.75rem;">CLOSING STOCKS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $colors = ['primary', 'success', 'warning', 'danger', 'info', 'dark'];
                                $index = 0;
                            @endphp
                            @forelse($categorySummaries as $summary)
                                @php
                                    $cColor = $colors[$index % count($colors)];
                                    $index++;
                                    $pct = $totalClosingQty > 0 ? ($summary['closing_qty'] / $totalClosingQty) * 100 : 0;
                                @endphp
                                <tr class="border-bottom" style="border-bottom-color: #f9f9f9 !important;">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="cat-bullet bg-{{ $cColor }}"></span>
                                            <strong>{{ $summary['name'] }}</strong>
                                        </div>
                                    </td>
                                    <td class="text-muted">{{ number_format($summary['opening_qty'], 3) }}</td>
                                    <td class="text-success">{{ number_format($summary['received_qty'], 3) }}</td>
                                    <td class="text-danger">{{ number_format($summary['issued_qty'], 3) }}</td>
                                    <td>
                                        <div class="progress progress-slim">
                                            <div class="progress-bar bg-{{ $cColor }}" role="progressbar" style="width: {{ $pct }}%" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </td>
                                    <td class="text-end fw-bold text-primary">{{ number_format($summary['closing_qty'], 3) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No data available</td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Activity -->
    <div class="col-12 mb-4">
        <div class="card dashboard-card">
            <div class="card-header d-flex align-items-center justify-content-between border-bottom" style="border-bottom-color: #f0f0f0 !important; padding-bottom: 1rem;">
                <h5 class="card-title m-0 fw-bold text-primary">Recent Item Activity</h5>
                <span class="badge bg-label-primary rounded-pill">Latest 10</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            @forelse($recentActivities as $idx => $activity)
                                @php
                                    $actionLower = strtolower($activity['action']);
                                    $color = 'primary';
                                    $icon = 'bx-check';
                                    
                                    if (str_contains($actionLower, 'created') || str_contains($actionLower, 'received')) {
                                        $color = 'success';
                                        $icon = 'bx-plus';
                                    } elseif (str_contains($actionLower, 'issued') || str_contains($actionLower, 'cancelled') || str_contains($actionLower, 'deleted')) {
                                        $color = 'danger';
                                        $icon = 'bx-minus';
                                    } elseif (str_contains($actionLower, 'updated')) {
                                        $color = 'primary';
                                        $icon = 'bx-edit';
                                    } elseif (str_contains($actionLower, 'adjustment')) {
                                        $color = 'warning';
                                        $icon = 'bx-adjust';
                                    }
                                @endphp
                                <tr class="recent-activity-row">
                                    <td style="width: 80px;" class="py-3 px-3">
                                        <div class="bg-label-primary text-primary circle-number mx-auto">
                                            {{ $idx + 1 }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-muted text-nowrap" style="width: 220px;">
                                        <small><i class="bx bx-time-five me-1"></i>{{ \Carbon\Carbon::parse($activity['date_time'])->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</small>
                                    </td>
                                    <td class="py-3 px-4 text-nowrap" style="width: 350px;">
                                        <strong class="text-dark">{{ $activity['item_name'] }}</strong>
                                    </td>
                                    <td class="text-start py-3 pe-4">
                                        @if(isset($activity['url']) && $activity['url'])
                                            <a href="{{ $activity['url'] }}" class="text-decoration-none">
                                        @endif
                                        <span class="badge bg-label-{{ $color }} rounded-pill d-inline-flex align-items-center py-2 px-3 fw-semibold" style="cursor: {{ (isset($activity['url']) && $activity['url']) ? 'pointer' : 'default' }};">
                                            <i class="bx {{ $icon }} me-2" style="font-size: 14px;"></i> 
                                            {{ $activity['action'] }}
                                        </span>
                                        @if(isset($activity['url']) && $activity['url'])
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No recent item activity</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
