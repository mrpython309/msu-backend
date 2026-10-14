@extends('layouts/contentNavbarLayout')

@section('title', 'Purchase & Stock Dashboard')

@section('vendor-style')
@vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
<style>
  .hero-gradient {
    background: linear-gradient(135deg, #696cff 0%, #3f51b5 50%, #03c3ec 100%);
    color: #fff;
    border-radius: 16px;
    padding: 2.25rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 30px 0 rgba(105, 108, 255, 0.25);
  }
  .hero-gradient::after {
    content: '';
    position: absolute;
    top: -20%;
    right: -5%;
    width: 250px;
    height: 250px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.12);
    filter: blur(40px);
    pointer-events: none;
  }
  .hero-gradient::before {
    content: '';
    position: absolute;
    bottom: -20%;
    left: 10%;
    width: 200px;
    height: 200px;
    border-radius: 50%;
    background: rgba(3, 195, 236, 0.2);
    filter: blur(45px);
    pointer-events: none;
  }
  .hero-metric-card {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 12px;
    padding: 1rem 1.25rem;
    color: #fff;
    transition: transform 0.2s;
    text-decoration: none;
  }
  .hero-metric-card:hover {
    transform: translateY(-3px);
    background: rgba(255, 255, 255, 0.22);
    color: #fff;
  }
  .modern-card {
    border-radius: 16px !important;
    border: none !important;
    box-shadow: 0 4px 20px 0 rgba(0, 0, 0, 0.05) !important;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    background: #fff;
  }
  .modern-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px 0 rgba(0, 0, 0, 0.08) !important;
  }
  .accent-card-red {
    background: linear-gradient(135deg, #ff3e1d 0%, #ffab00 100%);
    color: #fff;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 10px 25px 0 rgba(255, 62, 29, 0.2);
    position: relative;
    overflow: hidden;
  }
  .accent-card-red::before {
    content: '';
    position: absolute;
    top: -20px;
    left: -20px;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    pointer-events: none;
  }
  .kpi-icon-wrapper {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 1rem;
  }
  .kpi-card {
    padding: 1.5rem;
  }
  .kpi-link {
    text-decoration: none;
    color: inherit;
    display: block;
    height: 100%;
  }
  .table-responsive::-webkit-scrollbar {
    height: 6px;
  }
  .table-responsive::-webkit-scrollbar-thumb {
    background: #e1e1e6;
    border-radius: 4px;
  }
  .modern-card.dashboard-filters:hover {
    transform: none;
    box-shadow: 0 4px 20px 0 rgba(0, 0, 0, 0.05) !important;
  }
  .dashboard-filters__field {
    min-width: 220px;
    flex: 1 1 220px;
  }
</style>
@endsection

@section('vendor-script')
@vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('page-script')
@php
  $peakIndex = 0;
  $peakValue = 0;
  foreach ($spendTrend as $idx => $item) {
      $combined = ($item['po_total'] ?? 0) + ($item['wo_total'] ?? 0);
      if ($combined >= $peakValue) {
          $peakValue = $combined;
          $peakIndex = $idx;
      }
  }
@endphp
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var options = {
      series: [{
        name: 'Purchase Orders',
        data: [{{ $spendTrend->map(fn($item) => $item['po_total'])->implode(',') }}]
      }, {
        name: 'Work Orders',
        data: [{{ $spendTrend->map(fn($item) => $item['wo_total'])->implode(',') }}]
      }],
      chart: {
        type: 'area',
        height: 330,
        parentHeightOffset: 0,
        toolbar: {
          show: false
        },
        dropShadow: {
          enabled: true,
          top: 10,
          left: 0,
          blur: 8,
          color: '#696cff',
          opacity: 0.15
        }
      },
      legend: {
        show: true,
        position: 'top',
        horizontalAlign: 'right',
        labels: {
          colors: '#a1acb8'
        }
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        curve: 'smooth',
        width: 3.5,
        colors: ['#696cff', '#03c3ec']
      },
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.5,
          opacityTo: 0.05
        }
      },
      colors: ['#696cff', '#03c3ec'],
      xaxis: {
        categories: [{!! $spendTrend->map(fn($item) => "'".$item['label']."'")->implode(',') !!}],
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        },
        labels: {
          style: {
            colors: '#a1acb8',
            fontSize: '13px'
          }
        }
      },
      yaxis: {
        labels: {
          style: {
            colors: '#a1acb8',
            fontSize: '13px'
          },
          formatter: function (value) {
            return "₹" + Math.round(value).toLocaleString('en-IN');
          }
        }
      },
      grid: {
        borderColor: '#eceef1',
        strokeDashArray: 5,
        xaxis: {
          lines: {
            show: false
          }
        },
        padding: {
          top: 10,
          right: 20,
          bottom: 0,
          left: 10
        }
      },
      tooltip: {
        theme: 'light',
        x: {
          show: true
        },
        y: {
          formatter: function (val) {
            return "₹" + Number(val).toLocaleString('en-IN');
          }
        }
      },
      markers: {
        size: 0,
        hover: {
          size: 6
        },
        discrete: [{
          seriesIndex: 0,
          dataPointIndex: {{ $peakIndex }},
          fillColor: '#696cff',
          strokeColor: '#ffffff',
          size: 8,
          shape: "circle"
        }]
      }
    };

    var chart = new ApexCharts(document.querySelector("#spendTrendChart"), options);
    chart.render();

    var branchDropdown = document.getElementById('dashboard_branch_id');
    var companyDropdown = document.getElementById('dashboard_company_id');
    var branchUrl = "{{ route('ajax.getbranch.bycompany', ':id') }}";

    function loadDashboardBranches(companyId, selectedId) {
      if (!branchDropdown) return;

      branchDropdown.innerHTML = '<option value="">Loading...</option>';
      branchDropdown.disabled = true;

      if (!companyId) {
        branchDropdown.innerHTML = '<option value="">All Branches</option>';
        branchDropdown.disabled = false;
        return;
      }

      fetch(branchUrl.replace(':id', companyId))
        .then(function (res) { return res.json(); })
        .then(function (data) {
          branchDropdown.innerHTML = '<option value="">All Branches</option>';
          if (data.data && data.data.length > 0) {
            data.data.forEach(function (branch) {
              var option = document.createElement('option');
              option.value = branch.id;
              option.textContent = branch.name;
              if (String(selectedId || '') === String(branch.id)) {
                option.selected = true;
              }
              branchDropdown.appendChild(option);
            });
          } else {
            branchDropdown.innerHTML = '<option value="">No branches found</option>';
          }
        })
        .catch(function () {
          branchDropdown.innerHTML = '<option value="">Failed to load branches</option>';
        })
        .finally(function () {
          branchDropdown.disabled = false;
        });
    }

    if (companyDropdown) {
      companyDropdown.addEventListener('change', function () {
        loadDashboardBranches(this.value, '');
      });
    }
  });
