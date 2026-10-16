<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('makePaymentModal');
    if (!modalEl) return;

    const form = document.getElementById('po-payment-form');
    const errorBox = document.getElementById('po-payment-form-errors');
    const submitBtn = document.getElementById('po-payment-submit-btn');
    const alertBox = document.getElementById('po-payment-alert');
    let storeUrl = null;
    let currentPoId = null;
    let submitting = false;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || form.querySelector('input[name="_token"]')?.value;

    let orderTotal = 0;
    let alreadyPaid = 0;
    let maxPayable = 0;

    function money(n) {
        return Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function updateRemainingBeforeSave() {
        const paidInput = document.getElementById('pay_paid');
        const remainingInput = document.getElementById('pay_remaining');
        const amount = parseFloat(paidInput.value) || 0;
        const remaining = Math.max(0, maxPayable - amount);
        if (remainingInput) remainingInput.value = money(remaining);
    }

    function showAlert(type, message) {
        if (!alertBox) {
            window.alert(message);
            return;
        }
        alertBox.className = 'alert alert-' + type + ' alert-dismissible';
        alertBox.innerHTML = message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        alertBox.classList.remove('d-none');
        window.scrollTo({ top: 0, behavior: 'smooth' });
        clearTimeout(alertBox._hideTimer);
        alertBox._hideTimer = setTimeout(function () {
            alertBox.classList.add('d-none');
            alertBox.innerHTML = '';
        }, 8000);
    }

    function fillSummary(summary) {
        orderTotal = Number(summary.total) || 0;
        alreadyPaid = Number(summary.paid) || 0;
        maxPayable = Number(summary.remaining) || 0;

        document.getElementById('pay_po_no').value = summary.po_no || '';
        document.getElementById('pay_vendor').value = summary.vendor_name || '';
        document.getElementById('pay_total').value = money(summary.total);

        const minPayable = Number(summary.min_advance_amount) || 0.01;
        const defaultPay = Math.max(minPayable, Math.min(maxPayable, maxPayable)); // or just use minPayable if it's the first payment, but setting value to 0 was original. Let's set it to minPayable if it's the first payment
        
        const paidInput = document.getElementById('pay_paid');
        paidInput.value = minPayable > 0.01 ? minPayable : '0';
        paidInput.min = minPayable;
        paidInput.max = maxPayable;
        paidInput.placeholder = 'Enter paid amount (max ' + money(maxPayable) + ')';

        const hint = document.getElementById('pay_already_paid_hint');
        if (hint) {
            if (alreadyPaid > 0) {
                hint.textContent = 'Already paid earlier: ₹' + money(alreadyPaid);
            } else if (minPayable > 0.01) {
                hint.innerHTML = '<span class="text-danger">Min. Advance Required: ₹' + money(minPayable) + '</span>';
            } else {
                hint.textContent = 'Enter the amount you are paying now';
            }
        }

        document.getElementById('pay_remaining').value = money(maxPayable);
    }

    function resetForm() {
        if (!form) return;
        form.reset();
        const paidInput = document.getElementById('pay_paid');
        if (paidInput) paidInput.value = '0';
        const paymentDate = document.getElementById('payment_date');
        if (paymentDate) paymentDate.value = new Date().toISOString().slice(0, 10);
        if (errorBox) {
            errorBox.classList.add('d-none');
            errorBox.innerHTML = '';
        }
        submitting = false;
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.querySelector('.btn-text')?.classList.remove('d-none');
            submitBtn.querySelector('.spinner-border')?.classList.add('d-none');
        }
    }

    function updateIndexRow(poId, summary) {
        const row = document.querySelector('tr[data-po-id="' + poId + '"]');
        if (!row) return;
        const paidEl = row.querySelector('.po-paid');
        const remEl = row.querySelector('.po-remaining');
        const statusEl = row.querySelector('.po-payment-status');
        if (paidEl) paidEl.textContent = money(summary.paid);
        if (remEl) remEl.textContent = money(summary.remaining);
        if (statusEl) {
            statusEl.textContent = summary.status;
            statusEl.className = 'badge bg-label-' + summary.status_badge + ' po-payment-status';
        }
        const makeBtn = row.querySelector('.btn-make-payment');
        if (summary.is_fully_paid && makeBtn) {
            makeBtn.closest('li').innerHTML = '<span class="dropdown-item text-muted disabled"><i class="bx bx-check me-1"></i> Fully Paid</span>';
        }
    }

    function updateShowPage(summary, payment) {
        const setText = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.textContent = val;
        };
        setText('summary-total', money(summary.total));
        setText('summary-paid', money(summary.paid));
        setText('summary-remaining', money(summary.remaining));
        const statusEl = document.getElementById('summary-payment-status');
        if (statusEl) {
            statusEl.textContent = summary.status;
            statusEl.className = 'badge bg-label-' + summary.status_badge;
        }

        const makeBtn = document.getElementById('btn-make-payment-show');
        const fullyPaidBadge = document.getElementById('po-fully-paid-badge');
        if (summary.is_fully_paid) {
            if (makeBtn) makeBtn.classList.add('d-none');
            if (fullyPaidBadge) fullyPaidBadge.classList.remove('d-none');
        }

        if (payment) {
            const tbody = document.getElementById('po-payment-history-body');
            const emptyRow = document.getElementById('po-payment-history-empty');
            if (emptyRow) emptyRow.remove();
            if (tbody) {
                let proofHtml = '<span class="text-muted">No Attachment</span>';
                if (payment.has_attachment) {
                    proofHtml = '<a class="btn btn-sm btn-outline-primary me-1" href="' + payment.view_url + '" target="_blank">View</a>'
                        + '<a class="btn btn-sm btn-outline-secondary" href="' + payment.download_url + '">Download</a>';
                }
                const tr = document.createElement('tr');
                tr.innerHTML = '<td>' + payment.payment_date + '</td>'
                    + '<td>' + payment.payment_amount + '</td>'
                    + '<td>' + payment.payment_method + '</td>'
                    + '<td>' + proofHtml + '</td>'
                    + '<td>' + payment.created_by + '</td>'
                    + '<td>' + payment.created_at + '</td>';
                tbody.prepend(tr);
            }
        }
    }

    document.getElementById('pay_paid')?.addEventListener('input', updateRemainingBeforeSave);

    document.querySelectorAll('.btn-make-payment').forEach(function (btn) {
        btn.addEventListener('click', function () {
            storeUrl = btn.getAttribute('data-store-url');
            currentPoId = btn.getAttribute('data-po-id');
            const summaryUrl = btn.getAttribute('data-summary-url');
            resetForm();

            fetch(summaryUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.json())
                .then(function (data) {
                    if (!data.success) throw new Error(data.message || 'Unable to load payment summary.');
                    if (data.summary.allows_payment === false) {
                        showAlert('warning', 'Payment is not allowed until this Purchase Order is fully approved.');
                        return;
                    }
                    if (data.summary.is_fully_paid) {
                        showAlert('info', 'Purchase Order Fully Paid');
                        return;
                    }
                    fillSummary(data.summary);
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                })
                .catch(function (err) {
                    showAlert('danger', err.message || 'Unable to open payment form.');
                });
        });
    });

    form?.addEventListener('submit', function (e) {
        e.preventDefault();
        if (submitting || !storeUrl || !form) return;
        if (!form.reportValidity()) return;

        const amount = parseFloat(document.getElementById('pay_paid')?.value) || 0;
        if (amount <= 0) {
            if (errorBox) {
                errorBox.textContent = 'Paid amount must be greater than zero.';
                errorBox.classList.remove('d-none');
            }
            return;
        }
        if (amount > maxPayable + 0.001) {
            if (errorBox) {
                errorBox.textContent = 'Paid amount cannot exceed the remaining balance of ₹' + money(maxPayable) + '.';
                errorBox.classList.remove('d-none');
            }
            return;
        }

        submitting = true;
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.querySelector('.btn-text')?.classList.add('d-none');
            submitBtn.querySelector('.spinner-border')?.classList.remove('d-none');
        }
        if (errorBox) errorBox.classList.add('d-none');

        const fd = new FormData(form);
        fetch(storeUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf
            },
            body: fd
        })
            .then(async function (res) {
                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.success) {
                    let msg = data.message || 'Unable to save payment.';
                    if (data.errors) {
                        msg = Object.values(data.errors).flat().join('<br>');
                    }
                    throw new Error(msg);
                }
                return data;
            })
            .then(function (data) {
                bootstrap.Modal.getInstance(modalEl)?.hide();
                const successMsg = data.message || ('Payment saved successfully! Remaining Balance: ₹' + (data.summary ? money(data.summary.remaining) : '0.00'));
                showAlert('success', successMsg);
                if (currentPoId) updateIndexRow(currentPoId, data.summary);
                updateShowPage(data.summary, data.payment);
            })
            .catch(function (err) {
                if (errorBox) {
                    errorBox.innerHTML = err.message || 'Unable to save payment.';
                    errorBox.classList.remove('d-none');
                }
            })
            .finally(function () {
                submitting = false;
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.querySelector('.btn-text')?.classList.remove('d-none');
                    submitBtn.querySelector('.spinner-border')?.classList.add('d-none');
                }
            });
    });

    let targetPayReqForm = null;
    let currentPoTotal = 0;
    let currentMinAdv = 0;

    const genModalEl = document.getElementById('generatePaymentRequestModal');
    const genModalInstance = genModalEl ? bootstrap.Modal.getOrCreateInstance(genModalEl) : null;
    const genPercentInput = document.getElementById('gen_req_percent');
    const genAmountInput = document.getElementById('gen_req_amount');
    const genErrorBox = document.getElementById('gen-pay-req-error');
    const genSubmitBtn = document.getElementById('btn-submit-pay-req-modal');

    function validateMinAdv() {
        if (!genErrorBox || !genSubmitBtn) return;
        const pct = parseFloat(genPercentInput?.value || 0);
        if (pct < currentMinAdv - 0.001) {
            genErrorBox.textContent = `Requested advance percentage (${pct.toFixed(2)}%) cannot be less than the mandatory minimum advance payment percentage of ${currentMinAdv.toFixed(2)}%!`;
            genErrorBox.classList.remove('d-none');
            genSubmitBtn.disabled = true;
        } else {
            genErrorBox.classList.add('d-none');
            genSubmitBtn.disabled = false;
        }
    }

    if (genPercentInput && genAmountInput) {
        genPercentInput.addEventListener('input', function() {
            const pct = parseFloat(this.value || 0);
            if (currentPoTotal > 0) {
                genAmountInput.value = (currentPoTotal * (pct / 100)).toFixed(2);
            }
            validateMinAdv();
        });

        genAmountInput.addEventListener('input', function() {
            const amt = parseFloat(this.value || 0);
            if (currentPoTotal > 0) {
                genPercentInput.value = ((amt / currentPoTotal) * 100).toFixed(2);
            }
            validateMinAdv();
        });
    }

    document.querySelectorAll('.btn-payment-request-confirm').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            targetPayReqForm = btn.closest('form');
            const poNo = btn.dataset.po || 'this PO';
            currentPoTotal = parseFloat(btn.dataset.total) || 0;
            currentMinAdv = parseFloat(btn.dataset.minAdv) || 0;

            if (genModalEl && genModalInstance) {
                document.getElementById('gen_req_po_no').value = poNo;
                document.getElementById('gen_req_total_display').value = `₹${money(currentPoTotal)}`;
                
                // Initialize to mandatory min advance or 100%
                const initPct = currentMinAdv > 0 ? currentMinAdv : 100;
                genPercentInput.value = initPct.toFixed(2);
                genAmountInput.value = (currentPoTotal * (initPct / 100)).toFixed(2);
                validateMinAdv();
                genModalInstance.show();
            } else {
                if (confirm('Generate Payment Request for ' + poNo + '?')) {
                    targetPayReqForm?.submit();
                }
            }
        });
    });

    genSubmitBtn?.addEventListener('click', function() {
        if (!targetPayReqForm) return;
        const pct = parseFloat(genPercentInput?.value || 0);
        const amt = parseFloat(genAmountInput?.value || 0);
        
        let hiddenPct = targetPayReqForm.querySelector('input[name="percentage"]');
        if (!hiddenPct) {
            hiddenPct = document.createElement('input');
            hiddenPct.type = 'hidden';
            hiddenPct.name = 'percentage';
            targetPayReqForm.appendChild(hiddenPct);
        }
        hiddenPct.value = pct;

        let hiddenAmt = targetPayReqForm.querySelector('input[name="amount"]');
        if (!hiddenAmt) {
            hiddenAmt = document.createElement('input');
            hiddenAmt.type = 'hidden';
            hiddenAmt.name = 'amount';
            targetPayReqForm.appendChild(hiddenAmt);
        }
        hiddenAmt.value = amt;

        genModalInstance?.hide();
        targetPayReqForm.submit();
    });

    @if(session('success') && str_contains(strtolower(session('success')), 'payment request'))
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Success!',
                text: '{!! session('success') !!}',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        }
    @endif
});
</script>
