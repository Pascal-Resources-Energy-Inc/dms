<?php

// The permission keys already in use remain valid. New rows inherit their
// former group only until an explicit selection is saved for that row.
$row = function ($key, $label, $description, $routes, $legacy = null, $inherits = []) {
    return compact('key', 'label', 'description', 'routes', 'legacy', 'inherits');
};

return [
    'workspace' => ['label' => 'Workspace', 'icon' => 'fa-th-large', 'modules' => [
        $row('dashboard.overview', 'Dashboard', 'Overview, alerts, and performance summary', ['view' => ['home']], null),
    ]],
    'users' => ['label' => 'Administration', 'icon' => 'fa-users-cog', 'modules' => [
        $row('users.accounts', 'Users', 'Account list and access control', ['view' => ['users', 'users.data', 'users.show'], 'add' => [], 'edit' => ['users.update', 'users.access.update'], 'delete' => []]),
    ]],
    'partners' => ['label' => 'Partners', 'icon' => 'fa-handshake', 'modules' => [
        $row('distributors.records', 'Area Distributors', 'Area Distributor profiles and awarded areas', ['view' => ['ads', 'ad.view', 'ad.areas.offer-sheet'], 'add' => ['new-ad'], 'edit' => ['edit-ads', 'update.areas'], 'delete' => []], 'can_access_distributors'),
        $row('distributors.provincial', 'Provincial Distributors', 'Provincial Distributor profiles and awarded areas', ['view' => ['pds', 'pd.view', 'pds.areas.offer-sheet'], 'add' => ['pds.store'], 'edit' => ['pds.update', 'pds.areas.update'], 'delete' => ['pds.destroy']], 'can_access_distributors', ['distributors.records']),
        $row('dealers.records', 'Dealers', 'Regular, Project Rise, and Project Genesis dealers', ['view' => ['dealers', 'dealer.view', 'admin.crm.dealer.view', 'dealer-ads'], 'add' => [], 'edit' => ['dealer.update'], 'delete' => []], 'can_access_dealers'),
        $row('dealers.mega', 'Mega Dealers', 'Regular, Project Rise, and Project Genesis mega dealers', ['view' => ['mds', 'mds.data', 'mega-dealer.view', 'admin.crm.mega-dealer.view', 'md-ads'], 'add' => [], 'edit' => ['mega-dealer.update'], 'delete' => ['mds.destroy']], 'can_access_dealers', ['dealers.records']),
        $row('customers.records', 'Customers', 'Customer records and profiles', ['view' => ['customers'], 'add' => [], 'edit' => [], 'delete' => []], 'can_access_customers'),
    ]],
    'operations' => ['label' => 'Operations', 'icon' => 'fa-cash-register', 'modules' => [
        $row('transactions.sales', 'Transactions', 'Sales and payment activity', ['view' => ['transactions', 'ad-transactions'], 'add' => ['new-transaction', 'ad.new-transaction'], 'edit' => [], 'delete' => ['transactions.destroy', 'transactions.bulkDelete']], 'can_access_transactions'),
        $row('transactions.customer_overview', 'Customer Transaction Overview', 'Dealer, mega dealer, and center chief customer transactions', ['view' => ['customer-ads']], 'can_access_transactions', ['transactions.sales']),
        $row('purchase_orders.adpo', 'Purchase Orders', 'Distributor purchase orders from PREI Warehouse', ['view' => ['ad-purchase-orders.index', 'ad-purchase-orders.export', 'ad-purchase-orders.show'], 'add' => ['ad-purchase-orders.create', 'ad-purchase-orders.store'], 'edit' => ['ad-purchase-orders.edit', 'ad-purchase-orders.update', 'ad-purchase-orders.updateStatus'], 'delete' => ['ad-purchase-orders.destroy']], 'can_access_purchase_orders'),
        $row('purchase_orders.provincial', 'PD Purchase Orders', 'Incoming Area Distributor orders for Provincial Distributors', ['view' => ['provincial-distributor.purchase-orders']], 'can_access_purchase_orders', ['purchase_orders.adpo']),
        $row('purchase_orders.region_v', 'Region V Warehouse Orders', 'Purchase orders assigned to the Region V warehouse', ['view' => ['warehouse-ad-purchase-orders.region-v', 'warehouse-ad-purchase-orders.region-v.export']], 'can_access_purchase_orders', ['purchase_orders.adpo']),
        $row('inventory.stock', 'Stock Inventory Sheet', 'Stock inventory by distributor and area', ['view' => ['stock-inventory', 'stock-inventory.export'], 'add' => [], 'edit' => [], 'delete' => []], 'can_access_inventory'),
        $row('orders.sales_orders', 'Sales Orders', 'Customer and dealer order management', ['view' => ['orders', 'orders.export'], 'add' => ['orders.store'], 'edit' => ['orders.update'], 'delete' => []]),
        $row('orders.guest', 'Guest Orders', 'Orders for walk-in customers', ['view' => ['guest-order'], 'add' => ['guest-order.store']], null, ['orders.sales_orders']),
        $row('stock_requests.approvals', 'Stock Requests', 'Review and approve dealer stock requests', ['view' => ['admin.stock.requests'], 'add' => ['dealer.stock.requests.store'], 'edit' => ['admin.stock.requests.approve', 'admin.stock.requests.reject'], 'delete' => []], 'can_access_stock_requests'),
        $row('inventory_transfers.transfers', 'Inventory Movement', 'Stock transfers and inventory movement history', ['view' => ['inventory-transfers.index', 'inventory-transfers.data'], 'add' => ['inventory-transfers.store'], 'edit' => [], 'delete' => ['inventory-transfers.destroy']]),
        $row('pull_out_requests.approvals', 'Pull Out Requests', 'Warehouse review and release of pull-out replacements', ['view' => ['warehouse-pull-outs.index', 'warehouse-pull-outs.data', 'warehouse-pull-outs.delivery-receipts'], 'edit' => ['warehouse-pull-outs.review', 'warehouse-pull-outs.approve', 'warehouse-pull-outs.release']]),
        $row('return_refunds.requests', 'Return and Refund', 'Return, replacement, and refund workflow', ['view' => ['return-refunds.index'], 'add' => [], 'edit' => ['return-refunds.approve', 'return-refunds.approve-products', 'return-refunds.documents', 'return-refunds.receive', 'return-refunds.credit-memo', 'return-refunds.revert', 'return-refunds.reviewed', 'return-refunds.president-approve', 'return-refunds.president-revert'], 'delete' => []]),
        $row('charges.records', 'Other Charges', 'Distributor charges and discounts', ['view' => ['charges'], 'add' => ['charges.store'], 'edit' => ['charges.update'], 'delete' => ['charges.destroy']]),
        $row('locations.directory', 'Store Locations', 'Store map and location directory', ['view' => ['storelocation']]),
    ]],
    'settings' => ['label' => 'Settings and Rewards', 'icon' => 'fa-cog', 'modules' => [
        $row('settings.items', 'Items', 'Master product items and catalog setup', ['view' => ['items'], 'add' => ['items.store'], 'edit' => ['items.update'], 'delete' => ['items.destroy']], 'can_access_settings'),
        $row('products.catalog', 'Product Catalog', 'Product list, pricing, bundles, and product setup', ['view' => ['products'], 'add' => ['products.create', 'products.store'], 'edit' => ['products.storeBulk'], 'delete' => ['products.destroy']]),
        $row('settings.rewards', 'Rewards', 'Reward setup and maintenance', ['view' => ['rewards'], 'add' => ['rewards.store'], 'edit' => ['rewards.update', 'rewards.updateStatus'], 'delete' => ['rewards.destroy']], 'can_access_settings'),
        $row('settings.campaigns', 'Vouchers', 'Vouchers, imports, and redemption', ['view' => ['vouchers', 'vouchers.export', 'vouchers.ad-orders', 'vouchers.ad-orders.export', 'vouchers.import-template'], 'add' => ['vouchers.store', 'vouchers.upload'], 'edit' => ['vouchers.update'], 'delete' => ['vouchers.destroy']], 'can_access_settings'),
        $row('settings.raffles', 'Raffles', 'Raffles, entries, and draws', ['view' => ['raffles'], 'add' => ['raffles.store', 'raffles.entries.store'], 'edit' => ['raffles.update', 'raffles.draw'], 'delete' => ['raffles.destroy', 'raffles.entries.destroy']], 'can_access_settings', ['settings.campaigns']),
        $row('settings.areas', 'Areas', 'Area directory and settings', ['view' => ['areas'], 'add' => ['areas.store'], 'edit' => ['areas.update'], 'delete' => ['areas.destroy']], 'can_access_settings', ['settings.campaigns']),
        $row('settings.mfis', 'MFIs', 'MFI records and customer-name visibility', ['view' => ['mfis.index'], 'add' => ['mfis.store'], 'edit' => ['mfis.update'], 'delete' => ['mfis.destroy']], 'can_access_settings', ['settings.campaigns']),
    ]],
    'reports' => ['label' => 'Reports', 'icon' => 'fa-chart-line', 'modules' => [
        $row('reports.sales', 'Daily Sales & Remittance Report', 'Daily sales and remittance details', ['view' => ['dsr', 'reports.daily.export']], 'can_access_reports'),
        $row('reports.monthly', 'Monthly Sales Report', 'Monthly sales and exports', ['view' => ['monthly-sales', 'monthly-sales.export']], 'can_access_reports', ['reports.sales']),
        $row('reports.dpo', 'Distributor Purchase Order Report', 'Distributor purchase order activity', ['view' => ['dpo']], 'can_access_reports', ['reports.sales']),
        $row('reports.operations', 'Inventory Stock Level Report', 'Stock levels and exports', ['view' => ['isl', 'isl.export']], 'can_access_reports'),
        $row('reports.aging', 'Aging Report', 'Outstanding transactions and aging', ['view' => ['aging']], 'can_access_reports', ['reports.operations']),
        $row('reports.voucher_history', 'Voucher History Report', 'Voucher redemption history', ['view' => ['voucher-history', 'voucher-history.export']], 'can_access_reports', ['reports.operations']),
        $row('reports.other_charges', 'Distributor Other Charges Report', 'Other charges and discounts by distributor', ['view' => ['reports.distributor-other-charges']], 'can_access_reports', ['reports.operations']),
        $row('reports.sedp', 'Sign Up Incentives Report', 'Sign-ups, clients, and incentives', ['view' => ['signup-incentives', 'signup-incentives.clients', 'signup-incentives.export', 'signup-incentives.ad-export']], 'can_access_reports'),
        $row('reports.awarded_areas', 'AD Awarded Areas Report', 'Awarded areas, project affiliations, and package amounts', ['view' => ['ad-awarded-areas', 'ad-awarded-areas.export']], 'can_access_reports', ['reports.sedp']),
        $row('reports.repeat_purchase', 'Repeat Purchase Incentives Report', 'Repeat purchase incentives and transactions', ['view' => ['repeat-purchase-incentives', 'repeat-purchase-incentives.transactions', 'repeat-purchase-incentives.export']], 'can_access_reports', ['reports.sedp']),
        $row('reports.center_chief', 'Center Chief Incentive Report', 'Refill incentives and customer transactions', ['view' => ['center-chief-incentives', 'center-chief-incentives.transactions', 'center-chief-incentives.export']], 'can_access_reports', ['reports.sedp']),
        $row('reports.dealer_aging', 'Aging Report - Dealer', 'Dealer balances and transaction aging', ['view' => ['aging-report-dealer']], 'can_access_reports', ['reports.sedp']),
        $row('reports.customer_aging', 'Aging Report - Customer', 'Customer balances and transaction aging', ['view' => ['aging-report-customer']], 'can_access_reports', ['reports.sedp']),
        $row('reports.pull_out', 'Pull Out Request Report', 'Replacement progress and distributor confirmation', ['view' => ['pull-out-report', 'pull-out-report.data']], null, ['pull_out_requests.approvals']),
        $row('reports.return_refund', 'Return & Refund Report', 'Return documents, receiving, and refund progress', ['view' => ['return-refund-report', 'return-refund-report.data']], null, ['return_refunds.requests']),
    ]],
    'forms' => ['label' => 'Forms', 'icon' => 'fa-file-alt', 'modules' => [
        $row('forms.access_request', 'Access Request Form', 'External access request form', ['view' => []]) + ['url' => 'https://docs.google.com/forms/d/e/1FAIpQLScJ3kuD2UiWAMXyvQe3r4AZHlvY39N5_2bz68jY868uqjCxyQ/viewform?usp=send_form'],
        $row('forms.incident', 'Incident Report', 'External incident report form', ['view' => []]) + ['url' => 'https://docs.google.com/forms/d/e/1FAIpQLSdlOeSHBVOcAASbWkVOQpeVNbI1R36oVlEln-BctX7ekDlUnw/viewform'],
        $row('forms.borrowing', 'Borrowing Marketing Collateral Form', 'External collateral borrowing form', ['view' => []]) + ['url' => 'https://form.jotform.com/241338243724050'],
        $row('forms.layout', 'Layout Design Request Form', 'External design request form', ['view' => []]) + ['url' => 'https://form.jotform.com/240291186323048'],
        $row('forms.referral', 'Area Distributor Referral Sheet', 'External distributor referral form', ['view' => []]) + ['url' => 'https://docs.google.com/forms/d/e/1FAIpQLScDxCoJdMlFDfr12wcrFcG2K7F1djvSJl3JKs-aAozqAp8bww/viewform'],
        $row('forms.application', 'Area Distributor Application', 'External distributor application form', ['view' => []]) + ['url' => 'https://www.jotform.com/form/233230867016048#preview'],
        $row('forms.training', 'Repair Training Sign Up', 'External repair training registration', ['view' => []]) + ['url' => 'https://docs.google.com/forms/d/e/1FAIpQLSe_o7mnARbbttceB-vlm5P7_HjHZ1QGFKgq9AGIMZTRwbixgA/viewform'],
    ]],
];
