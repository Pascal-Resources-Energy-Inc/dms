@extends('layouts.header')
<link rel="icon" type="image/png" href="{{ asset('images/logo_nya.png') }}">

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">

<style>
    .customer-page {
        padding-bottom: 32px;
    }

    .customer-head {
        border: 1px solid #e2edf4;
        border-radius: 8px;
        padding: 22px;
        margin-bottom: 18px;
        background:
            linear-gradient(135deg, rgba(47, 155, 215, .08), rgba(22, 120, 180, .04)),
            #fff;
        box-shadow: 0 18px 45px rgba(19, 47, 69, .06);
    }

    .customer-eyebrow {
        color: #2f9bd7;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .customer-stat-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }

    .customer-stat {
        border: 1px solid #e2edf4;
        border-radius: 8px;
        padding: 16px;
        background: #fff;
        box-shadow: 0 16px 38px rgba(19, 47, 69, .06);
    }

    .customer-stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        color: #1678b4;
        background: #e8f6fc;
        font-size: 22px;
    }

    .customer-stat.is-active .customer-stat-icon {
        color: #166534;
        background: #dcfce7;
    }

    .customer-stat.is-inactive .customer-stat-icon {
        color: #991b1b;
        background: #fee2e2;
    }

    .customer-stat strong {
        display: block;
        color: #0f172a;
        font-size: 24px;
        font-weight: 900;
        line-height: 1;
    }

    .customer-stat span {
        display: block;
        margin-top: 7px;
        color: #64748b;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .customer-table-card {
        border: 1px solid #e2edf4;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 18px 45px rgba(19, 47, 69, .07);
        overflow: hidden;
    }

    .customer-table-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 18px 20px;
        border-bottom: 1px solid #e8eef4;
    }

    .customer-table-title {
        margin: 0;
        color: #0f172a;
        font-size: 17px;
        font-weight: 900;
    }

    .customer-table-count {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 12px;
    }

    .customer-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .customer-tab {
        min-height: 38px;
        border: 1px solid #dbe7ef;
        border-radius: 8px;
        padding: 8px 12px;
        color: #475569;
        background: #fff;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .customer-tab.active {
        border-color: #2f9bd7;
        color: #1678b4;
        background: #e8f6fc;
    }

    .customer-tab-count {
        min-width: 24px;
        border-radius: 999px;
        padding: 2px 7px;
        color: #64748b;
        background: #eef2f7;
        font-size: 11px;
        text-align: center;
    }

    .customer-tab.active .customer-tab-count {
        color: #fff;
        background: #2f9bd7;
    }

    .customer-table {
        margin-bottom: 0;
        color: #1f2937;
        font-size: 12px;
    }

    .customer-table thead th {
        border-top: 0;
        border-bottom: 1px solid #dbe7ef;
        color: #475569;
        background: #f8fafc;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .customer-table tbody td {
        vertical-align: middle;
        border-color: #edf2f7;
    }

    .customer-table tbody tr:hover {
        background: #f8fbff;
    }

    .customer-ref,
    .customer-source,
    .customer-points {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .customer-ref {
        color: #1d4ed8;
        background: #dbeafe;
    }

    .customer-source {
        color: #075985;
        background: #e0f2fe;
    }

    .customer-source.is-genesis {
        color: #6d28d9;
        background: #ede9fe;
    }

    .customer-source.is-regular {
        color: #166534;
        background: #dcfce7;
    }

    .customer-points {
        color: #0f766e;
        background: #ccfbf1;
    }

    .customer-link {
        color: #0f172a;
        font-weight: 900;
        text-decoration: none;
    }

    .customer-link:hover {
        color: #1678b4;
        text-decoration: none;
    }

    .customer-muted {
        max-width: 280px;
        color: #64748b;
        line-height: 1.45;
    }

    .customer-status {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 900;
    }

    .customer-status.is-active {
        color: #166534;
        background: #dcfce7;
    }

    .customer-status.is-inactive {
        color: #991b1b;
        background: #fee2e2;
    }

    .customer-action-btn {
        min-width: 34px;
        min-height: 34px;
        border: 1px solid #dbe2ea;
        border-radius: 8px;
        padding: 7px 10px;
        background: #fff;
        color: #2563eb;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 900;
        transition: background-color .18s ease, border-color .18s ease, color .18s ease;
    }

    .customer-action-btn:hover {
        border-color: #2563eb;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .customer-info-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: linear-gradient(135deg, #f8fbff 0%, #eef6ff 100%);
        margin-bottom: 16px;
    }

    .customer-info-person {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }

    .customer-info-avatar {
        width: 54px;
        height: 54px;
        border-radius: 8px;
        background: #2563eb;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        font-size: 20px;
        font-weight: 900;
    }

    .customer-info-title {
        margin: 0;
        color: #111827;
        font-size: 20px;
        font-weight: 900;
        line-height: 1.2;
    }

    .customer-info-subtitle {
        margin-top: 5px;
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
    }

    .customer-info-badges {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 8px;
    }

    .customer-info-badge {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .customer-info-badge.is-source {
        color: #1d4ed8;
        background: #dbeafe;
    }

    .customer-info-badge.is-status-active {
        color: #166534;
        background: #dcfce7;
    }

    .customer-info-badge.is-status-inactive {
        color: #991b1b;
        background: #fee2e2;
    }

    .customer-info-section {
        margin-top: 16px;
    }

    .customer-info-section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 10px;
        color: #334155;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .customer-info-section-title::before {
        content: "";
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #2563eb;
    }

    .customer-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .customer-info-item {
        border: 1px solid #edf0f5;
        border-radius: 8px;
        background: #f8fafc;
        padding: 11px 12px;
    }

    .customer-info-item span {
        display: block;
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .customer-info-item strong {
        display: block;
        margin-top: 4px;
        color: #111827;
        font-size: 13px;
        word-break: break-word;
    }

    .customer-info-item.is-wide {
        grid-column: 1 / -1;
    }

    .customer-modal-close {
        width: 36px;
        height: 36px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        opacity: 1;
        text-shadow: none;
    }

    .customer-empty {
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
        margin: 18px;
        padding: 34px 18px;
        color: #64748b;
        text-align: center;
        background: #f8fafc;
    }

    .dataTables_wrapper {
        padding: 0 18px 18px;
    }

    .dataTables_wrapper .row:first-child {
        align-items: center;
        padding-top: 14px;
    }

    .dataTables_filter,
    .dataTables_length {
        margin: 0 0 12px;
    }

    .dataTables_filter label,
    .dataTables_length label,
    .dataTables_info {
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
    }

    .dataTables_filter input,
    .dataTables_length select {
        border: 1px solid #dbe7ef;
        border-radius: 8px;
        color: #0f172a;
        font-size: 13px;
        outline: none;
        box-shadow: none;
    }

    .dataTables_filter input:focus,
    .dataTables_length select:focus {
        border-color: #2f9bd7;
        box-shadow: 0 0 0 3px rgba(47, 155, 215, .14);
    }

    .dataTables_paginate .pagination {
        gap: 5px;
        margin: 0;
    }

    .dataTables_paginate .page-link {
        border: 1px solid #dbe7ef;
        border-radius: 8px !important;
        color: #1678b4;
        font-size: 12px;
        font-weight: 900;
    }

    .dataTables_paginate .page-item.active .page-link {
        border-color: #2f9bd7;
        background: #2f9bd7;
        color: #fff;
    }

    @media (max-width: 991px) {
        .customer-stat-grid {
            grid-template-columns: 1fr;
        }

        .customer-table-head {
            display: block;
        }

        .customer-table-head .btn {
            margin-top: 12px;
        }

        .customer-info-hero {
            align-items: flex-start;
            flex-direction: column;
        }

        .customer-info-badges {
            justify-content: flex-start;
        }

        .customer-info-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
@php
    $isAdminCustomerPage = auth()->user()->role === 'Admin';
    $adminCrmCustomers = $adminCrmCustomers ?? collect();
    $adminCrm2Customers = $adminCrm2Customers ?? collect();
    $regularCustomers = $regularCustomers ?? collect();
    $totalCustomers = $customers->count();

    if ($isAdminCustomerPage) {
        $customerTabs = collect([
            ['key' => 'Regular', 'label' => 'Regular', 'icon' => 'ti ti-users', 'count' => $regularCustomers->count()],
            ['key' => 'admin_crms', 'label' => 'Project Rise', 'icon' => 'ti ti-database', 'count' => $adminCrmCustomers->count()],
            ['key' => 'admin_crms2', 'label' => 'Project Genesis', 'icon' => 'ti ti-database-export', 'count' => $adminCrm2Customers->count()],
        ]);
    } else {
        $customerTabs = collect([
            ['key' => 'All', 'label' => 'All Customers', 'icon' => 'ti ti-users', 'count' => $totalCustomers],
            ['key' => 'Active', 'label' => 'Active', 'icon' => 'ti ti-user-check', 'count' => $activeCustomers],
            ['key' => 'Inactive', 'label' => 'Inactive', 'icon' => 'ti ti-user-x', 'count' => $inactiveCustomers],
        ]);
    }

    $initialCustomerTab = $customerTabs->firstWhere('count', '>', 0) ?: $customerTabs->first();
    $initialCustomerKey = $initialCustomerTab['key'] ?? 'All';
    $initialCustomerLabel = $initialCustomerTab['label'] ?? 'All Customers';
    $initialCustomerCount = $initialCustomerTab['count'] ?? 0;
@endphp

<section class="customer-page">
    <div class="customer-head">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <div class="customer-eyebrow">Customer Network</div>
                <h4 class="mb-1 mt-2">Customers</h4>
                <div class="text-muted">Monitor customer status, serials, territories, and reward points.</div>
            </div>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#new_customer">
                <i class="ti ti-plus"></i> Add Customer
            </button>
        </div>
    </div>

    <div class="customer-stat-grid">
        <div class="customer-stat">
            <div class="customer-stat-icon"><i class="ti ti-users"></i></div>
            <strong>{{ number_format($totalCustomers) }}</strong>
            <span>Total Customers</span>
        </div>
        <div class="customer-stat is-active">
            <div class="customer-stat-icon"><i class="ti ti-user-check"></i></div>
            <strong>{{ number_format($activeCustomers) }}</strong>
            <span>Active Customers</span>
        </div>
        <div class="customer-stat is-inactive">
            <div class="customer-stat-icon"><i class="ti ti-user-x"></i></div>
            <strong>{{ number_format($inactiveCustomers) }}</strong>
            <span>Inactive Customers</span>
        </div>
    </div>

    <div class="customer-table-card">
        <div class="customer-table-head">
            <div>
                <h5 class="customer-table-title" id="customerTableTitle">{{ $initialCustomerLabel }}</h5>
                <p class="customer-table-count" id="customerTableCount">{{ number_format($initialCustomerCount) }} records listed</p>
            </div>
            <div class="customer-tabs mt-3 mt-lg-0" role="tablist" aria-label="Customer filters">
                @foreach($customerTabs as $tab)
                    <button type="button"
                        class="customer-tab {{ $tab['key'] === $initialCustomerKey ? 'active' : '' }}"
                        data-customer-tab="{{ $tab['key'] }}"
                        data-customer-label="{{ $tab['label'] }}"
                        data-count="{{ $tab['count'] }}"
                        role="tab"
                        aria-selected="{{ $tab['key'] === $initialCustomerKey ? 'true' : 'false' }}">
                        <i class="{{ $tab['icon'] }}"></i>
                        {{ $tab['label'] }}
                        <span class="customer-tab-count">{{ number_format($tab['count']) }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <div id="customerEmptyState" class="customer-empty" style="display: {{ $customers->count() ? 'none' : 'block' }};">
            <strong>No customers found</strong>
            <span class="d-block mt-1">Switch tabs or add a customer to populate this list.</span>
        </div>

        @if($customers->count())
            <div id="customerTableWrap" class="table-responsive">
                <table id="customerTable" class="table customer-table transaction-table" style="width:100%">
                    <thead>
                        <tr>
                            <th>Customer Reference</th>
                            <th>Customer Name</th>
                            <th>Contact Number</th>
                            <th>Email Address</th>
                            <th>Serial Number</th>
                            <th>Address</th>
                            <th>Total Points</th>
                            <th>Center</th>
                            <th>SPO</th>
                            <th>Status</th>
                            <th>View</th>
                        </tr>
                    </thead>
                    <tbody id="customerBody">
                        @foreach($customers as $customer)
                            @php
                                $customerTabKey = $isAdminCustomerPage ? ($customer->source ?? 'Regular') : 'All';
                                $isRemoteCustomer = $isAdminCustomerPage && $customerTabKey !== 'Regular';
                                $sourceLabel = $customer->source_label ?? ($customerTabKey === 'Regular' ? 'Regular' : $customerTabKey);
                                $sourceClass = $customerTabKey === 'admin_crms2' ? 'is-genesis' : ($customerTabKey === 'Regular' ? 'is-regular' : '');
                                $firstTransaction = $customer->transactions->sortBy('date')->first();
                                $address = strtoupper(trim(implode(', ', array_filter([
                                    $customer->street_address,
                                    $customer->location_barangay,
                                    $customer->location_city,
                                    $customer->location_province,
                                ])) . ' ' . $customer->postal_code));
                                $status = $customer->status == 'Active' ? 'Active' : 'Inactive';
                                $remarks = '';
                                $customerName = $customer->name ?: '-';
                                $serialNumber = $customer->serial && $customer->serial->serial_number ? $customer->serial->serial_number : '-';
                                $points = $customer->transactions->sum('points_client');
                                $customerViewUrl = $isRemoteCustomer
                                    ? url('admin-crm-customer/' . $customerTabKey . '/' . $customer->id)
                                    : url('view-client/' . $customer->id);

                                if ($customer->serial && !empty($customer->serial->remarks)) {
                                    $previousOwner = \App\Client::find($customer->serial->remarks);
                                    $remarks = 'SN# ' . $customer->serial->serial_number . ' used to be owned by ' . ($previousOwner ? $previousOwner->name : 'Unknown Client');
                                }

                                $customerInfo = [
                                    'source' => $sourceLabel,
                                    'reference' => $customer->client_reference ?: '-',
                                    'name' => $customerName,
                                    'initials' => collect(explode(' ', $customerName))->filter()->map(function ($part) { return strtoupper(substr($part, 0, 1)); })->take(2)->implode(''),
                                    'number' => $customer->number ?: '-',
                                    'email' => $customer->email_address ?: '-',
                                    'serial' => $serialNumber,
                                    'points' => number_format($points),
                                    'center' => $customer->center ?: '-',
                                    'spo' => $customer->spo ?: '-',
                                    'address' => $address ?: '-',
                                    'region' => $customer->location_region ?: '-',
                                    'province' => $customer->location_province ?: '-',
                                    'city' => $customer->location_city ?: '-',
                                    'barangay' => $customer->location_barangay ?: '-',
                                    'first_transaction' => $firstTransaction ? date('M d, Y', strtotime($firstTransaction->date)) : 'No Data',
                                    'remarks' => $remarks ?: '-',
                                    'status' => $status,
                                ];
                            @endphp
                            <tr data-customer-tab-key="{{ $customerTabKey }}" data-customer-status="{{ $status }}">
                                <td><span class="customer-ref">{{ strtoupper($customer->client_reference) }}</span></td>
                                <td>
                                    <a href="{{ $customerViewUrl }}" class="customer-link">
                                        {{ strtoupper($customer->name) }}
                                    </a>
                                </td>
                                <td>{{ $customer->number ?: '-' }}</td>
                                <td>{{ strtoupper($customer->email_address ?: '-') }}</td>
                                <td>{{ $serialNumber }}</td>
                                <td><div class="customer-muted">{{ $address ?: '-' }}</div></td>
                                <td><span class="customer-points">{{ number_format($points) }}</span></td>
                                <td>{{ strtoupper($customer->center ?: '-') }}</td>
                                <td>{{ strtoupper($customer->spo ?: '-') }}</td>
                                <td>
                                    <span class="customer-status {{ $status === 'Active' ? 'is-active' : 'is-inactive' }}">
                                        {{ $status }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ $customerViewUrl }}"
                                        class="customer-action-btn"
                                        title="View customer">
                                        <i class="ti ti-eye"></i>
                                        {{-- <span>View Info</span> --}}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="customer-empty">
                <strong>No customers found</strong>
                <span class="d-block mt-1">Add a customer to populate this list.</span>
            </div>
        @endif
    </div>
</section>

<div class="modal fade" id="customerInfoModal" tabindex="-1" aria-labelledby="customerInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="customerInfoModalLabel">Customer Information</h5>
                    <div class="small text-muted">Complete customer profile and source details</div>
                </div>
                <button type="button" class="close customer-modal-close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="customer-info-hero">
                    <div class="customer-info-person">
                        <div class="customer-info-avatar" id="customerInfoInitials">CL</div>
                        <div>
                            <h4 class="customer-info-title" id="customerInfoName">-</h4>
                            <div class="customer-info-subtitle">
                                <span id="customerInfoReference">-</span>
                                <span class="mx-1">/</span>
                                <span id="customerInfoSerial">-</span>
                            </div>
                        </div>
                    </div>
                    <div class="customer-info-badges">
                        <span class="customer-info-badge is-source" id="customerInfoSource">Source</span>
                        <span class="customer-info-badge" id="customerInfoStatus">Status</span>
                    </div>
                </div>

                <div class="customer-info-section">
                    <h6 class="customer-info-section-title">Account Details</h6>
                    <div class="customer-info-grid">
                        <div class="customer-info-item"><span>CRM Source</span><strong id="customerInfoSourceDetail">-</strong></div>
                        <div class="customer-info-item"><span>Serial Number</span><strong id="customerInfoSerialDetail">-</strong></div>
                        <div class="customer-info-item"><span>Contact Number</span><strong id="customerInfoNumber">-</strong></div>
                        <div class="customer-info-item"><span>Email Address</span><strong id="customerInfoEmail">-</strong></div>
                        <div class="customer-info-item"><span>Total Points</span><strong id="customerInfoPoints">-</strong></div>
                        <div class="customer-info-item"><span>First Transaction</span><strong id="customerInfoFirstTransaction">-</strong></div>
                        <div class="customer-info-item"><span>Center</span><strong id="customerInfoCenter">-</strong></div>
                        <div class="customer-info-item"><span>SPO</span><strong id="customerInfoSpo">-</strong></div>
                    </div>
                </div>

                <div class="customer-info-section">
                    <h6 class="customer-info-section-title">Location</h6>
                    <div class="customer-info-grid">
                        <div class="customer-info-item"><span>Region</span><strong id="customerInfoRegion">-</strong></div>
                        <div class="customer-info-item"><span>Province</span><strong id="customerInfoProvince">-</strong></div>
                        <div class="customer-info-item"><span>City / Municipality</span><strong id="customerInfoCity">-</strong></div>
                        <div class="customer-info-item"><span>Barangay</span><strong id="customerInfoBarangay">-</strong></div>
                        <div class="customer-info-item is-wide"><span>Address</span><strong id="customerInfoAddress">-</strong></div>
                        <div class="customer-info-item is-wide"><span>Remarks</span><strong id="customerInfoRemarks">-</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@include('new_customer')

@section('javascript')
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(async function() {
    const $customerTable = $('#customerTable');
    const isAdminCustomerPage = {{ $isAdminCustomerPage ? 'true' : 'false' }};
    const initialCustomerKey = "{{ $initialCustomerKey }}";

    function loadCustomerScript(src) {
        return new Promise(function(resolve, reject) {
            if (!src) {
                reject();
                return;
            }

            const existingScript = document.querySelector('script[src="' + src + '"]');

            if (existingScript) {
                resolve();
                return;
            }

            const script = document.createElement('script');
            script.src = src;
            script.async = true;
            script.onload = function() {
                script.dataset.loaded = 'true';
                resolve();
            };
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    async function ensureCustomerDataTables() {
        if ($.fn && $.fn.DataTable) {
            return true;
        }

        const dataTableSources = [
            "{{ asset('design/assets/libs/datatables.net/js/jquery.dataTables.min.js') }}",
            "{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}",
            "https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"
        ];

        for (let i = 0; i < dataTableSources.length; i++) {
            try {
                await loadCustomerScript(dataTableSources[i]);

                if ($.fn && $.fn.DataTable) {
                    break;
                }
            } catch (error) {
                // Try the next source.
            }
        }

        if (!($.fn && $.fn.DataTable)) {
            return false;
        }

        if (!$.fn.dataTable || !$.fn.dataTable.ext) {
            return false;
        }

        try {
            await loadCustomerScript("{{ asset('design/assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}");
        } catch (error) {
            try {
                await loadCustomerScript("https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js");
            } catch (fallbackError) {
                // Core DataTables can still paginate without the Bootstrap adapter.
            }
        }

        return true;
    }

    if ($customerTable.length) {
        let activeCustomerTab = $('.customer-tab.active').data('customer-tab') || initialCustomerKey;
        let customerTable = null;
        const $customerTableBody = $customerTable.find('tbody');
        const allCustomerRows = $customerTableBody.find('tr').detach().toArray();

        function customerRowMatchesActiveTab(row) {
            if (isAdminCustomerPage) {
                return row.getAttribute('data-customer-tab-key') === activeCustomerTab;
            }

            return activeCustomerTab === 'All' || row.getAttribute('data-customer-status') === activeCustomerTab;
        }

        function matchingCustomerRows() {
            return allCustomerRows.filter(customerRowMatchesActiveTab);
        }

        function updateCustomerHeader(label, count) {
            $('#customerTableTitle').text(label);
            $('#customerTableCount').text(
                count.toLocaleString() + ' record' + (count === 1 ? '' : 's') + ' listed'
            );
        }

        function setCustomerEmptyState(label, count) {
            const hasRows = count > 0;

            $('#customerTableWrap').toggle(hasRows);
            $('#customerEmptyState')
                .toggle(!hasRows)
                .find('strong')
                .text(hasRows ? 'No customers found' : 'No ' + String(label).toLowerCase() + ' customers found');
            $('#customerEmptyState span').text(
                hasRows ? '' : 'Switch tabs or add a customer to populate this list.'
            );
        }

        function activateCustomerTab($tab) {
            activeCustomerTab = $tab.data('customer-tab');
            const label = $tab.data('customer-label') || activeCustomerTab;
            const count = matchingCustomerRows().length;

            $('.customer-tab')
                .removeClass('active')
                .attr('aria-selected', 'false');

            $tab
                .addClass('active')
                .attr('aria-selected', 'true');

            updateCustomerHeader(label, count);
            setCustomerEmptyState(label, count);

            return count;
        }

        function renderCustomerRows() {
            const rows = matchingCustomerRows();

            if (customerTable) {
                customerTable.clear();
                customerTable.rows.add($(rows)).search('').page('first').draw();
                return;
            }

            $customerTableBody.empty().append(rows);
        }

        $customerTableBody.empty().append(matchingCustomerRows());

        if (await ensureCustomerDataTables()) {
            try {
                customerTable = $customerTable.DataTable({
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                    paging: true,
                    searching: true,
                    info: true,
                    deferRender: true,
                    autoWidth: false,
                    columnDefs: [
                        { targets: [10], orderable: false, searchable: false }
                    ],
                    order: [[0, 'asc']],
                    language: {
                        search: 'Search customers:',
                        lengthMenu: 'Show _MENU_ records',
                        info: 'Showing _START_ to _END_ of _TOTAL_ customers',
                        infoEmpty: 'No customers to show',
                        zeroRecords: 'No matching customers found.',
                        emptyTable: 'No customers found.',
                        paginate: {
                            previous: 'Previous',
                            next: 'Next'
                        }
                    }
                });
            } catch (error) {
                console.warn('Customer DataTables failed to initialize. Using basic table tabs.', error);
                customerTable = null;
            }
        } else {
            console.warn('Customer DataTables failed to load. Using basic table tabs.');
        }

        function showCustomerInfo(customer) {
            const status = customer.status || '-';
            const isActive = String(status).toLowerCase() === 'active';

            $('#customerInfoInitials').text(customer.initials || 'CL');
            $('#customerInfoName').text(customer.name || '-');
            $('#customerInfoReference').text(customer.reference || '-');
            $('#customerInfoSerial').text(customer.serial || '-');
            $('#customerInfoSource').text(customer.source || 'Local');
            $('#customerInfoSourceDetail').text(customer.source || 'Local');
            $('#customerInfoSerialDetail').text(customer.serial || '-');
            $('#customerInfoStatus')
                .text(status)
                .removeClass('is-status-active is-status-inactive')
                .addClass(isActive ? 'is-status-active' : 'is-status-inactive');
            $('#customerInfoNumber').text(customer.number || '-');
            $('#customerInfoEmail').text(customer.email || '-');
            $('#customerInfoPoints').text(customer.points || '0');
            $('#customerInfoFirstTransaction').text(customer.first_transaction || '-');
            $('#customerInfoCenter').text(customer.center || '-');
            $('#customerInfoSpo').text(customer.spo || '-');
            $('#customerInfoRegion').text(customer.region || '-');
            $('#customerInfoProvince').text(customer.province || '-');
            $('#customerInfoCity').text(customer.city || '-');
            $('#customerInfoBarangay').text(customer.barangay || '-');
            $('#customerInfoAddress').text(customer.address || '-');
            $('#customerInfoRemarks').text(customer.remarks || '-');

            const modalElement = document.getElementById('customerInfoModal');

            if (window.bootstrap && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(modalElement).show();
            } else {
                $('#customerInfoModal').modal('show');
            }
        }

        $(document).on('click', '.js-view-customer', function() {
            let customer = {};

            try {
                customer = JSON.parse($(this).attr('data-customer') || '{}');
            } catch (error) {
                customer = {};
            }

            showCustomerInfo(customer);
        });

        $('.customer-tab').on('click', function() {
            activateCustomerTab($(this));
            renderCustomerRows();
        });

        setCustomerEmptyState($('.customer-tab.active').data('customer-label') || activeCustomerTab, matchingCustomerRows().length);
    }

    $('#new_customer').on('shown.bs.modal', function () {
        if (typeof map === 'undefined' || !map) {
            initMap();
        } else {
            setTimeout(function() {
                map.invalidateSize();
            }, 200);
        }
    });

    if ($.fn.chosen) {
        $('.chosen-select').chosen({
            width: '100%'
        });
    } else if ($.fn.select2) {
        $('.chosen-select').select2({
            width: '100%',
            dropdownParent: $('#new_customer').length ? $('#new_customer') : $(document.body)
        });
    }
});
</script>
@endsection
