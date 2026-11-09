(function () {
    'use strict';

    function initialize() {
        const tableElement = document.getElementById('returnRefundRequestsTable');
        if (!tableElement || tableElement.dataset.initialized) return;
        tableElement.dataset.initialized = 'true';

        const filters = ['rrStatusFilter', 'rrDateFrom', 'rrDateTo'].map(id => document.getElementById(id));
        const searchInput = document.getElementById('rrSearch');
        const requestRows = Array.from(tableElement.querySelectorAll('tbody > tr[data-statuses]'));
        const detailRows = new Map();
        let dataTable = null;

        function updateApprovalSelection(details) {
            const form = details.querySelector('.rr-bulk-approval');
            if (!form) return;
            const choices = Array.from(details.querySelectorAll('.rr-approval-select'));
            const selected = choices.filter(input => input.checked);
            const all = form.querySelector('.rr-select-all');
            all.checked = choices.length > 0 && selected.length === choices.length;
            all.indeterminate = selected.length > 0 && selected.length < choices.length;
            const units = selected.reduce((sum, input) => sum + Number(input.dataset.qty || 0), 0);
            details.querySelector('.rr-approval-summary').textContent = selected.length + ' ' + (selected.length === 1 ? 'product' : 'products') + ' · ' + units.toLocaleString() + ' units selected';
            details.querySelector('.rr-approve-selected').disabled = selected.length === 0 || form.dataset.submitting === 'true';
            choices.forEach(input => input.closest('.rr-product-detail').classList.toggle('is-selected', input.checked));
        }

        function matchesFilters(row) {
            const [status, from, to] = filters.map(input => input.value);
            const statuses = JSON.parse(row.dataset.statuses || '[]');
            const date = row.dataset.date || '';
            return (!status || statuses.includes(status)) && (!from || date >= from) && (!to || date <= to);
        }

        async function loadDataTables() {
            if (!window.jQuery) return false;
            if (window.jQuery.fn.DataTable) return true;
            // The layout's deferred app.js replaces jQuery. Load the plugin only
            // after DOM readiness, so it attaches to the final jQuery instance.
            for (const src of [tableElement.dataset.datatablesSrc, tableElement.dataset.datatablesFallbackSrc]) {
                if (!src) continue;
                const loaded = await new Promise(resolve => {
                    const script = document.createElement('script');
                    const finish = success => {
                        clearTimeout(timeout);
                        script.onload = script.onerror = null;
                        if (!success) script.remove();
                        resolve(success);
                    };
                    const timeout = setTimeout(() => finish(false), 6000);
                    script.onload = () => finish(true);
                    script.onerror = () => finish(false);
                    script.src = src;
                    document.head.appendChild(script);
                });
                if (loaded && window.jQuery.fn.DataTable) return true;
            }
            return false;
        }

        const tableReady = (async function () {
            try {
                if (!await loadDataTables()) return;
                const $ = window.jQuery;
                $.fn.dataTable.ext.search.push(function (settings, data, index) {
                    if (settings.nTable !== tableElement) return true;
                    const row = settings.aoData[index] && settings.aoData[index].nTr;
                    return !row || matchesFilters(row);
                });
                dataTable = $(tableElement).DataTable({
                    pageLength: 25,
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
                    pagingType: 'simple_numbers',
                    autoWidth: false,
                    dom: 'rt<"rr-datatable-footer"lip>',
                    order: [[0, 'desc']],
                    columnDefs: [{ targets: 4, orderable: false, searchable: false }],
                    language: {
                        lengthMenu: '_MENU_ per page',
                        info: '_START_–_END_ of _TOTAL_ requests',
                        infoEmpty: '0 requests',
                        emptyTable: 'No Return & Refund requests found.',
                        zeroRecords: 'No matching requests found.'
                    }
                });
            } catch (error) {
                console.warn('Return & Refund table enhancement could not load.', error);
            }
        })();

        // Bind independently of DataTables so a failed library load cannot
        // prevent access to the request's products and workflow forms.
        tableElement.addEventListener('click', async function (event) {
            const button = event.target.closest('.rr-view-products');
            if (!button || button.disabled) return;
            const parent = button.closest('tr');
            const opening = button.getAttribute('aria-expanded') !== 'true';
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            button.textContent = opening ? 'Opening products…' : 'Closing products…';
            try {
                await tableReady;
                if (!detailRows.has(parent)) {
                    const template = document.getElementById(button.dataset.template);
                    if (!template || !template.content.firstElementChild) return;
                    const detail = document.createElement('tr');
                    detail.className = 'rr-detail-row';
                    const cell = detail.insertCell();
                    cell.colSpan = parent.cells.length;
                    cell.appendChild(template.content.firstElementChild.cloneNode(true));
                    detailRows.set(parent, detail);
                }
                const detail = detailRows.get(parent);
                if (dataTable) {
                    const row = dataTable.row(parent);
                    if (!row.child()) row.child(detail);
                    if (opening) row.child.show();
                    else row.child.hide();
                } else if (opening) {
                    parent.insertAdjacentElement('afterend', detail);
                } else {
                    detail.remove();
                }
                const details = detail.querySelector('.rr-request-details');
                if (details) updateApprovalSelection(details);
                button.setAttribute('aria-expanded', opening ? 'true' : 'false');
            } finally {
                button.disabled = false;
                button.removeAttribute('aria-busy');
                button.textContent = button.getAttribute('aria-expanded') === 'true' ? 'Close products' : 'View products';
            }
        });

        async function refreshFilters() {
            await tableReady;
            if (dataTable) {
                dataTable.search(searchInput.value);
                dataTable.draw();
                return;
            }
            requestRows.forEach(row => {
                const searchable = row.textContent + ' ' + Array.from(row.querySelectorAll('[data-search]')).map(cell => cell.dataset.search).join(' ');
                const visible = matchesFilters(row) && searchable.toLocaleLowerCase().includes(searchInput.value.trim().toLocaleLowerCase());
                row.style.display = visible ? '' : 'none';
                const detail = detailRows.get(row);
                if (!detail) return;
                const expanded = row.querySelector('.rr-view-products').getAttribute('aria-expanded') === 'true';
                if (visible && expanded) row.insertAdjacentElement('afterend', detail);
                else detail.remove();
            });
        }
        filters.forEach(input => input.addEventListener('change', refreshFilters));
        searchInput.addEventListener('input', refreshFilters);
        document.getElementById('rrClearFilters').addEventListener('click', function () {
            filters.forEach(input => { input.value = ''; });
            searchInput.value = '';
            refreshFilters();
        });

        tableElement.addEventListener('change', function (event) {
            if (!event.target.matches('.rr-approval-select, .rr-select-all')) return;
            const details = event.target.closest('.rr-request-details');
            if (event.target.matches('.rr-select-all')) {
                details.querySelectorAll('.rr-approval-select').forEach(input => { input.checked = event.target.checked; });
            }
            updateApprovalSelection(details);
        });

        tableElement.addEventListener('submit', function (event) {
            const form = event.target;
            if (!form.matches('.rr-bulk-approval')) return;
            const details = form.closest('.rr-request-details');
            if (form.dataset.submitting === 'true' || !details.querySelector('.rr-approval-select:checked')) {
                event.preventDefault();
                return;
            }
            form.dataset.submitting = 'true';
            const button = details.querySelector('.rr-approve-selected');
            button.disabled = true;
            button.textContent = 'Approving products…';
        });

        tableElement.addEventListener('input', function (event) {
            const quantityInput = event.target.closest('.js-return-receive-form [name="warehouse_received_qty"]');
            if (!quantityInput) return;
            const form = quantityInput.closest('.js-return-receive-form');
            const remarks = form.querySelector('.js-receiving-remarks');
            const changed = Number(quantityInput.value || 0) !== Number(form.dataset.requestedQty || 0);
            remarks.required = changed;
            remarks.placeholder = changed ? 'Remarks are required for changed quantity' : 'Optional remarks';
        });

        tableElement.addEventListener('click', function (event) {
            const button = event.target.closest('.js-show-revert-form');
            if (!button) return;
            const form = document.getElementById(button.dataset.target);
            if (!form) return;
            const opening = form.classList.contains('d-none');
            form.classList.toggle('d-none', !opening);
            button.setAttribute('aria-expanded', opening ? 'true' : 'false');
            button.innerHTML = opening
                ? '<i class="bi bi-x-lg me-1"></i> Cancel revert'
                : '<i class="bi bi-arrow-counterclockwise me-1"></i> Revert';
        });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize, { once: true });
    else initialize();
})();
