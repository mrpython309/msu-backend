<div class="modal fade" id="itemHistoryModal" tabindex="-1" aria-labelledby="itemHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemHistoryModalLabel">Previous Item Purchase History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="item-history-loading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0">Fetching purchase order history...</p>
                </div>
                <div id="item-history-content" class="d-none">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>PO No.</th>
                                    <th>Item Name</th>
                                    <th>Vendor</th>
                                    <th>Date of Purchase</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody id="item-history-table-body">
                            </tbody>
                        </table>
                    </div>
                    <div id="item-history-empty" class="text-center text-muted py-4 d-none">
                        No previous purchase history found for this item.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const historyModalEl = document.getElementById('itemHistoryModal');
    if (!historyModalEl) return;
    const historyModal = new bootstrap.Modal(historyModalEl);
    const loadingEl = document.getElementById('item-history-loading');
    const contentEl = document.getElementById('item-history-content');
    const tbodyEl = document.getElementById('item-history-table-body');
    const emptyEl = document.getElementById('item-history-empty');
    const modalTitle = document.getElementById('itemHistoryModalLabel');
    const historyUrl = @json(route('procurement.items.history'));

    function escHtml(str) {
        return String(str ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    window.fetchAndShowItemHistory = function(itemName, itemCode, excludePoId = null, silentIfEmpty = false) {
        if (!itemName && !itemCode) return;

        if (!excludePoId) {
            const currentPoEl = document.querySelector('[data-current-po-id], input[name="po_id"], #current_po_id');
            if (currentPoEl) {
                excludePoId = currentPoEl.getAttribute('data-current-po-id') || currentPoEl.value || '';
            } else {
                const match = window.location.pathname.match(/\/purchase-orders\/(\d+)/);
                if (match) excludePoId = match[1];
            }
        }

        const query = '?item_name=' + encodeURIComponent(itemName) + '&item_code=' + encodeURIComponent(itemCode) + '&exclude_po_id=' + encodeURIComponent(excludePoId || '');
        
        if (!silentIfEmpty) {
            modalTitle.textContent = 'Previous Purchase History: ' + (itemName || itemCode);
            loadingEl.classList.remove('d-none');
            contentEl.classList.add('d-none');
            emptyEl.classList.add('d-none');
            tbodyEl.innerHTML = '';
            historyModal.show();
        }

        fetch(historyUrl + query, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (!data || !data.success || !data.history || data.history.length === 0) {
                if (silentIfEmpty) return;
                loadingEl.classList.add('d-none');
                contentEl.classList.remove('d-none');
                emptyEl.classList.remove('d-none');
                return;
            }

            if (silentIfEmpty) {
                modalTitle.textContent = 'Previous Purchase History: ' + (itemName || itemCode);
                loadingEl.classList.add('d-none');
                emptyEl.classList.add('d-none');
                tbodyEl.innerHTML = '';
                historyModal.show();
            } else {
                loadingEl.classList.add('d-none');
            }
            
            contentEl.classList.remove('d-none');

            let rowsHtml = '';
            data.history.forEach(function (po) {
                const poNoDisplay = po.url && po.url !== '#'
                    ? `<a href="${escHtml(po.url)}" target="_blank" class="fw-bold">${escHtml(po.po_no)}</a>`
                    : `<strong class="text-dark">${escHtml(po.po_no)}</strong>`;
                    
                rowsHtml += `<tr>
                    <td>${poNoDisplay}</td>
                    <td><strong>${escHtml(po.item_name || '--')}</strong></td>
                    <td>${escHtml(po.vendor)}</td>
                    <td>${escHtml(po.date)}</td>
                    <td class="text-end">${escHtml(po.quantity)}</td>
                    <td class="text-end">${escHtml(po.item_price || po.rate)} ${escHtml(po.currency)}</td>
                    <td class="text-end fw-bold">${escHtml(po.total)} ${escHtml(po.currency)}</td>
                </tr>`;
            });
            tbodyEl.innerHTML = rowsHtml;
        })
        .catch(err => {
            if (silentIfEmpty) return;
            loadingEl.classList.add('d-none');
            contentEl.classList.remove('d-none');
            emptyEl.classList.remove('d-none');
            emptyEl.textContent = 'Failed to load purchase order history. Please try again.';
        });
    };

    document.body.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-view-history');
        if (!btn) return;
        e.preventDefault();

        let itemName = btn.getAttribute('data-item-name') || '';
        let itemCode = btn.getAttribute('data-item-code') || '';

        // If embedded in a dynamic row in create forms, attempt to find input value if attribute is empty
        if (!itemName) {
            const row = btn.closest('.item-row, .po-item-block, tr');
            if (row) {
                const nameInput = row.querySelector('.field-item-name, input[name*="[item_name]"]');
                if (nameInput) itemName = nameInput.value.trim();
                
                const codeInput = row.querySelector('.field-item-code, input[name*="[item_code]"]');
                if (codeInput) itemCode = codeInput.value.trim();
            }
        }

        if (!itemName && !itemCode) {
            alert('Please enter or select an asset name first to view its previous history.');
            return;
        }

        let excludePoId = btn.getAttribute('data-exclude-po-id') || btn.getAttribute('data-current-po-id') || null;
        window.fetchAndShowItemHistory(itemName, itemCode, excludePoId, false);
    });
});
</script>
