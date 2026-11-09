@extends('layouts.header')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/warehouse-pull-outs.css') }}">
@endsection

@section('content')
    <div class="pull-out-queue">
        <header class="queue-heading">
            <div>
                <p class="queue-eyebrow">Warehouse operations</p>
                <h1>Pull Out Replacement Requests</h1>
                <p class="queue-intro">Track replacement stock from request review through distributor receipt.</p>
            </div>
            <div class="queue-location"><i class="bi bi-building" aria-hidden="true"></i>{{ ucfirst($warehouse) }} warehouse</div>
        </header>

        <dl class="queue-stages" aria-label="Request counts by stage">
            @foreach(['pending' => 'Pending review', 'processing' => 'For processing', 'replacing' => 'Replacing', 'ad_confirmed' => 'AD confirmed'] as $key => $label)
                <div class="queue-stage stage-{{ $key }}">
                    <dt>{{ $label }}</dt><dd>{{ number_format($summary[$key]) }}</dd>
                </div>
            @endforeach
        </dl>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="status">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Dismiss message"></button></div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Dismiss message"></button></div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
        @endif

        <section class="queue-card" aria-labelledby="queueTitle">
            <header class="queue-card-head">
                <div><h2 id="queueTitle">Replacement queue</h2>
                    <p>Review pending quantities or release approved products under one DR.</p></div>
                <span class="queue-total">{{ number_format($summary['total']) }} {{ $summary['total'] == 1 ? 'request' : 'requests' }}</span>
            </header>
            <div class="queue-card-body">
                <div class="queue-table-shell">
                    <table id="pullOutRequestsTable" class="table queue-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Request</th>
                                <th scope="col">Distributor</th>
                                <th scope="col">Products</th>
                                <th scope="col">Progress</th>
                                <th scope="col">Actions</th>
                                <th>Search</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    @include('inventory_transfers.partials.warehouse_release_dialog')

    <script>
        document.addEventListener('DOMContentLoaded', async function () {

            if (!window.jQuery) return;

            async function loadDataTables() {
                if (window.jQuery.fn && window.jQuery.fn.DataTable) return true;
                var sources = [
                    "{{ asset('design/assets/libs/datatables.net/js/jquery.dataTables.min.js') }}",
                    "{{ asset('design/vendors/datatables.net/jquery.dataTables.js') }}",
                    'https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js'
                ];
                for (var i = 0; i < sources.length; i++) {
                    try {
                        await new Promise(function (resolve, reject) {
                            var script = document.createElement('script');
                            script.src = sources[i];
                            script.onload = resolve;
                            script.onerror = reject;
                            document.head.appendChild(script);
                        });
                        if (window.jQuery.fn && window.jQuery.fn.DataTable) return true;
                    } catch (error) {}
                }
                return false;
            }

            if (!await loadDataTables()) return;
            var table = window.jQuery('#pullOutRequestsTable');
            if (window.jQuery.fn.DataTable.isDataTable('#pullOutRequestsTable')) table.DataTable().destroy();
            var csrfToken = @json(csrf_token());
            var appUrl = @json(url('/'));
            var escapeHtml = function (value) {
                return String(value == null ? '' : value).replace(/[&<>"']/g, function (character) {
                    return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[character];
                });
            };
            const text = value => escapeHtml(releaseLabel(value));
            const units = value => Number(value || 0).toLocaleString();
            const statusClasses = {
                'Pending': 'pending', 'For Processing': 'processing', 'Replacing': 'replacing',
                'AD Confirmed': 'confirmed', 'Rejected': 'rejected'
            };
            function statusBadge(status, suffix = '') {
                return `<span class="queue-status ${statusClasses[status] || ''}">${text(status)}${suffix}</span>`;
            }
            function productSummary(products, total) {
                const visible = products.slice(0, 2).map(product =>
                    `<div class="queue-mini-product"><span>${text(product.name)}</span><b>${units(product.qty)} pcs</b></div>`
                ).join('');
                const more = products.length > 2 ? `+${products.length - 2} more · ` : '';
                return visible + `<small class="queue-subtext">${more}${units(total)} pcs requested</small>`;
            }
            function progressSummary(items) {
                const counts = new Map();
                items.forEach(item => {
                    const status = item.status || 'Pending';
                    const qty = status === 'Pending' || status === 'Rejected' ? item.requested_qty : item.approved_qty;
                    counts.set(status, (counts.get(status) || 0) + Number(qty));
                });
                return '<div class="queue-status-list">' + Array.from(counts, ([status, qty]) =>
                    statusBadge(status, ' · ' + units(qty) + ' pcs')).join('') + '</div>';
            }
            function bulkApprovalForm(row) {
                const pending = row.items.filter(item => item.status === 'Pending');
                if (!pending.length) return '';
                const fields = pending.map((item, index) => {
                    const capacity = Number(item.requested_qty);
                    return `<fieldset class="queue-approval-line" data-capacity="${capacity}">
                        <legend><span class="queue-product-heading"><span class="queue-product-index" aria-hidden="true">${index + 1}</span>
                            <span><strong>${text(item.name)}</strong><small>${text(item.sku)}</small></span>
                            <span class="queue-pending-count">${units(capacity)} pending</span></span></legend>
                        <input type="hidden" name="lines[${index}][line_id]" value="${Number(item.id)}">
                        <div class="queue-approval-fields">
                            <label><span class="queue-form-label">Approve quantity</span>
                                <span class="queue-quantity-input"><input type="number" name="lines[${index}][qty]" class="form-control js-approval-qty" min="0" max="${capacity}" step="1" inputmode="numeric" placeholder="0" aria-label="Approve quantity for ${text(item.name)}" aria-describedby="approval-balance-${Number(item.id)}"><span aria-hidden="true">pcs</span></span></label>
                            <div class="queue-balance" id="approval-balance-${Number(item.id)}"><span class="queue-form-label">Still pending</span>
                                <span class="queue-balance-value"><b class="js-pending-balance">${units(capacity)}</b> <small>pcs</small></span></div>
                            <label class="queue-remarks-field"><span class="queue-form-label js-remark-label">Remarks <small>optional</small></span>
                                <input name="lines[${index}][remarks]" class="form-control js-approval-remark" maxlength="1000" placeholder="Optional"></label>
                        </div>
                    </fieldset>`;
                }).join('');
                return `<form method="POST" action="${text(row.approval_url)}" class="queue-bulk-approval">
                    <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
                    <div class="queue-approval-toolbar"><div><h3>Review products <span class="queue-total">${pending.length}</span></h3>
                        <p>Enter full or partial quantities to approve.</p></div>
                        <div class="queue-fill-actions"><button type="button" class="btn btn-outline-primary btn-sm js-fill-pending">Fill all quantities</button>
                            <button type="button" class="btn btn-light btn-sm js-clear-pending" disabled>Clear</button></div></div>
                    <div class="queue-approval-products">${fields}</div>
                    <p class="queue-approval-help">Unapproved quantities stay pending on this request.</p>
                    <div class="queue-approval-toolbar queue-approval-footer"><div><span class="queue-form-label">Ready to approve</span><strong class="js-approval-total" aria-live="polite">0 products · 0 units</strong></div>
                        <button class="btn btn-success js-submit-approval" type="submit" disabled>Approve selected products <span aria-hidden="true">&rarr;</span></button></div>
                </form>`;
            }
            function syncBulkApproval(form) {
                let count = 0, total = 0, valid = true;
                form.querySelectorAll('.queue-approval-line').forEach(line => {
                    const input = line.querySelector('.js-approval-qty');
                    const capacity = Number(line.dataset.capacity), qty = Number(input.value);
                    const partial = qty > 0 && qty < capacity;
                    line.classList.toggle('is-selected', qty > 0 && input.validity.valid);
                    line.querySelector('.js-pending-balance').textContent = input.validity.valid ? units(capacity - qty) : '—';
                    const remark = line.querySelector('.js-approval-remark');
                    remark.required = partial;
                    remark.placeholder = partial ? 'Reason for partial approval' : 'Optional';
                    line.querySelector('.js-remark-label').innerHTML = partial ? 'Remarks <small class="queue-required">required</small>' : 'Remarks <small>optional</small>';
                    if (qty > 0) { count++; total += qty; }
                    valid = valid && input.validity.valid;
                });
                form.querySelector('.js-approval-total').textContent = `${count} ${count === 1 ? 'product' : 'products'} · ${units(total)} units`;
                form.querySelector('.js-submit-approval').disabled = !count || !valid;
                form.querySelector('.js-clear-pending').disabled = !Array.from(form.querySelectorAll('.js-approval-qty')).some(input => input.value !== '');
            }
            table.on('input', '.js-approval-qty', function () { syncBulkApproval(this.form); });
            table.on('click', '.js-fill-pending', function () {
                this.form.querySelectorAll('.js-approval-qty').forEach(input => { input.value = input.max; });
                syncBulkApproval(this.form);
            });
            table.on('click', '.js-clear-pending', function () {
                this.form.querySelectorAll('.js-approval-qty').forEach(input => { input.value = ''; });
                syncBulkApproval(this.form);
            });
            table.on('submit', '.queue-bulk-approval', function (event) {
                const button = this.querySelector('.js-submit-approval');
                if (button.disabled) event.preventDefault();
                button.disabled = true;
            });
            function productDetail(item) {
                const age = Object.entries(item.aging || {}).filter(([, days]) => days != null).map(([stage, days]) =>
                    `${text(stage)}: ${units(days)}d`).join(' · ');
                const status = item.status || 'Pending';
                const qty = status === 'Pending' || status === 'Rejected' ? item.requested_qty : item.approved_qty;
                let workflow = '';
                if (item.dr_number) workflow += `<p>DR <strong>${text(item.dr_number)}</strong></p>`;
                if (status === 'Pending') workflow += `<form method="POST" class="queue-rejection" action="${text(item.review_url)}">
                    <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
                    <label><span class="queue-form-label">Rejection remarks (optional)</span>
                        <input name="warehouse_remarks" class="form-control" maxlength="1000"></label>
                    <button class="btn btn-outline-danger btn-sm" type="submit" name="decision" value="Rejected">Reject pending quantity</button>
                    </form>`;
                if (status === 'For Processing') workflow += `<p>${units(item.approved_qty)} approved · Unit cost PHP ${Number(item.unit_cost || 0).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}</p>`;
                if (status === 'Replacing') workflow += '<p>Awaiting distributor receipt.</p>';
                if (status === 'AD Confirmed') workflow += `<p>${units(item.ad_confirmed_qty)} pcs received</p>`;
                if (item.remarks) workflow += `<p>${text(item.remarks)}</p>`;
                return `<details class="queue-product-detail"><summary>
                    <strong>${text(item.name)}</strong><span>${units(qty)} pcs</span>${statusBadge(status)}
                    </summary><div class="queue-portion"><p class="queue-subtext">${text(item.sku)} · ${units(item.requested_qty)} requested</p>
                    ${age ? `<p class="queue-subtext">Time in stage · ${age}</p>` : ''}${workflow}</div></details>`;
            }
            function requestDetails(row, panelId) {
                const metadata = [
                    ['Source areas', row.source_areas.join(', ')], ['RIS number', row.ris_number],
                    ['RIS date', row.ris_date], ['Warehouse', row.warehouse]
                ].map(([label, value]) => `<div><dt>${label}</dt><dd>${text(value || '—')}</dd></div>`).join('');
                const files = (row.attachments || []).map(file => {
                    const path = typeof file === 'string' ? file : file.path;
                    if (!path) return '';
                    const href = appUrl + '/' + releaseLabel(path).replace(/^\/+/, '');
                    const name = typeof file === 'object' && file.name ? file.name : path;
                    return `<a class="queue-file" href="${escapeHtml(href)}" target="_blank" rel="noopener noreferrer"><span class="queue-file-icon" aria-hidden="true">↗</span><span>${text(name)}</span><span class="visually-hidden"> (opens in a new tab)</span></a>`;
                }).join('');
                const totals = row.products.map(product => `<div><dt>${text(product.name)}</dt><dd>${units(product.open)} awaiting release · ${units(product.confirmed)} received</dd></div>`).join('');
                const approval = bulkApprovalForm(row);
                return `<section class="queue-details" id="${panelId}" aria-label="Request details">
                    <div class="queue-review-main">
                        ${approval}
                        <details class="queue-tracking" ${approval ? '' : 'open'}><summary>Product tracking and history</summary>
                            <div class="queue-tracking-body"><dl class="queue-meta">${totals}</dl>
                            ${row.items.map(productDetail).join('')}</div>
                        </details>
                    </div>
                    <aside class="queue-context" aria-label="Request information">
                        <h3>Request information</h3><dl class="queue-meta">${metadata}</dl>
                        <div class="queue-docs"><h4>Attachments</h4>${files || '<span class="queue-context-empty">No attachments</span>'}</div>
                        <div class="queue-docs"><h4>Request remarks</h4><p class="queue-request-remarks">${text(row.remarks || 'No remarks added.')}</p></div>
                    </aside></section>`;
            }
            const labels = ['Request', 'Distributor', 'Products', 'Progress', 'Actions'];
            const dataTable = table.DataTable({
                processing: true, serverSide: true, deferRender: true, searchDelay: 350, autoWidth: false,
                dom: "<'queue-tools'lf>rt<'queue-foot'ip>",
                pageLength: 25, lengthMenu: [25, 50, 100], order: [[6, 'desc']],
                ajax: { url: @json(route('warehouse-pull-outs.data')) },
                columns: [
                    { data: 'reference', orderData: [6], render: (ref, type, row) =>
                        `<strong class="queue-ref">${text(ref)}</strong><small class="queue-subtext">${text(row.date)}</small>` },
                    { data: 'ad_name', render: (name, type, row) =>
                        `<strong>${text(name)}</strong><small class="queue-subtext">${text(row.source_areas.join(', '))}</small>` },
                    { data: 'products', orderable: false, searchable: false, render: (products, type, row) => productSummary(products, row.requested_qty_total) },
                    { data: 'items', orderable: false, searchable: false, render: progressSummary },
                    { data: null, orderable: false, searchable: false, render: function (data, type, row) {
                        const ready = row.release_products.length > 0;
                        const pending = row.items.some(item => item.status === 'Pending');
                        return `<div class="queue-actions">${ready ? '<button type="button" class="btn btn-success btn-sm js-open-dr-release" aria-haspopup="dialog">Release products</button>' : ''}
                            <button type="button" class="btn ${pending && !ready ? 'btn-primary' : 'btn-outline-primary'} btn-sm js-review-request" aria-expanded="false">${pending ? 'Review request' : 'View details'}</button></div>`;
                    } },
                    { data: 'search_text', visible: false, orderable: false },
                    { data: 'sort_date', visible: false, searchable: false }
                ],
                createdRow: function (row) {
                    labels.forEach((label, i) => row.cells[i].setAttribute('data-label', label));
                },
                language: {
                    processing: 'Loading requests…', search: 'Search', searchPlaceholder: 'Find a request…',
                    lengthMenu: 'Show _MENU_', info: 'Showing _START_–_END_ of _TOTAL_ requests', infoEmpty: '0 requests',
                    emptyTable: 'No Pull Out requests for this warehouse.', zeroRecords: 'No requests match this search.'
                }
            });
            table.on('click', '.js-review-request', function () {
                const tr = this.closest('tr'), row = dataTable.row(tr);
                const opening = !row.child.isShown();
                if (opening) {
                    const id = 'queue-details-' + row.index();
                    if (!row.child()) row.child(requestDetails(row.data(), id), 'queue-detail-row');
                    row.child.show();
                    this.setAttribute('aria-controls', id);
                    this.dataset.closedLabel = this.textContent;
                } else {
                    row.child.hide();
                }
                tr.classList.toggle('is-expanded', opening);
                this.setAttribute('aria-expanded', String(opening));
                this.textContent = opening ? 'Close details' : this.dataset.closedLabel;
            });
            const dialog = document.getElementById('pullOutReleaseDialog');
            const form = document.getElementById('pullOutReleaseForm');
            const products = document.getElementById('drReleaseProducts');
            const submit = document.getElementById('drReleaseSubmit');
            const clearRelease = document.getElementById('drClear');
            const drChoice = document.getElementById('drReleaseChoice');
            const drOrder = document.getElementById('drReleaseOrder');
            const drNumber = document.getElementById('drReleaseNumber');
            const drHelp = document.getElementById('drNumberHelp');
            const drRetry = document.getElementById('drRetryReceipts');
            let drOptions = [], drLoading = false, drFailed = false, drRequest = null, drLoadVersion = 0, drFetch = null;
            // DataTables escapes the JSON labels for HTML. Decode one layer for textContent.
            function releaseLabel(value) {
                const entities = { '&amp;': '&', '&lt;': '<', '&gt;': '>', '&quot;': '"', '&#039;': "'" };
                return String(value == null ? '' : value).replace(/&(amp|lt|gt|quot|#039);/g, entity => entities[entity]);
            }
            function syncDeliveryReceipt() {
                const manual = drChoice.value === 'manual';
                const selected = drOptions.find(option => String(option.id) === drChoice.value);
                drOrder.value = selected ? selected.id : '';
                document.getElementById('drManualEntry').hidden = !manual;
                drNumber.required = manual;
                if (!manual) drNumber.value = selected ? selected.dr_number : '';
                if (!drLoading) {
                    drHelp.textContent = drFailed
                        ? 'Unable to load DPO receipts. Retry or enter a new DR number.'
                        : selected ? [selected.po_number, selected.delivery_date ? 'Delivery ' + selected.delivery_date : '', 'One DR for all selected products.'].filter(Boolean).join(' · ')
                        : drOptions.length ? 'Select a DR from this distributor’s For Delivery DPOs, or enter a new DR number.'
                        : 'No For Delivery DPO with a DR number was found for this distributor. Enter a new DR number.';
                }
                updateReleaseTotal();
            }
            async function loadDeliveryReceipts(request) {
                const version = ++drLoadVersion;
                drRequest = request;
                drLoading = true;
                drFailed = false;
                drOptions = [];
                drOrder.value = '';
                drNumber.value = '';
                drNumber.required = false;
                document.getElementById('drManualEntry').hidden = true;
                drChoice.replaceChildren(new Option('Loading delivery receipts…', ''));
                drChoice.disabled = true;
                drRetry.hidden = true;
                drHelp.textContent = 'Loading this distributor’s For Delivery DPOs…';
                updateReleaseTotal();
                if (drFetch) drFetch.abort();
                const controller = new AbortController();
                drFetch = controller;
                const timeout = window.setTimeout(() => controller.abort(), 15000);
                try {
                    const response = await fetch(request.delivery_receipts_url, {headers: {'Accept': 'application/json'}, credentials: 'same-origin', cache: 'no-store', signal: controller.signal});
                    if (!response.ok) throw new Error('Unable to load delivery receipts');
                    const data = await response.json();
                    if (!Array.isArray(data.delivery_receipts)) throw new Error('Invalid delivery receipts');
                    if (version !== drLoadVersion) return;
                    drOptions = data.delivery_receipts;
                } catch (error) {
                    if (version !== drLoadVersion) return;
                    drFailed = true;
                    drRetry.hidden = false;
                } finally {
                    window.clearTimeout(timeout);
                }
                if (version !== drLoadVersion) return;
                drLoading = false;
                drChoice.replaceChildren();
                if (drOptions.length) {
                    const placeholder = new Option('Select a DPO delivery receipt', '');
                    placeholder.disabled = true;
                    placeholder.selected = true;
                    drChoice.add(placeholder);
                    drOptions.forEach(option => drChoice.add(new Option([option.dr_number, option.po_number, option.delivery_date].filter(Boolean).join(' · '), option.id)));
                }
                drChoice.add(new Option('Enter a new DR number', 'manual'));
                drChoice.value = drOptions.length ? '' : 'manual';
                drChoice.disabled = false;
                syncDeliveryReceipt();
                if (document.activeElement === dialog || document.activeElement === dialog.querySelector('.dr-close')) {
                    (drChoice.value === 'manual' ? drNumber : drChoice).focus();
                }
            }
            function updateReleaseTotal() {
                let units = 0, selected = 0, valid = true, hasInput = false;
                products.querySelectorAll('.dr-line-qty').forEach(function (input) {
                    const qty = Number(input.value), available = Number(input.max);
                    const line = input.closest('.dr-product'), error = line.querySelector('.dr-quantity-error');
                    let message = '';
                    if (input.validity.badInput || !Number.isInteger(qty) || qty < 0) {
                        message = 'Enter a whole number of 0 or more.';
                    } else if (qty > available) {
                        message = 'Only ' + available.toLocaleString() + ' approved pcs are available to release.';
                    }
                    const lineValid = !message && input.validity.valid;
                    valid = valid && lineValid;
                    hasInput = hasInput || input.value !== '' || input.validity.badInput;
                    if (lineValid && qty > 0) { units += qty; selected++; }
                    error.textContent = message;
                    error.hidden = !message;
                    input.setAttribute('aria-invalid', String(!lineValid));
                    line.classList.toggle('has-error', !lineValid);
                    line.classList.toggle('is-selected', lineValid && qty > 0);
                    line.querySelector('.dr-remaining b').textContent = lineValid ? (available - qty).toLocaleString() : '—';
                });
                document.getElementById('drReleaseTotal').textContent = selected + (selected === 1 ? ' product' : ' products') + ' · ' + units.toLocaleString() + ' units';
                const receiptValid = !drLoading && (drChoice.value === 'manual'
                    ? drNumber.value.trim() !== '' && drNumber.validity.valid
                    : drOptions.some(option => String(option.id) === drChoice.value));
                submit.disabled = !valid || !selected || !receiptValid;
                clearRelease.disabled = !hasInput;
                return valid && selected > 0 && receiptValid;
            }
            table.on('click', '.js-open-dr-release', function () {
                const request = dataTable.row(this.closest('tr')).data();
                form.reset();
                delete form.dataset.submitting;
                form.action = request.release_url;
                document.getElementById('drReleaseReference').textContent = releaseLabel(request.reference);
                document.getElementById('drProductCount').textContent = request.release_products.length;
                products.textContent = '';
                request.release_products.forEach(function (product, index) {
                    const line = document.getElementById('drReleaseLine').content.cloneNode(true);
                    const id = line.querySelector('.dr-line-id'), qty = line.querySelector('.dr-line-qty');
                    id.name = 'lines[' + index + '][line_id]';
                    id.value = product.line_id;
                    qty.name = 'lines[' + index + '][qty]';
                    qty.max = product.available;
                    const name = releaseLabel(product.name);
                    qty.setAttribute('aria-label', 'Release quantity for ' + name);
                    const prefix = 'dr-product-' + index;
                    qty.setAttribute('aria-describedby', prefix + '-limit ' + prefix + '-balance ' + prefix + '-error');
                    line.querySelector('.dr-product').setAttribute('aria-labelledby', prefix + '-name');
                    line.querySelector('.dr-product-name strong').id = prefix + '-name';
                    line.querySelector('.dr-product-name strong').textContent = name;
                    const sku = line.querySelector('.dr-product-sku');
                    sku.textContent = releaseLabel(product.sku);
                    sku.hidden = !product.sku;
                    line.querySelector('.dr-approved-count').textContent = Number(product.available).toLocaleString();
                    const limit = line.querySelector('.dr-quantity-limit');
                    limit.id = prefix + '-limit';
                    limit.textContent = 'Maximum ' + Number(product.available).toLocaleString() + ' pcs';
                    line.querySelector('.dr-remaining').id = prefix + '-balance';
                    line.querySelector('.dr-quantity-error').id = prefix + '-error';
                    products.appendChild(line);
                });
                loadDeliveryReceipts(request);
                dialog.showModal();
                dialog.querySelector('.dr-dialog-body').scrollTop = 0;
            });
            products.addEventListener('input', updateReleaseTotal);
            drChoice.addEventListener('change', function () {
                drNumber.value = '';
                syncDeliveryReceipt();
                if (drChoice.value === 'manual') drNumber.focus();
            });
            drNumber.addEventListener('input', updateReleaseTotal);
            drRetry.addEventListener('click', function () { if (drRequest) loadDeliveryReceipts(drRequest); });
            dialog.addEventListener('close', function () { drLoadVersion++; if (drFetch) drFetch.abort(); });
            document.getElementById('drFillAll').addEventListener('click', function () {
                products.querySelectorAll('.dr-line-qty').forEach(input => { input.value = input.max; });
                updateReleaseTotal();
            });
            clearRelease.addEventListener('click', function () {
                products.querySelectorAll('.dr-line-qty').forEach(input => { input.value = ''; });
                updateReleaseTotal();
            });
            dialog.querySelectorAll('.js-close-dr').forEach(function (button) {
                button.addEventListener('click', function () { dialog.close(); });
            });
            form.addEventListener('submit', function (event) {
                if (form.dataset.submitting === 'true' || !updateReleaseTotal()) {
                    event.preventDefault();
                    return;
                }
                form.dataset.submitting = 'true';
                submit.disabled = true;
            });
        });
    </script>
@endsection
