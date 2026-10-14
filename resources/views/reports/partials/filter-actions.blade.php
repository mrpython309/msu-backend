<div class="col-md-2">
    <label class="form-label">From Date</label>
    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
</div>

<div class="col-md-2">
    <label class="form-label">To Date</label>
    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
</div>

<div class="col-md-1 d-flex align-items-end">
    <a href="{{ $clearRoute }}" class="btn btn-outline-secondary w-100">Clear</a>
</div>

<div class="col-md-1 d-flex align-items-end">
    <button type="submit" class="btn btn-primary w-100">GO</button>
</div>

<div class="col-md-1 d-flex align-items-end">
    <button type="button" class="btn btn-success w-100 js-report-export-excel">Excel</button>
</div>

<div class="col-md-1 d-flex align-items-end">
    <button type="button" class="btn btn-danger w-100 js-report-export-pdf">PDF</button>
</div>

@once
<script>
(function () {
    function submitReportExport(btn, format) {
        const form = btn.closest('form');
        if (!form) {
            alert('No filter form found for export.');
            return;
        }

        let input = form.querySelector('input[name="export_format"]');
        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'export_format';
            form.appendChild(input);
        }

        input.value = format;
        form.submit();
    }

    document.addEventListener('click', function (e) {
        const goBtn = e.target.closest('button[type="submit"]');
        if (goBtn) {
            const form = goBtn.closest('form');
            const exportInput = form?.querySelector('input[name="export_format"]');
            if (exportInput) {
                exportInput.remove();
            }
        }

        if (e.target.closest('.js-report-export-excel')) {
            e.preventDefault();
            submitReportExport(e.target.closest('.js-report-export-excel'), 'csv');
        }
        if (e.target.closest('.js-report-export-pdf')) {
            e.preventDefault();
            submitReportExport(e.target.closest('.js-report-export-pdf'), 'pdf');
        }
    });
})();
</script>
@endonce
