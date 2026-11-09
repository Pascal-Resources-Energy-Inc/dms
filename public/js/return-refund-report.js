(function () {
    'use strict';

    function initialize() {
        const element = document.getElementById('returnRefundReportTable');
        if (!element || element.dataset.initialized) return;
        element.dataset.initialized = 'true';
        const state = document.getElementById('returnRefundReportError');
        const message = state.querySelector('[data-report-message]');
        const retry = document.getElementById('returnRefundReportRetry');
        const filters = ['returnRefundStatusFilter', 'returnRefundWarehouseFilter', 'returnRefundDateFrom', 'returnRefundDateTo'].map(id => document.getElementById(id));
        let table, starting = false, latestDraw = 0;

        function showState(text, error) {
            message.textContent = text;
            state.classList.remove('d-none');
            state.classList.toggle('is-error', error);
            retry.classList.toggle('d-none', !error);
        }

        async function loadDataTables() {
            if (!window.jQuery) return false;
            if (window.jQuery.fn.DataTable) return true;
            // app.js is deferred and replaces jQuery. Attach DataTables after
            // DOM readiness, to the instance that the report will actually use.
            for (const src of [element.dataset.datatablesSrc, element.dataset.datatablesFallbackSrc]) {
                if (!src) continue;
                await new Promise(resolve => {
                    const script = document.createElement('script');
                    const finish = () => {
                        clearTimeout(timeout);
                        script.onload = script.onerror = null;
                        script.remove();
                        resolve();
                    };
                    const timeout = setTimeout(finish, 6000);
                    script.onload = script.onerror = finish;
                    script.src = src;
                    document.head.appendChild(script);
                });
                if (window.jQuery.fn.DataTable) return true;
            }
            return false;
        }

        async function start() {
            if (starting) return;
            if (table) return table.ajax.reload();
            starting = true;
            showState('Loading Return & Refund requests…', false);
            try {
                if (!await loadDataTables()) throw new Error('Table library unavailable');
                const $ = window.jQuery;
                table = $(element).DataTable({
                    processing: true, serverSide: true, deferRender: true, searchDelay: 350,
                    pageLength: 25, lengthMenu: [[25, 50, 100], [25, 50, 100]],
                    pagingType: 'simple_numbers', scrollX: true, scrollCollapse: true, autoWidth: false,
                    dom: '<"rrr-datatable-tools"lf>rt<"rrr-datatable-footer"ip>', order: [[0, 'desc']],
                    language: {
                        processing: 'Loading requests…', search: 'Search transactions:',
                        searchPlaceholder: 'Reference, product, document…', lengthMenu: 'Show _MENU_ requests',
                        emptyTable: 'No Return & Refund requests found.',
                        zeroRecords: 'No matching Return & Refund requests found.'
                    },
                    ajax: function (data, callback) {
                        latestDraw = data.draw;
                        [data.status, data.warehouse, data.date_from, data.date_to] = filters.map(input => input.value);
                        showState('Loading Return & Refund requests…', false);
                        const failed = function (status) {
                            if (data.draw !== latestDraw) return;
                            showState(status === 403 ? 'You do not have access to view this report.'
                                : status === 401 || status === 419 ? 'Your session has expired. Sign in again to view this report.'
                                : 'Unable to load Return & Refund requests. Please try again.', true);
                            // Finish the draw on failure so the loading indicator stops.
                            callback({draw: data.draw, recordsTotal: 0, recordsFiltered: 0, data: []});
                            $(element).find('td.dataTables_empty').text('Report data is unavailable. Use Try again above.');
                        };
                        $.ajax({url: element.dataset.url, data: data, dataType: 'json', timeout: 30000})
                            .done(function (response) {
                                if (data.draw !== latestDraw) return;
                                if (!response || response.error || !Array.isArray(response.data)) return failed(0);
                                state.classList.add('d-none');
                                callback(response);
                            })
                            .fail(xhr => failed(xhr.status));
                    },
                    columns: [
                        {data: 'transfer_date', name: 'return_refund_rows.transfer_date'},
                        {data: 'reference_no', name: 'return_refund_rows.reference_no'},
                        {data: 'distributor', name: 'distributors.name'},
                        {data: 'products', orderable: false},
                        {data: 'ris_details', orderable: false, searchable: false, render: function (value, type, row) {
                            return (value || '') + (type === 'display' && row.ris_submission
                                ? '<button type="button" class="btn btn-outline-primary btn-sm rrr-submit-ris">Submit RIS documents</button>' : '');
                        }},
                        {data: 'receiving_details', orderable: false, searchable: false},
                        {data: 'credit_memo_details', orderable: false, searchable: false},
                        {data: 'warehouse', name: 'return_refund_rows.warehouse'},
                        {data: 'status_badge', name: 'return_refund_rows.approval_status', orderable: false},
                        {data: 'remarks_display', name: 'return_refund_rows.warehouse_remarks'}
                    ]
                });
            } catch (error) {
                showState('Unable to load the report table. Please try again.', true);
            } finally {
                starting = false;
            }
        }

        filters.forEach(input => input.addEventListener('change', () => { if (table) table.draw(); }));
        document.getElementById('returnRefundClearFilters').addEventListener('click', function () {
            filters.forEach(input => { input.value = ''; });
            if (table) table.search('').draw();
        });
        retry.addEventListener('click', start);

        const dialog = document.getElementById('returnRefundRisDialog');
        if (dialog) {
            const form = document.getElementById('returnRefundRisForm');
            const errors = document.getElementById('rrrRisErrors');
            const submit = document.getElementById('rrrRisSubmit');
            const files = document.getElementById('rrrRisAttachments');
            let submitting = false, previousOverflow = '';
            function displayErrors(messages) {
                errors.replaceChildren();
                const list = document.createElement('ul');
                messages.forEach(message => { const item = document.createElement('li'); item.textContent = message; list.appendChild(item); });
                errors.appendChild(list);
                errors.classList.remove('d-none');
                errors.focus();
            }
            element.addEventListener('click', function (event) {
                const button = event.target.closest('.rrr-submit-ris');
                if (!button || !table) return;
                const submission = table.row(button.closest('tr')).data().ris_submission;
                if (!submission || !submission.products.length) return;
                form.reset();
                form.action = submission.url;
                errors.classList.add('d-none');
                document.getElementById('rrrRisReference').textContent = submission.reference;
                const list = document.getElementById('rrrRisProductList');
                list.replaceChildren();
                submission.products.forEach(product => {
                    const item = document.createElement('li'), details = document.createElement('div');
                    const name = document.createElement('strong'), sku = document.createElement('small');
                    name.textContent = product.name; sku.textContent = product.sku || 'No SKU';
                    details.append(name, sku);
                    const quantity = document.createElement('span'); quantity.textContent = Number(product.qty).toLocaleString() + ' pcs';
                    const id = document.createElement('input'); id.type = 'hidden'; id.name = 'product_ids[]'; id.value = product.id;
                    item.append(details, quantity, id); list.appendChild(item);
                });
                previousOverflow = document.body.style.overflow;
                document.body.style.overflow = 'hidden';
                dialog.showModal();
                document.getElementById('rrrRisNumber').focus();
            });
            dialog.querySelectorAll('[data-close-ris]').forEach(button => button.addEventListener('click', () => { if (!submitting) dialog.close(); }));
            dialog.addEventListener('cancel', event => { if (submitting) event.preventDefault(); });
            dialog.addEventListener('close', () => { document.body.style.overflow = previousOverflow; });
            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                if (submitting) return;
                const selected = Array.from(files.files);
                if (!selected.length || selected.length > 5) return displayErrors(['Choose between 1 and 5 attachments.']);
                if (selected.some(file => file.size > 5 * 1024 * 1024)) return displayErrors(['Each attachment must be 5 MB or smaller.']);
                if (selected.some(file => !/\.(jpe?g|png|pdf)$/i.test(file.name))) return displayErrors(['Use JPG, PNG, or PDF attachments.']);
                const payload = new FormData(form);
                submitting = true;
                errors.classList.add('d-none');
                dialog.querySelectorAll('button').forEach(button => { button.disabled = true; });
                submit.textContent = 'Submitting…';
                try {
                    const response = await fetch(form.action, {method: 'POST', body: payload,
                        headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}});
                    const result = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        const validation = result.errors ? Object.values(result.errors).flat() : [];
                        displayErrors(validation.length ? validation : [response.status === 403
                            ? 'You can only submit RIS documents for your own approved requests.'
                            : response.status === 401 || response.status === 419 ? 'Your session expired. Sign in again before submitting.'
                            : 'Unable to submit RIS documents. Please try again.']);
                        return;
                    }
                    if (!result.message) throw new Error('Unexpected submission response');
                    dialog.close();
                    const success = document.getElementById('returnRefundSubmissionSuccess');
                    success.textContent = result.message;
                    success.classList.remove('d-none');
                    document.querySelectorAll('[data-report-summary]').forEach(counter => {
                        if (result.summary && result.summary[counter.dataset.reportSummary] !== undefined)
                            counter.textContent = Number(result.summary[counter.dataset.reportSummary]).toLocaleString();
                    });
                    table.ajax.reload(null, false);
                    success.focus();
                } catch (error) {
                    displayErrors(['Unable to confirm submission. Check your connection and try again, or refresh the report to see its latest status.']);
                } finally {
                    submitting = false;
                    dialog.querySelectorAll('button').forEach(button => { button.disabled = false; });
                    submit.textContent = 'Submit to warehouse';
                }
            });
        }
        start();
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize, {once: true});
    else initialize();
})();
