@extends('layouts.header')

@section('css')
<style>
    .adpo-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 18px; }
    .adpo-title { margin: 0; color: #101828; font-size: 25px; font-weight: 900; line-height: 1.2; }
    .adpo-subtitle { margin: 5px 0 0; color: #667085; font-size: 13px; }
    .adpo-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .adpo-summary { display: grid; grid-template-columns: repeat(4, minmax(180px, 1fr)); gap: 12px; margin-bottom: 18px; }
    .adpo-tile { position: relative; display: grid; grid-template-columns: 44px minmax(0, 1fr); align-items: center; gap: 13px; min-height: 92px; background: #fff; border: 1px solid #e6e9ef; border-radius: 8px; padding: 16px; overflow: hidden; box-shadow: 0 10px 24px rgba(15, 23, 42, .04); }
    .adpo-tile::before { content: ""; position: absolute; inset: 0 auto 0 0; width: 4px; background: #d0d5dd; }
    .adpo-tile.is-pending::before, .adpo-tile.is-delivery::before { background: #c2410c; }
    .adpo-tile.is-completed::before { background: #027a48; }
    .adpo-tile.is-amount::before, .adpo-tile.is-verification::before { background: #1d4ed8; }
    .adpo-tile-icon { display: inline-flex; align-items: center; justify-content: center; width: 44px; height: 44px; border-radius: 8px; background: #f8fafc; color: #475467; font-size: 20px; }
    .adpo-tile-icon.pending { background: #fff7ed; color: #c2410c; }
    .adpo-tile-icon.completed { background: #ecfdf3; color: #027a48; }
    .adpo-tile-icon.amount { background: #eff6ff; color: #1d4ed8; }
    .adpo-tile span { display: block; color: #667085; font-size: 11px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
    .adpo-tile strong { display: block; margin-top: 4px; color: #101828; font-size: 22px; font-weight: 900; line-height: 1.15; overflow-wrap: anywhere; }
    .adpo-panel { background: #fff; border: 1px solid #e6e9ef; border-radius: 8px; overflow: hidden; box-shadow: 0 12px 30px rgba(15, 23, 42, .05); }
    .adpo-panel-head { display: grid; grid-template-columns: 1fr; gap: 14px; padding: 16px; border-bottom: 1px solid #edf0f5; background: #fcfcfd; }
    .adpo-panel-head > div:first-child { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; }
    .adpo-panel-title { margin: 0; color: #101828; font-size: 15px; font-weight: 800; }
    .adpo-panel-subtitle { margin: 3px 0 0; color: #667085; font-size: 12px; }
    .adpo-filter-area { display: grid; gap: 12px; min-width: 0; }
    .adpo-quick-filters { display: flex; align-items: center; justify-content: flex-start; gap: 7px; flex-wrap: wrap; }
    .adpo-quick-filter { display: inline-flex; align-items: center; gap: 7px; min-height: 34px; padding: 0 12px; border: 1px solid #d9dee8; border-radius: 999px; background: #fff; color: #475467; font-size: 12px; font-weight: 800; text-decoration: none; white-space: nowrap; transition: background .16s ease, border-color .16s ease, color .16s ease; }
    .adpo-quick-filter:hover { border-color: #cbd5e1; color: #101828; text-decoration: none; background: #f8fafc; }
    .adpo-quick-filter.is-active { border-color: #dc2626; background: #fef2f2; color: #b91c1c; box-shadow: inset 0 0 0 1px rgba(220, 38, 38, .08); }
    .adpo-quick-filter .count { min-width: 24px; padding: 2px 8px; border-radius: 999px; background: #eef2f7; color: #475467; font-size: 11px; text-align: center; }
    .adpo-quick-filter.is-active .count { background: #fee2e2; color: #991b1b; }
    .adpo-filters { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; width: 100%; }
    .adpo-filters .form-select, .adpo-filters .form-control { min-height: 36px; }
    .adpo-filters > .form-select { flex: 1 1 145px; }
    .adpo-search { position: relative; flex: 2 1 280px; min-width: 220px; }
    .adpo-search i { position: absolute; left: 11px; top: 50%; color: #98a2b3; transform: translateY(-50%); pointer-events: none; }
    .adpo-search .form-control { padding-left: 34px; }
    .adpo-date-range { display: grid; grid-template-columns: repeat(2, minmax(120px, 1fr)); gap: 8px; flex: 1.4 1 260px; }
    .adpo-filter-actions { display: inline-flex; align-items: center; justify-content: flex-end; gap: 8px; flex: 0 0 auto; margin-left: auto; }
    .adpo-filter-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-height: 36px; font-weight: 800; white-space: nowrap; }
    .adpo-table { margin: 0; }
    .adpo-table thead th { background: #f8fafc; border-bottom: 1px solid #e6e9ef; color: #667085; font-size: 11px; font-weight: 800; letter-spacing: .04em; padding: 13px 16px; text-transform: uppercase; white-space: nowrap; }
    .adpo-table tbody td { border-bottom: 1px solid #f1f3f6; padding: 15px 16px; vertical-align: middle; color: #344054; }
    .adpo-table tbody tr:last-child td { border-bottom: 0; }
    .adpo-table tbody tr:hover { background: #fafafa; }
    .po-number { color: #101828; font-weight: 800; white-space: nowrap; }
    .po-date { color: #667085; font-size: 12px; white-space: nowrap; }
    .business-name { color: #101828; font-weight: 800; }
    .ad-name { display: block; margin-top: 2px; color: #475467; font-size: 12px; font-weight: 700; }
    .territory-text { display: block; max-width: 330px; overflow: hidden; color: #667085; font-size: 12px; text-overflow: ellipsis; white-space: nowrap; }
    .item-count { color: #344054; font-weight: 800; }
    .item-count small { display: block; margin-top: 2px; color: #667085; font-weight: 500; }
    .amount-text { color: #101828; font-weight: 800; white-space: nowrap; }
    .status-pill { display: inline-flex; align-items: center; gap: 6px; border-radius: 999px; padding: 6px 10px; font-size: 11px; font-weight: 800; white-space: nowrap; }
    .status-pill::before { content: ""; width: 7px; height: 7px; border-radius: 999px; background: currentColor; }
    .status-pending { background: #fff7ed; color: #c2410c; }
    .status-for-delivery { background: #eff6ff; color: #1d4ed8; }
    .status-partial-received { background: #fffbeb; color: #b45309; }
    .status-for-verification { background: #f5f3ff; color: #6d28d9; }
    .status-completed { background: #ecfdf3; color: #027a48; }
    .status-cancelled { background: #fef2f2; color: #b42318; }
    .task-board { display: grid; overflow: auto; grid-template-columns: repeat(6, minmax(300px, 1fr)); gap: 14px; padding: 14px; background: #f8fafc; align-items: start; }
    .task-column { min-width: 0; border: 1px solid #e6e9ef; border-radius: 8px; background: #fff; overflow: hidden; box-shadow: 0 10px 24px rgba(15, 23, 42, .04); }
    .task-column-head { position: sticky; top: 0; z-index: 1; display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 13px 14px; border-bottom: 1px solid #edf0f5; background: #fcfcfd; }
    .task-column-title { display: flex; align-items: center; gap: 8px; min-width: 0; margin: 0; color: #101828; font-size: 13px; font-weight: 900; }
    .task-column-title span:last-child { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .task-dot { width: 9px; height: 9px; flex: 0 0 9px; border-radius: 50%; background: #c2410c; box-shadow: 0 0 0 4px rgba(194, 65, 12, .10); }
    .task-dot.delivery { background: #1d4ed8; box-shadow: 0 0 0 4px rgba(29, 78, 216, .10); }
    .task-dot.partial { background: #b45309; box-shadow: 0 0 0 4px rgba(180, 83, 9, .12); }
    .task-dot.verification { background: #6d28d9; box-shadow: 0 0 0 4px rgba(109, 40, 217, .10); }
    .task-dot.completed { background: #027a48; box-shadow: 0 0 0 4px rgba(2, 122, 72, .10); }
    .task-dot.cancelled { background: #b42318; box-shadow: 0 0 0 4px rgba(180, 35, 24, .10); }
    .task-count { min-width: 28px; padding: 4px 9px; border-radius: 999px; background: #eef2f7; color: #475467; font-size: 12px; font-weight: 900; text-align: center; }
    .task-list { display: grid; align-content: start; gap: 12px; min-height: 180px; max-height: calc(100vh - 340px); padding: 12px; overflow-y: auto; }
    .task-card { position: relative; border: 1px solid #e6e9ef; border-radius: 8px; padding: 13px; background: #fff; box-shadow: 0 8px 18px rgba(15, 23, 42, .04); transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease; }
    .task-card::before { content: ""; position: absolute; inset: 0 auto 0 0; width: 4px; border-radius: 8px 0 0 8px; background: #c2410c; }
    .task-card.task-pending::before { background: #c2410c; }
    .task-card.task-for-delivery::before { background: #1d4ed8; }
    .task-card.task-partial-received::before { background: #b45309; }
    .task-card.task-for-verification::before { background: #6d28d9; }
    .task-card.task-completed::before { background: #027a48; }
    .task-card.task-cancelled::before { background: #b42318; }
    .task-card:hover { border-color: #cbd5e1; box-shadow: 0 14px 26px rgba(15, 23, 42, .08); transform: translateY(-1px); }
    .task-card-top { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: flex-start; gap: 10px; padding-left: 4px; }
    .task-id { display: flex; align-items: center; gap: 8px; min-width: 0; }
    .task-po { color: #101828; font-weight: 900; font-size: 13px; line-height: 1.25; overflow-wrap: anywhere; }
    .task-number { color: #98a2b3; font-size: 11px; font-weight: 800; white-space: nowrap; }
    .task-date { display: inline-flex; align-items: center; justify-content: center; min-width: 52px; padding: 5px 8px; border-radius: 999px; background: #f8fafc; color: #667085; font-size: 11px; font-weight: 800; white-space: nowrap; }
    .task-business { margin-top: 10px; padding-left: 4px; color: #101828; font-weight: 900; font-size: 14px; line-height: 1.35; overflow-wrap: anywhere; }
    .task-ad { display: flex; align-items: center; gap: 6px; margin-top: 6px; padding-left: 4px; color: #475467; font-size: 12px; font-weight: 700; min-width: 0; }
    .task-ad span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .task-address { display: grid; grid-template-columns: 18px minmax(0, 1fr); gap: 7px; margin-top: 10px; padding: 9px 10px; border: 1px solid #edf0f5; border-radius: 8px; background: #fcfcfd; color: #667085; font-size: 12px; line-height: 1.4; }
    .task-address i { color: #98a2b3; line-height: 1.4; }
    .task-address span { display: -webkit-box; overflow: hidden; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
    .task-meta { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; margin-top: 10px; }
    .task-metric { min-width: 0; padding: 9px 10px; border: 1px solid #edf0f5; border-radius: 8px; background: #fff; }
    .task-metric span { display: flex; align-items: center; gap: 5px; color: #667085; font-size: 10px; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; white-space: nowrap; }
    .task-metric strong { display: block; margin-top: 4px; color: #101828; font-size: 13px; font-weight: 900; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .task-actions { display: grid; grid-template-columns: 1fr auto; align-items: center; gap: 8px; margin-top: 12px; padding-top: 11px; border-top: 1px solid #f1f3f6; }
    .task-actions .btn { min-height: 34px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 0 12px; font-weight: 800; }
    .task-payment { color: #667085; font-size: 11px; font-weight: 800; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .task-empty { min-height: 120px; display: flex; align-items: center; justify-content: center; padding: 24px 10px; border: 1px dashed #d0d5dd; border-radius: 8px; background: #fcfcfd; color: #98a2b3; font-size: 12px; text-align: center; }
    .empty-state { padding: 54px 24px; text-align: center; }
    .empty-state i { display: inline-flex; align-items: center; justify-content: center; width: 54px; height: 54px; margin-bottom: 12px; border-radius: 8px; background: #f8fafc; color: #667085; font-size: 26px; }
    .empty-state h6 { margin: 0 0 5px; color: #101828; font-weight: 800; }
    .empty-state p { margin: 0; color: #667085; font-size: 13px; }
    .status-action-group { display: inline-flex; align-items: center; justify-content: flex-end; gap: 6px; }
    .status-modal-summary { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-bottom: 14px; }
    .status-modal-item { padding: 10px 12px; border: 1px solid #edf0f5; border-radius: 8px; background: #f8fafc; }
    .status-modal-item span { display: block; color: #667085; font-size: 11px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
    .status-modal-item strong { display: block; margin-top: 4px; color: #101828; font-size: 13px; overflow-wrap: anywhere; }
    .verification-note { display: none; }
    .verification-note.is-visible { display: block; }
    .partial-received-items { display: none; margin-bottom: 14px; }
    .partial-received-items.is-visible { display: block; }
    .partial-items-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 8px; }
    .partial-received-card { border: 1px solid #f3d18b; border-radius: 8px; padding: 12px; background: #fffbeb; }
    .partial-received-copy { margin: 0 0 10px; color: #92400e; font-size: 12px; font-weight: 700; }
    .partial-confirm-callout { display: grid; grid-template-columns: 34px minmax(0, 1fr); gap: 10px; margin-bottom: 10px; padding: 10px 12px; border: 1px solid #bfdbfe; border-radius: 8px; background: #eff6ff; color: #1e40af; }
    .partial-confirm-callout i { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 8px; background: #dbeafe; color: #1d4ed8; font-size: 16px; }
    .partial-confirm-callout strong { display: block; color: #1e3a8a; font-size: 13px; font-weight: 900; }
    .partial-confirm-callout span { display: block; margin-top: 2px; font-size: 12px; font-weight: 700; line-height: 1.35; }
    .partial-summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; margin-bottom: 10px; }
    .partial-summary-item { padding: 9px 10px; border: 1px solid #fde68a; border-radius: 8px; background: #fff; }
    .partial-summary-item span { display: block; color: #92400e; font-size: 10px; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; }
    .partial-summary-item strong { display: block; margin-top: 3px; color: #101828; font-size: 15px; font-weight: 900; }
    .partial-items-list { display: grid; gap: 8px; max-height: 260px; overflow-y: auto; padding-right: 2px; }
    .partial-item-row { display: grid; grid-template-columns: minmax(0, 1fr) 118px 145px 145px; align-items: center; gap: 10px; padding: 10px 12px; border: 1px solid #edf0f5; border-radius: 8px; background: #fff; }
    .partial-item-row.is-qty-only { grid-template-columns: minmax(0, 1fr) 128px; }
    .partial-item-name { color: #101828; font-size: 13px; font-weight: 800; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .partial-item-meta { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 5px; color: #667085; font-size: 11px; font-weight: 700; }
    .partial-item-meta span { display: inline-flex; align-items: center; border-radius: 999px; padding: 2px 7px; background: #f8fafc; }
    .partial-item-row .form-control { text-align: center; font-weight: 800; }
    .partial-qty-label { display: block; margin-bottom: 4px; color: #667085; font-size: 10px; font-weight: 900; letter-spacing: .04em; text-align: center; text-transform: uppercase; }
    .partial-items-empty { padding: 14px; border: 1px dashed #d0d5dd; border-radius: 8px; color: #667085; font-size: 12px; text-align: center; }
    .partial-doc-input { font-weight: 700; }
    .partial-readonly-doc { min-height: 31px; display: flex; align-items: center; justify-content: center; padding: 5px 8px; border: 1px solid #edf0f5; border-radius: 6px; background: #f8fafc; color: #344054; font-size: 12px; font-weight: 800; text-align: center; overflow-wrap: anywhere; }
    @media (max-width: 992px) {
        .adpo-head { align-items: stretch; flex-direction: column; }
        .adpo-panel-head > div:first-child { align-items: flex-start; flex-direction: column; }
        .adpo-summary { grid-template-columns: repeat(2, minmax(160px, 1fr)); }
        .adpo-search { flex-basis: 100%; }
        .adpo-filter-actions { justify-content: stretch; }
        .adpo-filter-actions .btn { flex: 1; }
        .adpo-table-wrap { overflow-x: auto; }
        .adpo-table { min-width: 980px; }
        .task-board { grid-template-columns: repeat(2, minmax(240px, 1fr)); }
        .task-list { max-height: none; }
    }
    @media (max-width: 640px) {
        .adpo-date-range, .adpo-filter-actions { grid-template-columns: 1fr; }
        .adpo-filter-actions { display: grid; }
        .adpo-filter-actions { flex: 1 1 100%; margin-left: 0; }
        .adpo-filters .form-select, .adpo-filters .form-control { width: 100%; min-width: 0; }
        .adpo-filters > .form-select, .adpo-date-range { flex: 1 1 100%; }
        .adpo-actions .btn, .adpo-filters .btn { width: 100%; }
        .task-board { grid-template-columns: 1fr; }
        .task-card-top, .task-meta { align-items: flex-start; grid-template-columns: 1fr; }
        .task-date { justify-self: start; }
        .task-actions { grid-template-columns: 1fr 38px; }
        .partial-summary, .partial-item-row { grid-template-columns: 1fr; }
    }
    @media (max-width: 576px) { .adpo-summary { grid-template-columns: 1fr; } }
    @media (max-width: 576px) { .status-modal-summary { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
@php
    $hasFilters = request()->filled('search')
        || request()->filled('status')
        || request()->filled('shipping_type')
        || request()->filled('payment_method')
        || request()->filled('date_from')
        || request()->filled('date_to');
    $pageTitle = $pageTitle ?? 'Distributor Purchase Orders';
    $pageSubtitle = $pageSubtitle ?? 'Create, review, and track ADPO submissions in a separate module.';
    $panelTitle = $panelTitle ?? 'Purchase Order History';
    $showCreateButton = ($showCreateButton ?? true) && !filled(auth()->user()->warehouse);
    $clearRoute = $clearRoute ?? route('ad-purchase-orders.index');
    $exportRoute = $exportRoute ?? route('ad-purchase-orders.export', request()->query());
    $viewRouteName = $viewRouteName ?? 'ad-purchase-orders.show';
    $statusOptions = auth()->user()->role === 'Area Distributor'
        ? ['Pending', 'Partial Received', 'Completed', 'Cancelled']
        : ['Pending', 'For Delivery', 'SO Created', 'Partial Received', 'Completed', 'Cancelled'];
    $shippingOptions = [
        'delivered' => 'Delivered',
        'pickup_lubao' => 'Pick Up Lubao',
        'pickup_guinobatan' => 'Pick Up Guinobatan',
    ];
    $paymentOptions = [
        'cash' => 'Cash',
        'gcash' => 'GCash',
        'bank_transfer' => 'Bank Transfer',
        'voucher' => 'Voucher',
        'credit' => 'Credit',
    ];
    $favoriteSummary = $favoriteSummary ?? $summary;
    $statusCounts = [
        '' => $favoriteSummary['total'] ?? 0,
        'Pending' => $favoriteSummary['pending'] ?? 0,
        'For Delivery' => $favoriteSummary['for_delivery'] ?? 0,
        'SO Created' => $favoriteSummary['so_created'] ?? 0,
        'Partial Received' => $favoriteSummary['partial_received'] ?? 0,
        'Completed' => $favoriteSummary['completed'] ?? 0,
    ];
    $quickStatusOptions = [
        '' => 'All',
        'Pending' => 'Pending',
        'For Delivery' => 'Delivery',
        'SO Created' => 'Verification',
        'Partial Received' => 'Partial',
        'Completed' => 'Completed',
    ];
    $editableAdpoStatuses = ['Pending', 'For Delivery', 'SO Created', 'Partial Received'];
    $canUpdateAdpoStatus = auth()->user()->role === 'Area Distributor';
    $isWarehouseTaskView = auth()->user()->role === 'Admin' && filled(auth()->user()->warehouse);
    $taskColumns = [
        'Pending' => ['label' => 'Pending', 'dot' => 'pending'],
        'For Delivery' => ['label' => 'For Delivery', 'dot' => 'delivery'],
        'SO Created' => ['label' => 'SO Created', 'dot' => 'verification'],
        'Partial Received' => ['label' => 'Partial Received', 'dot' => 'partial'],
        'Completed' => ['label' => 'Completed', 'dot' => 'completed'],
        'Cancelled' => ['label' => 'Cancelled', 'dot' => 'cancelled'],
    ];
    $adpoOrderItemsJson = $orders->mapWithKeys(function ($order) {
        if (auth()->user()->role === 'Area Distributor' && $order->status === 'Partial Received' && $order->partialReceipts->isNotEmpty()) {
            return [
                strval($order->id) => $order->partialReceipts->filter(function ($receipt) {
                    return (int) $receipt->received_qty > (int) $receipt->confirmed_qty;
                })->map(function ($receipt) use ($order) {
                    $item = $receipt->item;
                    $confirmedBeforeQty = $item
                        ? $item->partialReceipts->sum(function ($itemReceipt) {
                            return (int) $itemReceipt->confirmed_qty;
                        })
                        : 0;

                    return [
                        'receipt_id' => $receipt->id,
                        'id' => optional($item)->id,
                        'name' => optional($item)->product_name ?: 'Product',
                        'qty' => (int) optional($item)->qty,
                        'confirmed_before_qty' => $confirmedBeforeQty,
                        'received_qty' => max((int) $receipt->received_qty - (int) $receipt->confirmed_qty, 0),
                        'delivery_date' => optional($receipt->delivery_date)->format('Y-m-d'),
                        'dr_number' => $receipt->dr_number ?: $order->dr_number,
                    ];
                })->values(),
            ];
        }

        return [
            strval($order->id) => $order->items->filter(function ($item) {
                return (int) ($item->partial_received_qty ?? 0) !== (int) $item->qty;
            })->map(function ($item) use ($order) {
                $receivedQty = min(max((int) ($item->partial_received_qty ?? 0), 0), (int) $item->qty);

                return [
                    'id' => $item->id,
                    'name' => $item->product_name,
                    'qty' => (int) $item->qty,
                    'confirmed_before_qty' => $receivedQty,
                    'received_qty' => 0,
                    'receive_mode' => 'increment',
                    'delivery_date' => optional($item->partial_delivery_date)->format('Y-m-d'),
                    'dr_number' => $item->partial_dr_number ?: $order->dr_number,
                ];
            })->values(),
        ];
    });
@endphp
    <div class="adpo-head">
        <div>
            <h4 class="adpo-title">{{ $pageTitle }}</h4>
            <p class="adpo-subtitle">{{ $pageSubtitle }}</p>
        </div>
        @if($showCreateButton)
            <div class="adpo-actions">
                <a href="{{ route('ad-purchase-orders.create') }}" class="btn btn-danger">
                    <i class="ti ti-circle-plus"></i> New ADPO
                </a>
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="adpo-summary">
        <div class="adpo-tile is-total">
            <div class="adpo-tile-icon"><i class="ti ti-receipt"></i></div>
            <div><span>Total Orders</span><strong>{{ number_format($summary['total']) }}</strong></div>
        </div>
        @if($isWarehouseTaskView)
            <div class="adpo-tile is-delivery">
                <div class="adpo-tile-icon pending"><i class="ti ti-truck-delivery"></i></div>
                <div><span>For Delivery</span><strong>{{ number_format($summary['for_delivery'] ?? 0) }}</strong></div>
            </div>
            <div class="adpo-tile is-verification">
                <div class="adpo-tile-icon amount"><i class="ti ti-shield-check"></i></div>
                <div><span>SO Created</span><strong>{{ number_format($summary['so_created'] ?? 0) }}</strong></div>
            </div>
            <div class="adpo-tile is-completed">
                <div class="adpo-tile-icon completed"><i class="ti ti-circle-check"></i></div>
                <div><span>Completed</span><strong>{{ number_format($summary['completed']) }}</strong></div>
            </div>
        @else
            <div class="adpo-tile is-pending">
                <div class="adpo-tile-icon pending"><i class="ti ti-hourglass"></i></div>
                <div><span>Pending</span><strong>{{ number_format($summary['pending']) }}</strong></div>
            </div>
            <div class="adpo-tile is-completed">
                <div class="adpo-tile-icon completed"><i class="ti ti-circle-check"></i></div>
                <div><span>Completed</span><strong>{{ number_format($summary['completed']) }}</strong></div>
            </div>
            <div class="adpo-tile is-amount">
                <div class="adpo-tile-icon amount"><i class="ti ti-cash"></i></div>
                <div><span>Total Amount</span><strong>PHP {{ number_format($summary['amount'], 2) }}</strong></div>
            </div>
        @endif
    </div>

    <div class="adpo-panel">
        <div class="adpo-panel-head">
            <div>
                <h6 class="adpo-panel-title">{{ $panelTitle }}</h6>
                <p class="adpo-panel-subtitle">{{ number_format($orders->count()) }} order(s) found</p>
            </div>
            <div class="adpo-filter-area">
                <div class="adpo-quick-filters">
                    @foreach($quickStatusOptions as $quickStatus => $quickLabel)
                        @php
                            $quickQuery = request()->except(['status', 'page']);
                            if ($quickStatus !== '') {
                                $quickQuery['status'] = $quickStatus;
                            }
                            $isQuickActive = request('status', '') === $quickStatus;
                        @endphp
                        <a href="{{ url()->current() }}{{ count($quickQuery) ? '?' . http_build_query($quickQuery) : '' }}" class="adpo-quick-filter {{ $isQuickActive ? 'is-active' : '' }}">
                            <span>{{ $quickLabel }}</span>
                            <span class="count">{{ number_format($statusCounts[$quickStatus] ?? 0) }}</span>
                        </a>
                    @endforeach
                </div>
                <form method="GET" class="adpo-filters">
                    <div class="adpo-search">
                        <i class="ti ti-search"></i>
                        <input type="search" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search PO, business, territory">
                    </div>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        @foreach($statusOptions as $status)
                            <option value="{{ $status }}" @if(request('status') === $status) selected @endif>{{ $status }}</option>
                        @endforeach
                    </select>
                    <select name="shipping_type" class="form-select form-select-sm">
                        <option value="">All Shipping</option>
                        @foreach($shippingOptions as $value => $label)
                            <option value="{{ $value }}" @if(request('shipping_type') === $value) selected @endif>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="payment_method" class="form-select form-select-sm">
                        <option value="">All Payment</option>
                        @foreach($paymentOptions as $value => $label)
                            <option value="{{ $value }}" @if(request('payment_method') === $value) selected @endif>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="adpo-date-range">
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm" title="Date from">
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm" title="Date to">
                    </div>
                    <div class="adpo-filter-actions">
                        <button class="btn btn-sm btn-outline-secondary adpo-filter-btn" type="submit">
                            <i class="ti ti-filter"></i> Filter
                        </button>
                        <a href="{{ $exportRoute }}" class="btn btn-sm btn-outline-success adpo-filter-btn">
                            <i class="ti ti-file-spreadsheet"></i> Excel
                        </a>
                        @if($hasFilters)
                            <a href="{{ $clearRoute }}" class="btn btn-sm btn-outline-secondary adpo-filter-btn">
                                <i class="ti ti-circle-x"></i> Clear
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        @if($isWarehouseTaskView)
            <div class="task-board">
                @foreach($taskColumns as $columnStatus => $column)
                    @php
                        $columnOrders = $orders->where('status', $columnStatus);
                    @endphp
                    <section class="task-column">
                        <div class="task-column-head">
                            <h6 class="task-column-title">
                                <span class="task-dot {{ $column['dot'] }}"></span><span>{{ $column['label'] }}</span>
                            </h6>
                            <span class="task-count">{{ number_format($columnOrders->count()) }}</span>
                        </div>
                        <div class="task-list">
                            @forelse($columnOrders as $order)
                                @php
                                    $submittedAt = $order->submitted_at ?: $order->created_at;
                                    $taskStatusClass = 'task-' . strtolower(str_replace(' ', '-', $order->status));
                                @endphp
                                <article class="task-card {{ $taskStatusClass }}">
                                    <div>
                                        <div class="task-card-top">
                                            <div class="task-id">
                                                <div class="task-po">{{ $order->po_number }}</div>
                                                <span class="task-number">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                                            </div>
                                            <div class="task-date">{{ optional($submittedAt)->format('M d') }}</div>
                                        </div>
                                        <div class="task-business">{{ $order->business_name ?: 'Area Distributor' }}</div>
                                        <div class="task-ad">
                                            <i class="ti ti-id-badge"></i>
                                            <span>{{ optional($order->ad)->name ?: optional($order->ad)->business_name ?: 'N/A' }}</span>
                                        </div>
                                        <div class="task-address" title="{{ $order->delivery_address ?: $order->authorized_territory ?: 'No delivery address' }}">
                                            <i class="ti ti-map-pin"></i>
                                            <span>{{ $order->delivery_address ?: $order->authorized_territory ?: 'No delivery address' }}</span>
                                        </div>
                                        <div class="task-meta">
                                            <div class="task-metric">
                                                <span><i class="ti ti-box-seam"></i> Quantity</span>
                                                <strong>{{ number_format($order->total_qty) }} item(s)</strong>
                                            </div>
                                            <div class="task-metric">
                                                <span><i class="ti ti-cash"></i> Amount</span>
                                                <strong>PHP {{ number_format($order->total_amount, 2) }}</strong>
                                            </div>
                                        </div>
                                        <div class="task-actions">
                                            <div class="task-payment">
                                                <i class="ti ti-credit-card"></i>
                                                {{ strtoupper(ucwords(str_replace('_', ' ', $order->payment_method))) }}
                                                @if($order->bank_name)
                                                    <div class="small text-muted">{{ strtoupper($order->bank_name) }}</div>
                                                @endif
                                            </div>
                                            <a href="{{ route($viewRouteName, $order->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="ti ti-eye"></i> View
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            @empty
                                <div class="task-empty">No {{ strtolower($column['label']) }} tasks.</div>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        @else
            <div class="adpo-table-wrap">
                <table class="table table-hover adpo-table">
                    <thead>
                        <tr>
                            <th>PO Number</th>
                            <th>Business</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            @php
                                $statusClass = 'status-' . strtolower(str_replace(' ', '-', $order->status));
                                $submittedAt = $order->submitted_at ?: $order->created_at;
                                $canAutoCompleteReceiving = $order->items->isNotEmpty()
                                    && $order->items->every(function ($item) {
                                        return $item->partialReceipts->sum('confirmed_qty') >= (int) $item->qty;
                                    })
                                    && $order->partialReceipts->every(function ($receipt) {
                                        return (int) $receipt->confirmed_qty >= (int) $receipt->received_qty;
                                    });
                            @endphp
                            <tr>
                                <td>
                                    <div class="po-number">{{ $order->po_number }}</div>
                                    <small class="text-muted">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</small>
                                </td>
                                <td>
                                    <div class="business-name">{{ $order->business_name ?: 'Area Distributor' }}</div>
                                    <span class="ad-name">AD: {{ optional($order->ad)->name ?: optional($order->ad)->business_name ?: 'N/A' }}</span>
                                    <span class="territory-text" title="{{ $order->authorized_territory ?: 'No territory set' }}">
                                        {{ $order->authorized_territory ?: 'No territory set' }}
                                    </span>
                                    @if(isset($showCreateButton) && !$showCreateButton)
                                        <span class="territory-text" title="{{ $order->delivery_address ?: 'No delivery address' }}">
                                            {{ $order->delivery_address ?: 'No delivery address' }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ optional($submittedAt)->format('M d, Y') }}</div>
                                    <div class="po-date">{{ optional($submittedAt)->format('h:i A') }}</div>
                                </td>
                                <td>
                                    <div class="item-count">{{ number_format($order->total_qty) }} qty</div>
                                    <small>{{ number_format($order->items->count()) }} line(s)</small>
                                </td>
                                <td class="amount-text">PHP {{ number_format($order->total_amount, 2) }}</td>
                                <td><span class="status-pill {{ $statusClass }}">{{ $order->status }}</span></td>
                                <td class="text-end">
                                    <div class="status-action-group">
                                        @if($canUpdateAdpoStatus && in_array($order->status, $editableAdpoStatuses))
                                            <button type="button"
                                                class="btn btn-sm btn-outline-secondary js-status-modal"
                                                data-bs-toggle="modal"
                                                data-bs-target="#adpoStatusModal"
                                                data-action="{{ route('ad-purchase-orders.updateStatus', $order->id) }}"
                                                data-order-id="{{ $order->id }}"
                                                data-po="{{ $order->po_number }}"
                                                data-business="{{ $order->business_name ?: 'Area Distributor' }}"
                                                data-current-status="{{ $order->status }}"
                                                data-can-complete-receiving="{{ $canAutoCompleteReceiving ? '1' : '0' }}"
                                                data-payment-method="{{ $order->payment_method }}"
                                                data-reference-no="{{ $order->reference_no }}"
                                                data-has-proof="{{ $order->proof_of_payment ? '1' : '0' }}"
                                                data-proof-url="{{ $order->proof_of_payment ? asset($order->proof_of_payment) : '' }}"
                                                data-delivery-date="{{ optional($order->delivery_date)->format('Y-m-d') }}"
                                                data-so-number="{{ $order->so_number }}"
                                                data-dr-number="{{ $order->dr_number }}"
                                                data-si-number="{{ $order->si_number }}"
                                                data-remarks="{{ $order->remarks }}"
                                                data-total="PHP {{ number_format($order->total_amount, 2) }}">
                                                <i class="ti ti-shield-check"></i> {{ auth()->user()->role === 'Area Distributor' && $order->status === 'Partial Received' ? 'Confirm Partial' : 'Status' }}
                                            </button>
                                        @endif
                                        <a href="{{ route($viewRouteName, $order->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-eye"></i> View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="ti ti-inbox"></i>
                                        <h6>{{ $hasFilters ? 'No matching purchase orders' : 'No AD purchase orders yet' }}</h6>
                                        <p>{{ $hasFilters ? 'Try clearing the filters or searching another keyword.' : 'Create a new ADPO to start tracking submissions.' }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if($canUpdateAdpoStatus)
        <script type="application/json" id="adpoOrderItemsJson">
            @json($adpoOrderItemsJson)
        </script>
        <div class="modal fade" id="adpoStatusModal" tabindex="-1" aria-labelledby="adpoStatusModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <form method="POST" enctype="multipart/form-data" id="adpoStatusForm" class="modal-content">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title" id="adpoStatusModalLabel">Verify DPO Status</h5>
                            <div class="text-muted small" id="adpoStatusModalSubtitle">Confirm warehouse movement before saving.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="status-modal-summary">
                            <div class="status-modal-item">
                                <span>PO Number</span>
                                <strong id="statusModalPo">N/A</strong>
                            </div>
                            <div class="status-modal-item">
                                <span>Total</span>
                                <strong id="statusModalTotal">N/A</strong>
                            </div>
                            <div class="status-modal-item">
                                <span>Business</span>
                                <strong id="statusModalBusiness">N/A</strong>
                            </div>
                            <div class="status-modal-item">
                                <span>Current Status</span>
                                <strong id="statusModalCurrent">N/A</strong>
                            </div>
                            <div class="status-modal-item d-none" id="statusModalSoWrap">
                                <span>SO Number</span>
                                <strong id="statusModalSo">N/A</strong>
                            </div>
                            <div class="status-modal-item d-none" id="statusModalSiWrap">
                                <span>SI Number</span>
                                <strong id="statusModalSi">N/A</strong>
                            </div>
                        </div>

                        <input type="hidden" name="payment_method" id="statusPaymentMethod">
                        <input type="hidden" name="reference_no" id="statusReferenceNo">

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-muted">Proof of Payment</label>
                            <div class="d-none" id="statusCurrentProof">
                                <a href="#" id="statusCurrentProofLink" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">
                                    <i class="ti ti-paperclip"></i> View Current Attachment
                                </a>
                            </div>
                            <div class="form-text d-none" id="statusNoCurrentProof">No proof of payment attachment is available.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-muted">Status</label>
                            <select name="status" id="statusModalSelect" class="form-select" required>
                                @foreach($statusOptions as $status)
                                    <option value="{{ $status }}">
                                        {{ auth()->user()->role === 'Area Distributor' && $status === 'Partial Received' ? 'Incomplete' : $status }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="verification-note mb-3" id="statusSoWrap">
                            <label class="form-label small fw-bold text-uppercase text-muted">SO Number</label>
                            <input type="text" name="so_number" id="statusSoNumber" class="form-control form-control-sm" placeholder="Enter SO number" data-uppercase disabled>
                        </div>

                        <div class="verification-note mb-3" id="statusDeliveryWrap">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-uppercase text-muted">Delivery Date</label>
                                    <input type="date" name="delivery_date" id="statusDeliveryDate" class="form-control form-control-sm" disabled>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-uppercase text-muted">DR Number</label>
                                    <input type="text" name="dr_number" id="statusDrNumber" class="form-control form-control-sm" placeholder="Enter DR number" data-uppercase disabled>
                                    <div class="form-text d-none" id="statusDrLockedHelp">The first DR number is saved and cannot be changed.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-uppercase text-muted">SI Number</label>
                                    <input type="text" name="si_number" id="statusSiNumber" class="form-control form-control-sm" placeholder="Enter SI number" data-uppercase disabled>
                                </div>
                            </div>
                        </div>

                        <div class="partial-received-items" id="partialReceivedWrap">
                            <div class="partial-received-card">
                                <div class="partial-items-head">
                                    <label class="form-label small fw-bold text-uppercase text-muted mb-0">Products Received</label>
                                    <span class="badge bg-warning text-dark">Partial</span>
                                </div>
                                <p class="partial-received-copy">
                                    {{ auth()->user()->role === 'Area Distributor' ? 'Verify the warehouse partial delivery and confirm only the quantity actually received.' : 'Enter only the actual received quantity. Ordered quantities and order totals will not be changed.' }}
                                </p>
                                @if(auth()->user()->role === 'Area Distributor')
                                    <div class="partial-confirm-callout">
                                        <i class="ti ti-clipboard-check"></i>
                                        <div>
                                            <strong>AD receiving confirmation</strong>
                                            <span>Review the DR rows below. Leave any item at 0 if it was not actually received, then confirm the partial delivery before saving.</span>
                                        </div>
                                    </div>
                                @endif
                                <div class="partial-summary">
                                    <div class="partial-summary-item">
                                        <span>Ordered</span>
                                        <strong id="partialSummaryOrdered">0</strong>
                                    </div>
                                    <div class="partial-summary-item">
                                        <span>Received</span>
                                        <strong id="partialSummaryReceived">0</strong>
                                    </div>
                                    <div class="partial-summary-item">
                                        <span>Pending</span>
                                        <strong id="partialSummaryPending">0</strong>
                                    </div>
                                </div>
                                <div class="partial-items-list" id="partialReceivedItems"></div>
                                <div class="form-text">
                                    {{ auth()->user()->role === 'Area Distributor' ? 'Confirmed quantity cannot exceed the For Receiving quantity from warehouse.' : 'Use 0 for products not received yet. Received quantity cannot exceed ordered quantity.' }}
                                </div>
                            </div>
                        </div>

                        <div class="verification-note" id="statusVerificationWrap">
                            <label class="form-label small fw-bold text-uppercase text-muted" id="statusVerificationLabel">Verification Remarks</label>
                            <textarea name="remarks" id="statusVerificationRemarks" class="form-control" rows="4" placeholder="Add notes for warehouse verification."></textarea>
                            <div class="form-text">Required for For Delivery, SO Created, Partial Received, and Cancelled status.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="ti ti-circle-check"></i> Save Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@section('javascript')
    @if($canUpdateAdpoStatus)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const orderItemsJson = document.getElementById('adpoOrderItemsJson');
                let orderItemsById = {};

                try {
                    orderItemsById = JSON.parse(orderItemsJson ? orderItemsJson.textContent : '{}') || {};
                } catch (error) {
                    orderItemsById = {};
                }

                const form = document.getElementById('adpoStatusForm');
                const statusSelect = document.getElementById('statusModalSelect');
                const remarksWrap = document.getElementById('statusVerificationWrap');
                const remarks = document.getElementById('statusVerificationRemarks');
                const remarksLabel = document.getElementById('statusVerificationLabel');
                const partialWrap = document.getElementById('partialReceivedWrap');
                const partialItems = document.getElementById('partialReceivedItems');
                const partialSummaryOrdered = document.getElementById('partialSummaryOrdered');
                const partialSummaryReceived = document.getElementById('partialSummaryReceived');
                const partialSummaryPending = document.getElementById('partialSummaryPending');
                const statusSoWrap = document.getElementById('statusSoWrap');
                const statusSoNumber = document.getElementById('statusSoNumber');
                const statusDeliveryWrap = document.getElementById('statusDeliveryWrap');
                const statusDeliveryDate = document.getElementById('statusDeliveryDate');
                const statusDrNumber = document.getElementById('statusDrNumber');
                const statusDrLockedHelp = document.getElementById('statusDrLockedHelp');
                const statusSiNumber = document.getElementById('statusSiNumber');

                if (!form || !statusSelect || !partialWrap || !partialItems || !statusSoWrap || !statusSoNumber || !statusDeliveryWrap || !statusDeliveryDate || !statusDrNumber || !statusSiNumber) {
                    return;
                }

                const statusesNeedingRemarks = ['For Delivery', 'SO Created', 'Partial Received', 'Cancelled'];
                const canEditPartialDocs = @json(auth()->user()->role === 'Admin');
                let currentItems = [];

                function syncPartialRow(row) {
                    if (!row) {
                        return;
                    }

                    const qtyInput = row.querySelector('.partial-received-qty');
                    const dateInput = row.querySelector('.partial-delivery-date');
                    const drInput = row.querySelector('.partial-dr-number');
                    const receivedQty = Number(qtyInput ? qtyInput.value || 0 : 0);
                    const orderedQty = Number(qtyInput ? qtyInput.dataset.orderedQty || 0 : 0);
                    const needsDocs = canEditPartialDocs && receivedQty > 0 && receivedQty < orderedQty && statusSelect.value === 'Partial Received';

                    if (dateInput) {
                        dateInput.required = needsDocs;
                    }

                    if (drInput) {
                        drInput.required = needsDocs;
                    }
                }

                function toggleRemarks() {
                    const needsRemarks = statusesNeedingRemarks.includes(statusSelect.value);
                    const needsSoDetails = statusSelect.value === 'SO Created';
                    const needsDeliveryDetails = statusSelect.value === 'For Delivery';
                    const needsPartialItems = statusSelect.value === 'Partial Received';

                    remarksWrap.classList.toggle('is-visible', needsRemarks);
                    remarks.required = needsRemarks;
                    remarksLabel.textContent = statusSelect.value === 'Cancelled'
                        ? 'Cancellation Remarks'
                        : (statusSelect.value === 'Partial Received' ? 'Partial Received Remarks' : 'Verification Remarks');
                    remarks.placeholder = statusSelect.value === 'Cancelled'
                        ? 'Add the reason for cancellation.'
                        : (statusSelect.value === 'Partial Received' ? 'Add the items or quantity still pending.' : 'Add notes for warehouse verification.');

                    statusSoWrap.classList.toggle('is-visible', needsSoDetails);
                    statusSoNumber.disabled = !needsSoDetails;
                    statusSoNumber.required = needsSoDetails;

                    statusDeliveryWrap.classList.toggle('is-visible', needsDeliveryDetails);
                    statusDeliveryDate.disabled = !needsDeliveryDetails;
                    statusDeliveryDate.required = needsDeliveryDetails;
                    statusDrNumber.disabled = !needsDeliveryDetails;
                    statusDrNumber.required = needsDeliveryDetails;
                    statusDrNumber.readOnly = needsDeliveryDetails && statusDrNumber.dataset.hasSavedDr === '1';
                    statusDrLockedHelp.classList.toggle('d-none', !statusDrNumber.readOnly);
                    statusSiNumber.disabled = !needsDeliveryDetails;
                    statusSiNumber.required = needsDeliveryDetails;

                    partialWrap.classList.toggle('is-visible', needsPartialItems);
                    partialItems.querySelectorAll('.partial-received-qty, .partial-receive-mode, .partial-delivery-date, .partial-dr-number').forEach(function (input) {
                        input.disabled = !needsPartialItems;
                    });
                    partialItems.querySelectorAll('.partial-item-row').forEach(syncPartialRow);
                }

                function escapeHtml(value) {
                        return String(value || '')
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                }

                function renderPartialItems(items) {
                    partialItems.innerHTML = '';
                    items = Array.isArray(items) ? items : Object.values(items || {});
                    let orderedTotal = 0;
                    let receivedTotal = 0;

                    if (!items.length) {
                        partialItems.innerHTML = '<div class="partial-items-empty">No products found for this DPO.</div>';
                        updatePartialSummary(0, 0);
                        return;
                    }

                    items.forEach(function (item) {
                        if (!item || !item.id) {
                            return;
                        }

                        const qty = Math.max(Number(item.qty || 0), 0);
                        const confirmedBeforeQty = Math.min(Math.max(Number(item.confirmed_before_qty || 0), 0), qty);
                        const forReceivingQty = Math.min(Math.max(Number(item.received_qty || 0), 0), qty);
                        const receiveMode = item.receive_mode || '';
                        const receiptId = Number(item.receipt_id || 0);
                        const isDirectAdReceipt = !canEditPartialDocs && !receiptId && receiveMode === 'increment';
                        const inputMax = canEditPartialDocs
                            ? qty
                            : (isDirectAdReceipt ? Math.max(qty - confirmedBeforeQty, 0) : forReceivingQty);
                        const receivedQty = isDirectAdReceipt ? 0 : Math.min(forReceivingQty, inputMax);
                        const inputName = receiptId
                            ? `partial_receipts[${receiptId}][confirmed_qty]`
                            : `partial_items[${Number(item.id)}][received_qty]`;
                        const receiveModeInput = isDirectAdReceipt
                            ? `<input type="hidden" name="partial_items[${Number(item.id)}][receive_mode]" value="increment" class="partial-receive-mode" disabled>`
                            : '';
                        const deliveryDate = item.delivery_date || '';
                        const drNumber = item.dr_number || '';
                        orderedTotal += receiptId ? 0 : qty;
                        receivedTotal += receiptId ? 0 : receivedQty;

                        const row = document.createElement('div');
                        row.className = 'partial-item-row';
                        row.innerHTML = `
                            <div>
                                <div class="partial-item-name" title="${escapeHtml(item.name)}">${escapeHtml(item.name)}</div>
                                <div class="partial-item-meta">
                                    <span>Ordered: ${qty.toLocaleString()}</span>
                                    ${confirmedBeforeQty > 0 ? `<span>Confirmed: ${confirmedBeforeQty.toLocaleString()}</span>` : ''}
                                    <span>For Receiving: ${forReceivingQty.toLocaleString()}</span>
                                    <span class="js-partial-pending">Pending: ${Math.max(qty - confirmedBeforeQty - receivedQty, 0).toLocaleString()}</span>
                                </div>
                            </div>
                            <div>
                                <span class="partial-qty-label">${canEditPartialDocs ? 'Received' : 'Confirm Received'}</span>
                                <input type="number"
                                    name="${inputName}"
                                    class="form-control form-control-sm partial-received-qty"
                                    min="0"
                                    max="${inputMax}"
                                    value="${receivedQty}"
                                    data-item-id="${Number(item.id || 0)}"
                                    data-ordered-qty="${qty}"
                                    data-confirmed-before-qty="${confirmedBeforeQty}"
                                    data-for-receiving-qty="${forReceivingQty}"
                                    data-dr-number="${escapeHtml(drNumber)}"
                                    disabled>
                                ${receiveModeInput}
                            </div>
                            ${canEditPartialDocs ? `
                                <div>
                                    <span class="partial-qty-label">Delivery Date</span>
                                    <input type="date"
                                        name="partial_items[${Number(item.id)}][delivery_date]"
                                        class="form-control form-control-sm partial-doc-input partial-delivery-date"
                                        value="${escapeHtml(deliveryDate)}"
                                        disabled>
                                </div>
                                <div>
                                    <span class="partial-qty-label">DR No.</span>
                                    <input type="text"
                                        name="partial_items[${Number(item.id)}][dr_number]"
                                        class="form-control form-control-sm partial-doc-input partial-dr-number"
                                        value="${escapeHtml(drNumber)}"
                                        placeholder="Enter DR no."
                                        data-uppercase
                                        disabled>
                                </div>
                            ` : `
                                <div>
                                    <span class="partial-qty-label">Delivery Date</span>
                                    <div class="partial-readonly-doc">${deliveryDate ? escapeHtml(deliveryDate) : 'N/A'}</div>
                                </div>
                                <div>
                                    <span class="partial-qty-label">DR No.</span>
                                    <div class="partial-readonly-doc">${drNumber ? escapeHtml(drNumber).toUpperCase() : 'N/A'}</div>
                                </div>
                            `}
                        `;
                        partialItems.appendChild(row);
                    });

                    updatePartialSummaryFromRows();
                }

                function updatePartialSummary(orderedTotal, receivedTotal) {
                    const pendingTotal = Math.max(orderedTotal - receivedTotal, 0);

                    if (partialSummaryOrdered) {
                        partialSummaryOrdered.textContent = orderedTotal.toLocaleString();
                    }

                    if (partialSummaryReceived) {
                        partialSummaryReceived.textContent = receivedTotal.toLocaleString();
                    }

                    if (partialSummaryPending) {
                        partialSummaryPending.textContent = pendingTotal.toLocaleString();
                    }
                }

                function partialTotalsFromRows() {
                    const itemTotals = {};

                    Array.from(partialItems.querySelectorAll('.partial-received-qty')).forEach(function (input) {
                        const itemId = input.dataset.itemId || input.name || Math.random().toString();
                        const orderedQty = Number(input.dataset.orderedQty || 0);
                        const confirmedBeforeQty = Number(input.dataset.confirmedBeforeQty || 0);
                        const receivedQty = Number(input.value || 0);

                        if (!itemTotals[itemId]) {
                            itemTotals[itemId] = {
                                ordered: orderedQty,
                                confirmedBefore: confirmedBeforeQty,
                                received: 0
                            };
                        }

                        itemTotals[itemId].received += receivedQty;
                    });

                    return Object.values(itemTotals).reduce(function (totals, item) {
                        totals.ordered += item.ordered;
                        totals.received += Math.min(item.confirmedBefore + item.received, item.ordered);
                        return totals;
                    }, { ordered: 0, received: 0 });
                }

                function updatePartialSummaryFromRows() {
                    const totals = partialTotalsFromRows();
                    updatePartialSummary(totals.ordered, totals.received);
                }

                partialItems.addEventListener('input', function (event) {
                    if (!event.target.classList.contains('partial-received-qty')) {
                        return;
                    }

                    const input = event.target;
                    const orderedQty = Number(input.dataset.orderedQty || 0);
                    const maxQty = Number(input.max || orderedQty || 0);
                    let value = Number(input.value || 0);

                    if (value < 0) {
                        value = 0;
                    }

                    if (value > maxQty) {
                        value = maxQty;
                    }

                    input.value = value;

                    const row = input.closest('.partial-item-row');
                    const pending = row ? row.querySelector('.js-partial-pending') : null;

                    if (pending) {
                        const confirmedBeforeQty = Number(input.dataset.confirmedBeforeQty || 0);
                        pending.textContent = 'Pending: ' + Math.max(orderedQty - confirmedBeforeQty - value, 0).toLocaleString();
                    }

                    syncPartialRow(row);
                    updatePartialSummaryFromRows();
                });

                function partialConfirmationData() {
                    const rows = Array.from(partialItems.querySelectorAll('.partial-item-row'));
                    const itemTotals = {};
                    const receivedRows = [];
                    let allForReceivingConfirmed = rows.length > 0;

                    rows.forEach(function (row) {
                        const qtyInput = row.querySelector('.partial-received-qty');
                        const nameEl = row.querySelector('.partial-item-name');
                        const itemId = qtyInput ? (qtyInput.dataset.itemId || qtyInput.name) : Math.random().toString();
                        const orderedQty = Number(qtyInput ? qtyInput.dataset.orderedQty || 0 : 0);
                        const confirmedBeforeQty = Number(qtyInput ? qtyInput.dataset.confirmedBeforeQty || 0 : 0);
                        const forReceivingQty = Number(qtyInput ? qtyInput.dataset.forReceivingQty || 0 : 0);
                        const drNumber = qtyInput ? qtyInput.dataset.drNumber || 'N/A' : 'N/A';
                        const receivedQty = Number(qtyInput ? qtyInput.value || 0 : 0);

                        if (receivedQty !== forReceivingQty) {
                            allForReceivingConfirmed = false;
                        }

                        if (!itemTotals[itemId]) {
                            itemTotals[itemId] = {
                                ordered: orderedQty,
                                confirmedBefore: confirmedBeforeQty,
                                received: 0
                            };
                        }

                        itemTotals[itemId].received += receivedQty;

                        if (receivedQty > 0) {
                            receivedRows.push({
                                name: nameEl ? nameEl.textContent.trim() : 'Product',
                                drNumber: drNumber,
                                ordered: orderedQty,
                                forReceiving: forReceivingQty,
                                received: receivedQty,
                                pending: Math.max(orderedQty - confirmedBeforeQty - receivedQty, 0)
                            });
                        }
                    });

                    const totals = Object.values(itemTotals).reduce(function (summary, item) {
                        summary.ordered += item.ordered;
                        summary.received += Math.min(item.confirmedBefore + item.received, item.ordered);
                        return summary;
                    }, { ordered: 0, received: 0 });

                    return {
                        ordered: totals.ordered,
                        received: totals.received,
                        pending: Math.max(totals.ordered - totals.received, 0),
                        completesOrder: totals.ordered > 0
                            && allForReceivingConfirmed
                            && Math.max(totals.ordered - totals.received, 0) === 0,
                        rows: receivedRows
                    };
                }

                function submitConfirmedForm() {
                    Swal.fire({
                        title: 'Updating ADPO',
                        text: 'Please wait while we save the status and notify the warehouse.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: function () {
                            Swal.showLoading();
                        }
                    });
                    form.dataset.confirmed = 'true';
                    form.submit();
                }

                document.querySelectorAll('.js-status-modal').forEach(function (button) {
                    button.addEventListener('click', function () {
                        form.action = button.dataset.action;
                        document.getElementById('statusModalPo').textContent = button.dataset.po || 'N/A';
                        document.getElementById('statusModalTotal').textContent = button.dataset.total || 'N/A';
                        document.getElementById('statusModalBusiness').textContent = button.dataset.business || 'N/A';
                        document.getElementById('statusModalCurrent').textContent = button.dataset.currentStatus || 'N/A';
                        const modalSoWrap = document.getElementById('statusModalSoWrap');
                        const modalSiWrap = document.getElementById('statusModalSiWrap');
                        const savedSoNumber = (button.dataset.soNumber || '').trim();
                        const savedSiNumber = (button.dataset.siNumber || '').trim();
                        document.getElementById('statusModalSo').textContent = savedSoNumber || 'N/A';
                        document.getElementById('statusModalSi').textContent = savedSiNumber || 'N/A';
                        modalSoWrap.classList.toggle('d-none', savedSoNumber === '');
                        modalSiWrap.classList.toggle('d-none', savedSiNumber === '');
                        document.getElementById('statusPaymentMethod').value = button.dataset.paymentMethod || 'cash';
                        document.getElementById('statusReferenceNo').value = button.dataset.referenceNo || '';
                        const proofInput = document.getElementById('statusProofOfPayment');
                        const hasProof = button.dataset.hasProof === '1';
                        const currentProof = document.getElementById('statusCurrentProof');
                        const currentProofLink = document.getElementById('statusCurrentProofLink');
                        const noCurrentProof = document.getElementById('statusNoCurrentProof');

                        if (proofInput) {
                            proofInput.value = '';
                            proofInput.required = !hasProof;
                            document.getElementById('statusProofHelp').textContent = hasProof
                                ? 'A proof is already saved. Select a file only to replace it.'
                                : 'Required. JPG, PNG, or PDF. Maximum size: 5 MB.';
                        }

                        currentProof.classList.toggle('d-none', !hasProof);
                        currentProofLink.href = hasProof ? button.dataset.proofUrl : '#';
                        noCurrentProof.classList.toggle('d-none', hasProof);
                        statusDeliveryDate.value = button.dataset.deliveryDate || '';
                        statusSoNumber.value = button.dataset.soNumber || '';
                        statusDrNumber.value = button.dataset.drNumber || '';
                        statusDrNumber.dataset.hasSavedDr = statusDrNumber.value.trim() !== '' ? '1' : '0';
                        statusSiNumber.value = button.dataset.siNumber || '';
                        currentItems = orderItemsById[button.dataset.orderId] || [];
                        renderPartialItems(currentItems);
                        const requestedStatus = button.dataset.currentStatus || '';
                        const modalTitle = document.getElementById('adpoStatusModalLabel');
                        const modalSubtitle = document.getElementById('adpoStatusModalSubtitle');
                        const hasNoReceivingProducts = currentItems.length === 0;
                        const canCompleteReceiving = button.dataset.canCompleteReceiving === '1';
                        const cancelledOption = Array.from(statusSelect.options).find(function (option) {
                            return option.value === 'Cancelled';
                        });

                        if (modalTitle) {
                            modalTitle.textContent = requestedStatus === 'Partial Received'
                                ? 'Confirm Partial Delivery'
                                : 'Verify DPO Status';
                        }

                        if (modalSubtitle) {
                            modalSubtitle.textContent = requestedStatus === 'Partial Received'
                                ? 'Review each DR quantity received by the AD before saving.'
                                : 'Confirm warehouse movement before saving.';
                        }

                        if (cancelledOption) {
                            const canCancel = ['Pending', 'SO Created'].includes(requestedStatus);
                            cancelledOption.hidden = !canCancel;
                            cancelledOption.disabled = !canCancel;
                        }

                        const selectableOptions = Array.from(statusSelect.options).filter(function (option) {
                            return !option.disabled;
                        });
                        statusSelect.value = requestedStatus === 'Partial Received'
                            && hasNoReceivingProducts
                            && canCompleteReceiving
                            ? 'Completed'
                            : (selectableOptions.some(function (option) {
                            return option.value === requestedStatus;
                        }) ? requestedStatus : (selectableOptions[0] ? selectableOptions[0].value : ''));
                        remarks.value = button.dataset.remarks || '';
                        form.dataset.confirmed = '';
                        form.dataset.willComplete = '';
                        toggleRemarks();
                    });
                });

                statusSelect.addEventListener('change', toggleRemarks);

                form.addEventListener('submit', function (event) {
                    if (form.dataset.confirmed === 'true') {
                        return;
                    }

                    event.preventDefault();

                    if (statusSelect.value === 'Partial Received' && !canEditPartialDocs) {
                        const confirmation = partialConfirmationData();
                        const hasNoReceivingProducts = partialItems.querySelectorAll('.partial-received-qty').length === 0;

                        form.dataset.willComplete = (
                            hasNoReceivingProducts
                            || (confirmation.ordered > 0 && confirmation.pending === 0)
                        ) ? 'true' : 'false';
                    }

                    if (statusesNeedingRemarks.includes(statusSelect.value) && remarks.value.trim() === '') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Remarks required',
                            text: 'Please add remarks before saving this status.',
                            confirmButtonText: 'OK'
                        });
                        remarks.focus();
                        return;
                    }

                    if (statusSelect.value === 'SO Created' && statusSoNumber.value.trim() === '') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'SO number required',
                            text: 'Please enter the SO number before saving this status.',
                            confirmButtonText: 'OK'
                        });
                        statusSoNumber.focus();
                        return;
                    }

                    if (statusSelect.value === 'For Delivery' && (statusDeliveryDate.value.trim() === '' || statusDrNumber.value.trim() === '' || statusSiNumber.value.trim() === '')) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Delivery details required',
                            text: 'Please complete the delivery date, DR number, and SI number before saving this status.',
                            confirmButtonText: 'OK'
                        });

                        if (statusDeliveryDate.value.trim() === '') {
                            statusDeliveryDate.focus();
                        } else if (statusDrNumber.value.trim() === '') {
                            statusDrNumber.focus();
                        } else {
                            statusSiNumber.focus();
                        }

                        return;
                    }

                    if (statusSelect.value === 'Partial Received') {
                        const qtyInputs = Array.from(partialItems.querySelectorAll('.partial-received-qty'));
                        const hasProduct = qtyInputs.length > 0;
                        const hasReceivedQty = qtyInputs.some(function (input) {
                            return Number(input.value || 0) > 0;
                        });
                        const hasInvalidQty = qtyInputs.some(function (input) {
                            const value = Number(input.value || 0);
                            const max = Number(input.max || 0);

                            return value < 0 || value > max;
                        });
                        const missingDocsRow = canEditPartialDocs ? Array.from(partialItems.querySelectorAll('.partial-item-row')).find(function (row) {
                            const qtyInput = row.querySelector('.partial-received-qty');
                            const dateInput = row.querySelector('.partial-delivery-date');
                            const drInput = row.querySelector('.partial-dr-number');
                            const receivedQty = Number(qtyInput ? qtyInput.value || 0 : 0);
                            const orderedQty = Number(qtyInput ? qtyInput.dataset.orderedQty || 0 : 0);

                            return receivedQty > 0
                                && receivedQty < orderedQty
                                && (!dateInput || dateInput.value.trim() === '' || !drInput || drInput.value.trim() === '');
                        }) : null;

                        if (!hasProduct || !hasReceivedQty || hasInvalidQty || missingDocsRow) {
                            Swal.fire({
                                icon: 'warning',
                                title: missingDocsRow ? 'Product delivery details required' : 'Product quantities required',
                                text: missingDocsRow
                                    ? 'Please enter delivery date and DR number for each product with partial received quantity.'
                                    : (hasInvalidQty
                                    ? 'Confirmed quantity cannot be less than 0 or greater than the For Receiving quantity.'
                                    : 'Please enter at least one received product quantity.'),
                                confirmButtonText: 'OK'
                            });

                            if (missingDocsRow) {
                                const missingInput = missingDocsRow.querySelector('.partial-delivery-date:invalid, .partial-dr-number:invalid')
                                    || missingDocsRow.querySelector('.partial-delivery-date, .partial-dr-number');
                                missingInput.focus();
                            } else if (qtyInputs.length) {
                                qtyInputs[0].focus();
                            }

                            return;
                        }
                    }

                    if (statusSelect.value === 'Partial Received' && !canEditPartialDocs) {
                        const confirmation = partialConfirmationData();
                        const willComplete = form.dataset.willComplete === 'true' || confirmation.completesOrder;
                        const rowsHtml = confirmation.rows.map(function (row) {
                            return `
                                <tr>
                                    <td style="padding:6px 8px;text-align:left;border-bottom:1px solid #eef2f7;font-weight:700;">${escapeHtml(row.drNumber).toUpperCase()}</td>
                                    <td style="padding:6px 8px;text-align:left;border-bottom:1px solid #eef2f7;">${escapeHtml(row.name)}</td>
                                    <td style="padding:6px 8px;text-align:center;border-bottom:1px solid #eef2f7;">${row.ordered.toLocaleString()}</td>
                                    <td style="padding:6px 8px;text-align:center;border-bottom:1px solid #eef2f7;">${row.forReceiving.toLocaleString()}</td>
                                    <td style="padding:6px 8px;text-align:center;border-bottom:1px solid #eef2f7;font-weight:700;color:#15803d;">${row.received.toLocaleString()}</td>
                                    <td style="padding:6px 8px;text-align:center;border-bottom:1px solid #eef2f7;">${row.pending.toLocaleString()}</td>
                                </tr>
                            `;
                        }).join('');

                        Swal.fire({
                            icon: willComplete ? 'success' : 'question',
                            title: willComplete ? 'Complete this DPO?' : 'Confirm partial received?',
                            html: `
                                <div style="text-align:left">
                                    <div style="display:grid;grid-template-columns:38px minmax(0,1fr);gap:10px;align-items:start;margin-bottom:12px;padding:10px 12px;border:1px solid ${willComplete ? '#bbf7d0' : '#bfdbfe'};border-radius:8px;background:${willComplete ? '#f0fdf4' : '#eff6ff'};">
                                        <div style="width:38px;height:38px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:#fff;color:${willComplete ? '#15803d' : '#1d4ed8'};font-size:18px;">
                                            <i class="bi ${willComplete ? 'bi-check2-circle' : 'bi-clipboard-check'}"></i>
                                        </div>
                                        <div>
                                            <strong style="display:block;color:#101828;font-size:13px;">${willComplete ? 'All receiving will be completed' : 'AD confirmation required'}</strong>
                                            <span style="display:block;margin-top:2px;color:#475467;font-size:12px;line-height:1.4;">${willComplete
                                                ? 'All products are confirmed received. Saving will mark this DPO as Completed.'
                                                : 'Confirm only the quantities actually received from the warehouse delivery.'}</span>
                                        </div>
                                    </div>
                                    <p class="mb-2" style="color:#475467;font-size:13px;">${willComplete
                                        ? 'All products are confirmed received. Saving will mark this DPO as Completed.'
                                        : 'Please review the AD confirmed quantities before saving. Confirmed quantity cannot be higher than For Receiving.'}</p>
                                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:10px;">
                                        <div style="padding:9px 10px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;"><small style="color:#667085;font-weight:800;text-transform:uppercase;">Ordered</small><br><strong style="font-size:18px;color:#101828;">${confirmation.ordered.toLocaleString()}</strong></div>
                                        <div style="padding:9px 10px;border:1px solid #bbf7d0;border-radius:8px;background:#f0fdf4;"><small style="color:#15803d;font-weight:800;text-transform:uppercase;">Confirmed</small><br><strong style="font-size:18px;color:#101828;">${confirmation.received.toLocaleString()}</strong></div>
                                        <div style="padding:9px 10px;border:1px solid ${confirmation.pending > 0 ? '#fde68a' : '#bbf7d0'};border-radius:8px;background:${confirmation.pending > 0 ? '#fffbeb' : '#f0fdf4'};"><small style="color:${confirmation.pending > 0 ? '#92400e' : '#15803d'};font-weight:800;text-transform:uppercase;">Pending</small><br><strong style="font-size:18px;color:#101828;">${confirmation.pending.toLocaleString()}</strong></div>
                                    </div>
                                    <div style="max-height:220px;overflow:auto;border:1px solid #eef2f7;border-radius:8px;">
                                        <table style="width:100%;font-size:12px;border-collapse:collapse;">
                                            <thead>
                                                <tr>
                                                    <th style="padding:7px 8px;text-align:left;background:#f8fafc;">DR No.</th>
                                                    <th style="padding:7px 8px;text-align:left;background:#f8fafc;">Product</th>
                                                    <th style="padding:7px 8px;text-align:center;background:#f8fafc;">Order</th>
                                                    <th style="padding:7px 8px;text-align:center;background:#f8fafc;">For Receiving</th>
                                                    <th style="padding:7px 8px;text-align:center;background:#f8fafc;">Confirmed</th>
                                                    <th style="padding:7px 8px;text-align:center;background:#f8fafc;">Pending</th>
                                                </tr>
                                            </thead>
                                            <tbody>${rowsHtml || '<tr><td colspan="6" style="padding:12px;text-align:center;color:#667085;">No received quantity selected.</td></tr>'}</tbody>
                                        </table>
                                    </div>
                                </div>
                            `,
                            showCancelButton: true,
                            confirmButtonText: willComplete ? 'Confirm and complete' : 'Confirm received qty',
                            cancelButtonText: 'Review again',
                            confirmButtonColor: '#0d6efd',
                            reverseButtons: true,
                            width: 680
                        }).then(function (result) {
                            if (result.isConfirmed) {
                                submitConfirmedForm();
                            }
                        });

                        return;
                    }

                    Swal.fire({
                        icon: statusesNeedingRemarks.includes(statusSelect.value) ? 'warning' : 'question',
                        title: 'Save status?',
                        text: 'This will update the ADPO to ' + statusSelect.value + '.',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, save',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: statusSelect.value === 'Cancelled' ? '#b42318' : '#0d6efd',
                        reverseButtons: true
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            submitConfirmedForm();
                        }
                    });
                });
            });
        </script>
    @endif
@endsection
