<!-- Brand demo (display only for navbar-full and hide below xl) -->
@if(isset($navbarFull))
<div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
    <a href="{{ url('/') }}" class="app-brand-link gap-2">
        <span class="app-brand-logo demo">@include('_partials.macros')</span>
    </a>
</div>
@endif

<!-- Not required for layout-without-menu -->
@if(!isset($navbarHideToggle))
<div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 {{ isset($contentNavbar) ? 'd-xl-none' : '' }}">
    <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
        <i class="icon-base bx bx-menu icon-md"></i>
    </a>
</div>
@endif

<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
    <div class="navbar-nav align-items-center position-relative">
        @if (request()->routeIs(['msu-purchase-orders.*', 'msu-grns.*', 'material-requests.*', 'material-issues.*', 'msu-work-orders.*']))
        <div class="nav-item d-flex align-items-center">
            @php
                $globalCompanies = \App\Support\GlobalCompanyFilter::visibleCompaniesQuery()->get();
                $showAllCompanyOption = request()->routeIs([
                    'msu-purchase-orders.index',
                    'msu-grns.index',
                    'material-requests.index',
                    'material-issues.index',
                    'msu-work-orders.index',
                ]);
                $selectedCompanyId = session('global_company_id');

                // Drop stale session company the user can no longer access
                if (! empty($selectedCompanyId) && ! $globalCompanies->contains('id', (int) $selectedCompanyId)) {
                    session()->forget('global_company_id');
                    $selectedCompanyId = null;
                }
                if (!$showAllCompanyOption) {
                    // Create/edit pass $formCompanyId — keep navbar in lockstep with
                    // branch + doc-number filtering (PO and WO behave the same).
                    if (! empty($formCompanyId)) {
                        $selectedCompanyId = $formCompanyId;
                    } else {
                        $inferredCompanyId = null;
                        $routeParams = request()->route()->parameters();
                        if (! empty($routeParams)) {
                            $model = reset($routeParams);
                            if (is_object($model)) {
                                if (isset($model->company_id) && $model->company_id) {
                                    $inferredCompanyId = $model->company_id;
                                } elseif (! empty($model->branch_id)) {
                                    if (method_exists($model, 'relationLoaded') && $model->relationLoaded('branch') && $model->branch) {
                                        $inferredCompanyId = $model->branch->company_id;
                                    } else {
                                        $inferredCompanyId = \App\Models\Branch::whereKey($model->branch_id)->value('company_id');
                                    }
                                } elseif (isset($model->purchaseOrder) && ! empty($model->purchaseOrder?->branch_id)) {
                                    $inferredCompanyId = \App\Models\Branch::whereKey($model->purchaseOrder->branch_id)->value('company_id');
                                } elseif (isset($model->materialRequest)) {
                                    $inferredCompanyId = $model->materialRequest->company_id
                                        ?? (! empty($model->materialRequest->branch_id)
                                            ? \App\Models\Branch::whereKey($model->materialRequest->branch_id)->value('company_id')
                                            : null);
                                } elseif (isset($model->workOrder) && ! empty($model->workOrder?->branch_id)) {
                                    $inferredCompanyId = \App\Models\Branch::whereKey($model->workOrder->branch_id)->value('company_id');
                                }
                            }
                        }

                        // Display-only when session is empty (e.g. came from index "All").
                        // Never write session here so "All" survives Back.
                        if (empty($selectedCompanyId)) {
                            if ($inferredCompanyId) {
                                $selectedCompanyId = $inferredCompanyId;
                            } elseif ($globalCompanies->isNotEmpty()) {
                                $selectedCompanyId = $globalCompanies->first()->id;
                            }
                        }
                    }
                }
            @endphp
            <form action="{{ route('set-global-company') }}" method="POST" id="global-company-form" class="m-0 d-flex align-items-center">
                @csrf
                <i class="icon-base bx bx-building icon-md me-2"></i>
                <select name="global_company_id" autocomplete="off" class="form-select border-0 shadow-none bg-white fw-semibold" onchange="document.getElementById('global-company-form').submit()" style="min-width: 200px;">
                    @if($showAllCompanyOption)
                        <option value="" {{ empty($selectedCompanyId) ? 'selected' : '' }}>All</option>
                    @endif
                    @foreach($globalCompanies as $gc)
                        <option value="{{ $gc->id }}" {{ (string) $selectedCompanyId === (string) $gc->id ? 'selected' : '' }}>
                            {{ $gc->name }}
                        </option>
                    @endforeach
                </select>
            </form>
            <script>
                window.addEventListener('pageshow', function(event) {
                    const select = document.querySelector('select[name="global_company_id"]');
                    if (select) {
                        select.value = "{{ $selectedCompanyId ?? '' }}";
                    }
                });
            </script>
        </div>
        @endif
    </div>

    <ul class="navbar-nav flex-row align-items-center ms-auto">

        @auth
        @php
            $unreadNotifications = auth()->user()->unreadNotifications()->latest()->take(8)->get();
            $unreadCount = auth()->user()->unreadNotifications()->count();
        @endphp
        <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-2">
            <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                <span class="position-relative">
                    <i class="icon-base bx bx-bell icon-md"></i>
                    @if ($unreadCount > 0)
                        <span class="badge rounded-pill bg-danger badge-dot badge-notifications border" id="procurement-unread-count">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                    @endif
                </span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end py-0" style="min-width: 320px;">
                <li class="dropdown-menu-header border-bottom">
                    <div class="dropdown-header d-flex align-items-center py-3">
                        <h6 class="mb-0 me-auto">Notifications</h6>
                        @if ($unreadCount > 0)
                            <form method="POST" action="{{ route('notifications.read-all') }}">
                                @csrf
                                <button type="submit" class="btn btn-text-secondary btn-sm p-0">Mark all read</button>
                            </form>
                        @endif
                    </div>
                </li>
                <li class="dropdown-notifications-list scrollable-container" style="max-height: 320px; overflow:auto;">
                    <ul class="list-group list-group-flush">
                        @forelse ($unreadNotifications as $notification)
                            <li class="list-group-item list-group-item-action dropdown-notifications-item">
                                <a href="{{ $notification->data['url'] ?? 'javascript:void(0);' }}" class="d-block text-body">
                                    <div class="fw-semibold">{{ $notification->data['title'] ?? 'Notification' }}</div>
                                    <div class="small text-muted">{{ $notification->data['message'] ?? '' }}</div>
                                    <div class="small text-muted">{{ $notification->created_at?->diffForHumans() }}</div>
                                </a>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted py-4">No new notifications</li>
                        @endforelse
                    </ul>
                </li>
            </ul>
        </li>
        <script>
            (function () {
                const count = {{ (int) $unreadCount }};
                const key = 'procurement_unread_seen';
                const previous = parseInt(localStorage.getItem(key) || '0', 10);
                if (count > previous) {
                    try {
                        const ctx = new (window.AudioContext || window.webkitAudioContext)();
                        const oscillator = ctx.createOscillator();
                        const gain = ctx.createGain();
                        oscillator.type = 'sine';
                        oscillator.frequency.value = 880;
                        gain.gain.value = 0.05;
                        oscillator.connect(gain);
                        gain.connect(ctx.destination);
                        oscillator.start();
                        setTimeout(function () {
                            oscillator.stop();
                            ctx.close();
                        }, 180);
                    } catch (e) {}
                }
                localStorage.setItem(key, String(count));
            })();
        </script>
        @endauth

        <li class="nav-item navbar-dropdown dropdown-user dropdown">
            <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                <div class="avatar avatar-online">
                    <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle">
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="javascript:void(0);">
                        <div class="d-flex">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar avatar-online">
                                    <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle">
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">{{ auth()->user()->name }}</h6>
                                <small class="text-muted">{{ auth()->user()->designation->designation_name ?? '--' }}</small>
                            </div>
                        </div>
                    </a>
                </li>
                <li><div class="dropdown-divider my-1"></div></li>
                <li>
                    <a class="dropdown-item" href="javascript:void(0);">
                        <i class="icon-base bx bx-user icon-md me-3"></i><span>My Profile</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="javascript:void(0);">
                        <i class="icon-base bx bx-cog icon-md me-3"></i><span>Settings</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="javascript:void(0);">
                        <span class="d-flex align-items-center align-middle">
                            <i class="flex-shrink-0 icon-base bx bx-credit-card icon-md me-3"></i>
                            <span class="flex-grow-1 align-middle">Billing Plan</span>
                            <span class="flex-shrink-0 badge rounded-pill bg-danger">4</span>
                        </span>
                    </a>
                </li>
                <li><div class="dropdown-divider my-1"></div></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="icon-base bx bx-power-off icon-md me-3"></i><span>Log Out</span>
                        </button>
                    </form>
                </li>
            </ul>
        </li>
    </ul>
</div>
