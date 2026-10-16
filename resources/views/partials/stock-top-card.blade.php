{{-- Top stock summary card with an X-in-square at the top-right edge --}}
<div class="card mb-4 stock-top-card" style="position:relative; overflow:visible;">
  <style>
    .stock-top-card {
      background: linear-gradient(90deg, #0b1220 0%, #122033 100%);
      color: #fff;
      border-radius: .5rem;
    }

    .stock-top-card .card-body {
      padding: 1.25rem;
    }

    .stock-top-card .kpi {
      display: flex;
      gap: 1rem;
      align-items: center;
    }

    .stock-top-card .kpi .value {
      font-weight: 700;
      font-size: 1.25rem;
    }

    .stock-top-card .close-square {
      position: absolute;
      top: -12px;
      right: -12px;
      width: 40px;
      height: 40px;
      background: #fff;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 8px 20px rgba(12, 18, 28, 0.35);
    }

    .stock-top-card .close-square i {
      color: #222;
      font-size: 18px;
    }

    @media (max-width:576px) {
      .stock-top-card .kpi {
        flex-direction: column;
        align-items: flex-start;
      }
    }
  </style>

  <div class="close-square" title="Close">
    <i class="bx bx-x"></i>
  </div>

  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h6 class="mb-1">Available Items</h6>
        <div class="kpi">
          <div class="value">6</div>
          <div class="text-muted small">+0.0%</div>
        </div>
      </div>
      <div class="text-end">
        <small class="d-block text-muted">Total Cost</small>
        <div class="fw-bold">$12,330.00</div>
      </div>
    </div>
  </div>
</div>
