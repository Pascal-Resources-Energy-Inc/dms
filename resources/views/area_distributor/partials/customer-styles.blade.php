<style>
    .distributor-customers { min-width: 0; }
    .distributor-customers .customer-head { padding: 26px 28px; border: 0; border-radius: 18px; background: linear-gradient(120deg, #0b3f73, #0f6ba8 58%, #48a6c6); box-shadow: 0 16px 34px rgba(14,74,125,.16); }
    .distributor-customers .customer-head h4 { color: #fff; font-size: clamp(1.35rem, 2.5vw, 1.8rem); font-weight: 800; }
    .distributor-customers .customer-head .text-muted { color: rgba(255,255,255,.85) !important; }
    .distributor-customers .customer-eyebrow { color: #bdefff; }
    .distributor-customers .customer-head .btn { min-height: 44px; border-radius: 10px; font-weight: 700; }
    .distributor-customers .customer-stat { display: grid; grid-template-columns: 46px 1fr; column-gap: 14px; align-items: center; border-radius: 14px; }
    .distributor-customers .customer-stat-icon { grid-row: span 2; margin: 0; width: 46px; height: 46px; border-radius: 12px; }
    .distributor-customers .customer-stat span { margin-top: 4px; }
    .distributor-customers .customer-table-card { border-radius: 14px; }
    .distributor-customers .customer-tab { min-height: 40px; border-radius: 9px; }
    .distributor-customers .customer-tab.active { color: #fff; background: #0f6ba8; border-color: #0f6ba8; }
    .distributor-customers .customer-table thead th { background: #f7fafc; }
    .distributor-customers .customer-table td { vertical-align: middle; }
    .distributor-customers .dataTables_paginate .pagination { flex-wrap: wrap; justify-content: flex-end; }
    .distributor-customer-modal .modal-content { max-height: calc(100vh - 3.5rem); max-height: calc(100dvh - 3.5rem); overflow: hidden; }
    .distributor-customer-modal form { display: flex; flex-direction: column; min-height: 0; overflow: hidden; }
    .distributor-customer-modal .modal-header, .distributor-customer-modal .modal-footer { flex-shrink: 0; }
    .distributor-customer-modal .modal-header { padding: 18px 24px; border-bottom: 1px solid #e2e8f0; }
    .distributor-customer-modal .modal-footer { padding: 14px 24px; border-top: 1px solid #e2e8f0; }
    .distributor-customer-modal .modal-body { overflow-y: auto; overscroll-behavior: contain; }
    @media (max-width: 767.98px) {
        .distributor-customers .customer-head { padding: 20px; border-radius: 14px; }
        .distributor-customers .customer-head .btn { width: 100%; }
        .distributor-customers .customer-tabs { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); width: 100%; gap: 6px; }
        .distributor-customers .customer-tab { min-width: 0; min-height: 44px; justify-content: center; flex-wrap: wrap; white-space: normal; padding: 8px 5px; font-size: 12px; }
        .distributor-customers .customer-tab > i { display: none; }
        .distributor-customers .customer-table { min-width: 0; }
        .distributor-customers .customer-table thead { display: none; }
        .distributor-customers .customer-table, .distributor-customers .customer-table tbody, .distributor-customers .customer-table tr { display: block; width: 100% !important; }
        .distributor-customers .customer-table tbody tr { padding: 5px 12px; margin-bottom: 12px; border: 1px solid #e4edf5; border-radius: 11px; }
        .distributor-customers .customer-table tbody td { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 9px 0; white-space: normal; text-align: right; overflow-wrap: anywhere; }
        .distributor-customers .customer-table tbody td::before { content: attr(data-label); flex: 0 0 32%; text-align: left; font-size: 11px; font-weight: 700; color: #64748b; }
        .distributor-customers .customer-table tbody td:last-child { border-bottom: 0; }
        .distributor-customers .customer-muted { max-width: none; white-space: normal; }
        .distributor-customers .dataTables_filter label { width: 100%; text-align: left; }
        .distributor-customers .dataTables_filter input { display: block; width: 100%; margin: 6px 0 0; }
        .distributor-customers .dataTables_paginate .pagination { justify-content: center; }
        .distributor-customer-modal .modal-content { max-height: calc(100vh - 1rem); max-height: calc(100dvh - 1rem); }
    }
</style>
