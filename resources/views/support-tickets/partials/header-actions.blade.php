<style>
  .support-ticket-header-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }
  .support-ticket-header-actions .rt-notification-dropdown {
    position: relative;
    overflow: visible !important;
  }
  .support-ticket-header-actions .rt-notification-dropdown .dropdown-menu,
  .support-ticket-header-actions .rt-notification-dropdown .rt-notification-menu {
    max-height: 400px !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    right: 0 !important;
    left: auto !important;
    top: 100% !important;
    z-index: 1080 !important;
  }
  .support-ticket-header-actions .notification-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 10px 12px;
    white-space: nowrap;
    font-size: 14px;
  }
  .support-ticket-header-actions .notification-item .content {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-grow: 1;
    flex-wrap: nowrap;
    overflow: hidden;
  }
  .support-ticket-header-actions .task-name {
    font-size: 14px;
    font-weight: 500;
    white-space: nowrap;
  }
  .support-ticket-header-actions .badge {
    font-size: 11px;
    padding: 3px 6px;
    white-space: nowrap;
  }
  .support-ticket-header-actions .view-icon {
    margin-left: auto;
    display: flex;
    align-items: center;
    padding-left: 10px;
  }
  .support-ticket-header-actions .list-group-item {
    padding: 0;
  }
</style>

<div class="support-ticket-header-actions">
    <div class="nav-item dropdown-notifications navbar-dropdown dropdown rt-notification-dropdown">
        <a
            class="nav-link dropdown-toggle hide-arrow position-relative p-0"
            href="javascript:void(0);"
            data-bs-toggle="dropdown"
            data-bs-auto-close="outside"
            aria-expanded="false"
            id="rtTodayNotificationsToggle"
        >
            <i class="icon-base bx bx-bell icon-md"></i>
            <span
                id="rtTodayNotificationsBadge"
                class="badge rounded-pill bg-danger badge-notifications d-none"
            >0</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end py-0 rt-notification-menu" style="min-width: 360px;" id="rtTodayNotificationsMenu">
            <li class="dropdown-menu-header border-bottom">
                <div class="dropdown-header d-flex align-items-center py-3">
                    <h6 class="mb-0 me-auto">Today Tasks</h6>
                    <small id="rtTodayNotificationsSummary" class="text-muted">Total: 0 | High: 0</small>
                </div>
            </li>
            <li class="dropdown-notifications-list scrollable-container">
                <ul class="list-group list-group-flush" id="rtTodayNotificationsList">
                    <li class="list-group-item text-muted small">No tasks for today.</li>
                </ul>
            </li>
        </ul>
    </div>

    <div class="nav-item navbar-dropdown dropdown-user dropdown">
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
            <li>
                <div class="dropdown-divider my-1"></div>
            </li>
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
                <div class="dropdown-divider my-1"></div>
            </li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item">
                        <i class="icon-base bx bx-power-off icon-md me-3"></i><span>Log Out</span>
                    </button>
                </form>
            </li>
        </ul>
    </div>
</div>

@once
<script>
  (function () {
    var url = null;
    var taskBaseUrl = null;
    var badge = document.getElementById('rtTodayNotificationsBadge');
    var list = document.getElementById('rtTodayNotificationsList');
    var summary = document.getElementById('rtTodayNotificationsSummary');
    if (!url || !taskBaseUrl || !badge || !list || !summary) return;

    function esc(s) {
      var d = document.createElement('div');
      d.textContent = String(s == null ? '' : s);
      return d.innerHTML;
    }

    function priorityBadgeClass(priority) {
      var p = String(priority || '').toLowerCase();
      if (p === 'low') return 'bg-warning';
      if (p === 'medium' || p === 'normal') return 'bg-info';
      if (p === 'high' || p === 'urgent') return 'bg-danger';
      return 'bg-secondary';
    }

    function statusBadgeClass(status) {
      var s = String(status || '').toLowerCase();
      if (s === 'pending') return 'bg-success';
      if (s === 'open') return 'bg-primary';
      if (s === 'completed' || s === 'closed') return 'bg-success';
      if (s === 'in_progress') return 'bg-warning';
      if (s === 'overdue') return 'bg-danger';
      if (s === 'accepted') return 'bg-info';
      if (s === 'hold' || s === 'on_hold') return 'bg-secondary';
      return 'bg-secondary';
    }

    function render(payload) {
      var rows = Array.isArray(payload.items) ? payload.items : [];
      var seenTaskIds = {};
      rows = rows.filter(function (it) {
        var id = Number(it.task_id || 0);
        if (id <= 0) return false;
        if (seenTaskIds[id]) return false;
        seenTaskIds[id] = true;
        return true;
      });

      var total = rows.length;
      var high = Number(payload.high_priority_count || 0);
      summary.textContent = 'Total: ' + total + ' | High: ' + high;
      badge.textContent = String(total);
      badge.classList.toggle('d-none', total <= 0);
      if (!rows.length) {
        list.innerHTML = '<li class="list-group-item text-muted small">No tasks for today.</li>';
        return;
      }
      list.innerHTML = rows.map(function (it) {
        var pr = esc(it.priority_label || it.priority || 'Normal');
        var st = esc(it.status_label || it.status || 'Open');
        var prClass = priorityBadgeClass(it.priority);
        var stClass = statusBadgeClass(it.status || 'open');
        var taskName = esc(it.task_name || '');
        var taskId = Number(it.task_id || 0);
        var viewUrl = taskId > 0 ? (taskBaseUrl + '/' + taskId) : '#';
        return ''
          + '<li class="list-group-item">'
          + '<div class="notification-item">'
          + '<div class="content">'
          + '<strong class="task-name">' + taskName + '</strong>'
          + '<span class="badge ' + prClass + '">' + pr + '</span>'
          + '<span class="badge ' + stClass + '">' + st + '</span>'
          + '</div>'
          + '<a href="' + viewUrl + '" class="text-muted view-icon" title="View task" aria-label="View task">'
          + '<i class="icon-base bx bx-show icon-sm"></i>'
          + '</a>'
          + '</div>'
          + '</li>';
      }).join('');
    }

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
      .then(function (res) { return res.ok ? res.json() : null; })
      .then(function (data) { render(data || {}); })
      .catch(function () {
        summary.textContent = 'Total: 0 | High: 0';
      });
  })();
</script>
@endonce