</script>
@endsection

@section('content')
@php
    $filterQuery = $filterQuery ?? [];
    $poListQuery = array_filter(array_merge($filterQuery, ['status' => 'po_created']));
@endphp
<div class="dashboard-container">

    <div class="row mb-4">
        <div class="col-12">
            <div class="card modern-card dashboard-filters">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('dashboard') }}" class="d-flex flex-wrap align-items-end gap-3">
                        <div class="dashboard-filters__field">
                            <label for="dashboard_company_id" class="form-label mb-1">Company</label>
                            <select id="dashboard_company_id" name="company_id" class="form-select">
                                <option value="">All Companies</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ (string) ($companyId ?? '') === (string) $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="dashboard-filters__field">
                            <label for="dashboard_branch_id" class="form-label mb-1">Branch</label>
                            <select id="dashboard_branch_id" name="branch_id" class="form-select">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ (string) ($branchId ?? '') === (string) $branch->id ? 'selected' : '' }}>
                                        {{ $branch->code ? $branch->code.' - ' : '' }}{{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-filter-alt me-1"></i> Apply
                            </button>
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Reset</a>
                            @can('dashboard:view')
                            <a href="{{ route('dashboard.export', request()->only(['company_id', 'branch_id'])) }}" class="btn btn-outline-success">
                                <i class="bx bx-export me-1"></i> Export Excel
                            </a>
                            <a href="{{ route('dashboard.export-pdf', request()->only(['company_id', 'branch_id'])) }}" class="btn btn-outline-danger">
                                <i class="bx bxs-file-pdf me-1"></i> Export PDF
                            </a>
                            @endcan
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-12">
            <div class="hero-gradient d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4">
                <div>
                    <h3 class="text-white mb-2 fw-semibold">Welcome back, {{ Auth::user()->name }}! 👋</h3>
                    <p class="text-white opacity-75 mb-0 fs-5">Purchase Orders, GRNs, Items, Stock and Work Orders at a glance.</p>
                </div>
                <div class="d-flex gap-3 align-self-start align-self-md-center flex-wrap">
                    <a href="{{ route('msu-purchase-orders.index', $poListQuery) }}" class="hero-metric-card d-flex align-items-center gap-3">
                        <div class="avatar rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: rgba(255, 255, 255, 0.2);">
                            <i class="bx bx-check-shield text-white fs-4"></i>
                        </div>
                        <div>
                            <small class="d-block opacity-75">Pending Approvals</small>
                            <h4 class="mb-0 text-white fw-bold">{{ $pendingApprovals }}</h4>
                        </div>
                    </a>

                    <a href="{{ route('msu-grns.index', $filterQuery) }}" class="hero-metric-card d-flex align-items-center gap-3">
                        <div class="avatar rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: rgba(255, 255, 255, 0.2);">
                            <i class="bx bx-package text-white fs-4"></i>
                        </div>
                        <div>
                            <small class="d-block opacity-75">GRNs Pending Stock</small>
                            <h4 class="mb-0 text-white fw-bold">{{ $pendingStockGrns }}</h4>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-lg-8 mb-4 mb-lg-0">
            <div class="card modern-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center pb-2">
                    <div>
                        <h5 class="card-title mb-1 fw-bold text-dark">PO & Work Order Spend Trend</h5>
                        <small class="text-muted">Last 6 months</small>
                    </div>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="spendTrendOpt" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded text-muted fs-4"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="spendTrendOpt">
                            <a class="dropdown-item" href="{{ route('msu-purchase-orders.index', $filterQuery) }}">All Purchase Orders</a>
                            <a class="dropdown-item" href="{{ route('msu-work-orders.index', $filterQuery) }}">All Work Orders</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="spendTrendChart"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 d-flex flex-column justify-content-between gap-4">
            <div class="card modern-card p-2 flex-grow-1">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-label-primary px-3 py-2 rounded">Active Purchase Order</span>
                            <small class="text-muted fw-semibold">PO Progress</small>
                        </div>
                        <h5 class="fw-bold mb-1 text-dark">
                            <a href="{{ $poApprovalProgress['url'] }}" class="text-dark">{{ $poApprovalProgress['po_no'] }}</a>
                        </h5>
                        <small class="text-muted d-block mb-3">{{ $poApprovalProgress['vendor_name'] }}</small>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold text-dark small">Status</span>
                            <span class="text-primary fw-bold small">{{ $poApprovalProgress['progress'] }}%</span>
                        </div>
                        <div class="progress mb-3" style="height: 8px; border-radius: 4px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $poApprovalProgress['progress'] }}%" aria-valuenow="{{ $poApprovalProgress['progress'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <div>
                            <small class="d-block text-muted">Estimated Value</small>
                            <span class="fw-bold text-dark">₹{{ number_format($poApprovalProgress['amount'], 2) }}</span>
                        </div>
                        <span class="badge bg-label-{{ $poApprovalProgress['status_class'] }}">{{ $poApprovalProgress['status'] }}</span>
                    </div>
                </div>
            </div>

            <div class="accent-card-red d-flex flex-column justify-content-between flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="text-white opacity-75 small fw-semibold d-block mb-1">Total Order Value</span>
                        <h3 class="text-white fw-bold mb-0">₹{{ number_format($totalPoSpend + $totalWoSpend, 2) }}</h3>
                    </div>
                    <div class="avatar rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(255, 255, 255, 0.25);">
                        <i class="bx bx-bar-chart-alt-2 text-white fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-white opacity-75 mb-3 small">PO ₹{{ number_format($totalPoSpend, 2) }} · WO ₹{{ number_format($totalWoSpend, 2) }} currently in the system.</p>
                    <a href="{{ route('msu-purchase-orders.index', $filterQuery) }}" class="btn btn-white w-100 fw-bold text-danger border-0 bg-white shadow-sm">
                        <i class="bx bx-file-find me-1"></i> View Purchase Orders
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-xl col-md-4 col-sm-6 mb-4 mb-xl-0">
            <a href="{{ route('msu-purchase-orders.index', $filterQuery) }}" class="kpi-link">
                <div class="card modern-card kpi-card h-100">
                    <div class="kpi-icon-wrapper bg-label-primary">
                        <i class="bx bx-receipt text-primary"></i>
                    </div>
                    <small class="text-muted d-block mb-1 fw-semibold">Purchase Orders</small>
                    <h3 class="card-title mb-2 fw-bold text-dark">{{ number_format($poCount) }}</h3>
                    <small class="text-primary fw-semibold">{{ $pendingPoApprovals }} pending approval</small>
                </div>
            </a>
        </div>

        <div class="col-xl col-md-4 col-sm-6 mb-4 mb-xl-0">
            <a href="{{ route('msu-grns.index', $filterQuery) }}" class="kpi-link">
                <div class="card modern-card kpi-card h-100">
                    <div class="kpi-icon-wrapper bg-label-warning">
                        <i class="bx bx-package text-warning"></i>
                    </div>
                    <small class="text-muted d-block mb-1 fw-semibold">Goods Receipts (GRN)</small>
                    <h3 class="card-title mb-2 fw-bold text-dark">{{ number_format($grnCount) }}</h3>
                    <small class="text-warning fw-semibold">{{ $pendingStockGrns }} pending stock</small>
                </div>
            </a>
        </div>

        <div class="col-xl col-md-4 col-sm-6 mb-4 mb-xl-0">
            <a href="{{ route('item-masters.index') }}" class="kpi-link">
                <div class="card modern-card kpi-card h-100">
                    <div class="kpi-icon-wrapper bg-label-info">
                        <i class="bx bx-cube text-info"></i>
                    </div>
                    <small class="text-muted d-block mb-1 fw-semibold">Active Items</small>
                    <h3 class="card-title mb-2 fw-bold text-dark">{{ number_format($itemCount) }}</h3>
                    <small class="text-info fw-semibold">Item master records</small>
                </div>
            </a>
        </div>

        <div class="col-xl col-md-4 col-sm-6 mb-4 mb-md-0">
            <a href="{{ route('item-stocks.index') }}" class="kpi-link">
                <div class="card modern-card kpi-card h-100">
                    <div class="kpi-icon-wrapper bg-label-success">
                        <i class="bx bx-box text-success"></i>
                    </div>
                    <small class="text-muted d-block mb-1 fw-semibold">Current Stock</small>
                    <h3 class="card-title mb-2 fw-bold text-dark">{{ number_format($currentStockQty, 2) }}</h3>
                    <small class="text-success fw-semibold">Available quantity</small>
                </div>
            </a>
        </div>

        <div class="col-xl col-md-4 col-sm-6">
            <a href="{{ route('msu-work-orders.index', $filterQuery) }}" class="kpi-link">
                <div class="card modern-card kpi-card h-100">
                    <div class="kpi-icon-wrapper bg-label-danger">
                        <i class="bx bx-briefcase text-danger"></i>
                    </div>
                    <small class="text-muted d-block mb-1 fw-semibold">Work Orders</small>
                    <h3 class="card-title mb-2 fw-bold text-dark">{{ number_format($woCount) }}</h3>
                    <small class="text-danger fw-semibold">{{ $pendingWoApprovals }} pending approval</small>
                </div>
            </a>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-lg-7 mb-4 mb-lg-0">
            <div class="card modern-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center pb-3">
                    <h5 class="card-title mb-0 fw-bold text-dark">Recent Purchase Orders</h5>
                    <a href="{{ route('msu-purchase-orders.index', $filterQuery) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">View All</a>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover border-top mb-0">
                        <thead>
                            <tr class="table-light">
                                <th class="fw-bold text-dark">PO Number</th>
                                <th class="fw-bold text-dark">Vendor</th>
                                <th class="fw-bold text-dark">Date</th>
                                <th class="fw-bold text-dark">Amount</th>
                                <th class="fw-bold text-dark">Status</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse($recentPurchaseOrders as $po)
                            <tr>
                                <td>
                                    <a href="{{ route('msu-purchase-orders.show', $po) }}" class="fw-bold text-dark">{{ $po->po_number }}</a>
                                </td>
                                <td>{{ $po->vendor?->name ?? 'N/A' }}</td>
                                <td>{{ $po->po_date?->format('d M Y') ?? 'N/A' }}</td>
                                <td><span class="fw-semibold text-dark">₹{{ number_format((float) $po->grand_total, 2) }}</span></td>
                                <td>
                                    <span class="badge bg-label-{{ $po->statusEnum()->badgeClass() }}">{{ $po->statusEnum()->label() }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No purchase orders found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card modern-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center pb-3">
                    <h5 class="card-title mb-0 fw-bold text-dark">Recent Work Orders</h5>
                    <a href="{{ route('msu-work-orders.index', $filterQuery) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">View All</a>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover border-top mb-0">
                        <thead>
                            <tr class="table-light">
                                <th class="fw-bold text-dark">WO Number</th>
                                <th class="fw-bold text-dark">Vendor</th>
                                <th class="fw-bold text-dark">Amount</th>
                                <th class="fw-bold text-dark">Status</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse($recentWorkOrders as $wo)
                            <tr>
                                <td>
                                    <a href="{{ route('msu-work-orders.show', $wo) }}" class="fw-bold text-dark">{{ $wo->wo_number }}</a>
                                </td>
                                <td>{{ $wo->vendor?->name ?? 'N/A' }}</td>
                                <td><span class="fw-semibold text-dark">₹{{ number_format((float) $wo->grand_total, 2) }}</span></td>
                                <td>
                                    <span class="badge bg-label-{{ $wo->statusEnum()->badgeClass() }}">{{ $wo->statusEnum()->label() }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No work orders found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7 mb-4 mb-lg-0">
            <div class="card modern-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center pb-3">
                    <h5 class="card-title mb-0 fw-bold text-dark">Recent Goods Receipts</h5>
                    <a href="{{ route('msu-grns.index', $filterQuery) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">View All</a>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover border-top mb-0">
                        <thead>
                            <tr class="table-light">
                                <th class="fw-bold text-dark">GRN Number</th>
                                <th class="fw-bold text-dark">PO Number</th>
                                <th class="fw-bold text-dark">Vendor</th>
                                <th class="fw-bold text-dark">Date</th>
                                <th class="fw-bold text-dark">Status</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse($recentGrns as $grn)
                            <tr>
                                <td>
                                    <a href="{{ route('msu-grns.show', $grn) }}" class="fw-bold text-dark">{{ $grn->grn_number }}</a>
                                </td>
                                <td>{{ $grn->purchaseOrder?->po_number ?? 'N/A' }}</td>
                                <td>{{ $grn->vendor?->name ?? 'N/A' }}</td>
                                <td>{{ $grn->grn_date?->format('d M Y') ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-label-{{ $grn->statusEnum()->badgeClass() }}">{{ $grn->statusEnum()->label() }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No goods receipts found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card modern-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center pb-3">
                    <h5 class="card-title mb-0 fw-bold text-dark">Recent Stock Movements</h5>
                    <a href="{{ route('item-stocks.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">View Stock</a>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover border-top mb-0">
                        <thead>
                            <tr class="table-light">
                                <th class="fw-bold text-dark">Item</th>
                                <th class="fw-bold text-dark">Type</th>
                                <th class="fw-bold text-dark">Qty</th>
                                <th class="fw-bold text-dark">Date</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse($recentStockMovements as $movement)
                            @php
                                $typeLabel = $movement->transaction_type === 'in' ? 'In' : ($movement->transaction_type === 'out' ? 'Out' : 'Adj');
                                $typeClass = $movement->transaction_type === 'in' ? 'success' : ($movement->transaction_type === 'out' ? 'danger' : 'warning');
                            @endphp
                            <tr>
                                <td>
                                    @if($movement->msu_item_master_id)
                                        <a href="{{ route('item-masters.show', $movement->msu_item_master_id) }}" class="fw-bold text-dark">
                                            {{ $movement->itemMaster?->item_name ?? 'Unknown Item' }}
                                        </a>
                                    @else
                                        <span class="fw-bold text-dark">{{ $movement->itemMaster?->item_name ?? 'Unknown Item' }}</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-label-{{ $typeClass }}">{{ $typeLabel }}</span></td>
                                <td>{{ number_format((float) $movement->quantity, 2) }}</td>
                                <td>{{ $movement->created_at?->format('d M Y') ?? 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No stock movements found.</td>
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
