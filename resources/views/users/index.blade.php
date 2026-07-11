@extends('layouts.header')
<link rel="icon" type="image/png" href="{{asset('images/logo_nya.png')}}">
@section('css')
<style>
    .users-page {
        padding-bottom: 32px;
    }

    .users-hero {
        border: 1px solid #e2edf4;
        border-radius: 8px;
        padding: 22px;
        margin-bottom: 18px;
        background:
            linear-gradient(135deg, rgba(47, 155, 215, .09), rgba(22, 120, 180, .04)),
            #fff;
        box-shadow: 0 18px 45px rgba(19, 47, 69, .06);
    }

    .users-eyebrow {
        margin-bottom: 6px;
        color: #2f9bd7;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .users-title {
        margin: 0;
        color: #0f172a;
        font-size: 24px;
        font-weight: 900;
    }

    .users-subtitle {
        margin: 7px 0 0;
        color: #64748b;
        font-size: 14px;
        line-height: 1.5;
    }

    .users-add-btn {
        min-height: 42px;
        border: 0;
        border-radius: 8px;
        padding: 10px 15px;
        background: #16a34a;
        color: #fff;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 900;
        box-shadow: 0 14px 30px rgba(22, 163, 74, .22);
    }

    .users-add-btn:hover {
        background: #15803d;
        color: #fff;
    }

    .users-table-card {
        border: 1px solid #e2edf4;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 18px 45px rgba(19, 47, 69, .07);
        overflow: hidden;
    }

    .users-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 18px 20px;
        border-bottom: 1px solid #e8eef4;
    }

    .users-toolbar-title {
        margin: 0;
        color: #0f172a;
        font-size: 17px;
        font-weight: 900;
    }

    .users-toolbar-copy {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 12px;
    }

    .role-filter {
        min-width: 230px;
    }

    .role-filter label {
        display: block;
        margin: 0 0 6px;
        color: #64748b;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .custom-select-container {
        position: relative;
    }

    .custom-select-container::after {
        content: "";
        position: absolute;
        right: 14px;
        top: 50%;
        width: 8px;
        height: 8px;
        border-right: 2px solid #64748b;
        border-bottom: 2px solid #64748b;
        transform: translateY(-65%) rotate(45deg);
        pointer-events: none;
    }

    .custom-dropdown {
        height: 42px;
        border: 1px solid #dbe7ef;
        border-radius: 8px;
        padding: 0 38px 0 13px;
        background: #fff;
        color: #0f172a;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        font-size: 13px;
        font-weight: 800;
        box-shadow: none;
    }

    .custom-dropdown:focus {
        border-color: #2f9bd7;
        box-shadow: 0 0 0 3px rgba(47, 155, 215, .14);
    }

    .users-table-wrap {
        padding: 0 18px 18px;
    }

    .users-table {
        margin-bottom: 0;
        color: #1f2937;
        font-size: 12px;
    }

    .users-table thead th {
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

    .users-table tbody td {
        vertical-align: middle;
        border-color: #edf2f7;
    }

    .users-table tbody tr:hover {
        background: #f8fbff;
    }

    .user-pill {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .02em;
        white-space: nowrap;
    }

    .user-pill.is-admin {
        color: #075985;
        background: #e0f2fe;
    }

    .user-pill.is-client {
        color: #166534;
        background: #dcfce7;
    }

    .user-pill.is-dealer {
        color: #6d28d9;
        background: #ede9fe;
    }

    .user-pill.is-distributor {
        color: #92400e;
        background: #fef3c7;
    }

    .user-pill.is-active {
        color: #166534;
        background: #dcfce7;
    }

    .user-pill.is-inactive {
        color: #991b1b;
        background: #fee2e2;
    }

    .user-pill.is-muted {
        color: #475569;
        background: #e2e8f0;
    }

    .users-table td:nth-child(6) {
        white-space: nowrap;
        text-align: center;
    }

    .action-buttons {
        display: inline-flex;
        justify-content: center;
        gap: 6px;
    }

    .btn-custom {
        width: 34px;
        height: 34px;
        border: 1px solid #dbe2ea;
        border-radius: 8px;
        padding: 0;
        background: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: background-color .18s ease, border-color .18s ease, color .18s ease;
    }

    .btn-view-custom {
        color: #2563eb;
    }

    .btn-view-custom:hover {
        border-color: #2563eb;
        background: #eff6ff;
        color: #1d4ed8 !important;
    }

    .btn-edit-custom {
        color: #dc2626;
    }

    .btn-edit-custom:hover {
        border-color: #dc2626;
        background: #fef2f2;
        color: #b91c1c !important;
    }

    .btn-access-custom {
        color: #16a34a;
    }

    .btn-access-custom:hover {
        border-color: #16a34a;
        background: #f0fdf4;
        color: #15803d !important;
    }

    .dataTables_wrapper {
        padding-top: 14px;
    }

    .dataTables_wrapper .row:first-child {
        align-items: center;
    }

    .dataTables_filter,
    .dataTables_length {
        margin-bottom: 12px;
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

    @media (max-width: 768px) {
        .users-toolbar {
            display: block;
        }

        .role-filter {
            min-width: 0;
            margin-top: 14px;
        }

        .users-hero .d-flex {
            align-items: flex-start !important;
        }
    }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css">
<link rel="stylesheet" href="{{ asset('design/vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

@endsection
@section('content')
@php
    $currentUser = auth()->user();
    $canShowAddAdmin = $currentUser && in_array($currentUser->role, ['Admin', 'SEDP'], true) && $currentUser->can_add === 'on';
@endphp

<section class="users-page">
    <div class="users-hero">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
            <div>
                <div class="users-eyebrow">Access Management</div>
                <h4 class="users-title">Users</h4>
                <p class="users-subtitle">Manage admin access, distributors, dealers, clients, permissions, and role-based visibility.</p>
            </div>

            @if($canShowAddAdmin)
                <button class="users-add-btn mt-3 mt-lg-0" type="button" data-bs-toggle="modal" data-bs-target="#new_users">
                    <i class="fas fa-plus"></i>
                    <span>Add Users</span>
                </button>
            @endif
        </div>
    </div>

    <div class="users-table-card">
        <div class="users-toolbar">
            <div>
                <h5 class="users-toolbar-title">User Directory</h5>
                <p class="users-toolbar-copy">Filter by role, search records, and open profile or access controls.</p>
            </div>

            <div class="role-filter">
                <label for="roleFilter">Role Filter</label>
                <div class="custom-select-container">
                    <select id="roleFilter" class="form-control custom-dropdown">
                        <option value="">All Roles</option>
                        <option value="Admin">Admin</option>
                        <option value="SEDP">SEDP</option>
                        <option value="Dealer">Dealer</option>
                        <option value="Client">Client</option>
                        <option value="Provincial Distributor">Provincial Distributor</option>
                        <option value="Area Distributor">Area Distributor</option>
                        <option value="Mega Dealer">Mega Dealer</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="users-table-wrap">
            <div class="table-responsive">
                <table id="example" class="table users-table" style="width:100%">
                        <thead>
                            <tr>
                                <th scope="col" width="20%">Name</th>
                                <th scope="col" width="20%">Email</th>
                                <th scope="col" width="25%">Address</th>
                                <th scope="col" width="15%">Status</th>
                                <th scope="col" width="10%">Role</th>
                                <th scope="col" width="10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</section>

@include('new_admin')
@include('users.create')
@include('edit_users')
@include('admin-privillege')
@endsection


@section('javascript')
<script>
    $(document).ready(async function () {
        function loadScript(src) {
            return new Promise(function (resolve, reject) {
                var script = document.createElement('script');
                script.src = src;
                script.async = false;
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }

        async function ensureDataTables() {
            if ($.fn && $.fn.DataTable) {
                return true;
            }

            var dataTableSources = [
                "{{ asset('design/assets/libs/datatables.net/js/jquery.dataTables.min.js') }}",
                "{{ asset('design/vendors/datatables.net/jquery.dataTables.js') }}",
                "https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"
            ];

            for (var i = 0; i < dataTableSources.length; i++) {
                try {
                    await loadScript(dataTableSources[i]);

                    if ($.fn && $.fn.DataTable) {
                        break;
                    }
                } catch (error) {
                    console.warn('Unable to load DataTables from:', dataTableSources[i]);
                }
            }

            if (!$.fn || !$.fn.DataTable) {
                return false;
            }

            try {
                await loadScript("{{ asset('design/vendors/datatables.net-bs4/dataTables.bootstrap4.js') }}");
            } catch (error) {
                console.warn('DataTables Bootstrap styling script did not load. Continuing with core DataTables.');
            }

            return true;
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        if (!await ensureDataTables()) {
            console.error('DataTables failed to load from local assets and CDN fallback.');
            return;
        }

        if ($.fn.DataTable.isDataTable('#example')) {
            $('#example').DataTable().destroy();
        }
        var table = $('#example').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            autoWidth: false,
            order: [[0, 'asc']],
            ajax: {
                url: "{{ route('users.data') }}",
                data: function (d) {
                    d.role = $('#roleFilter').val();
                },
                error: function (xhr) {
                    console.log(xhr.responseText);
                }
            },
            columns: [
                { data: 'name', searchable: true },
                { data: 'email', searchable: true },
                { data: 'address', searchable: true },
                { data: 'status', searchable: true },
                { data: 'role', searchable: true },
                { data: 'actions', orderable: false, searchable: false }
            ],
            language: {
                search: 'Search users:',
                lengthMenu: 'Show _MENU_ records',
                info: 'Showing _START_ to _END_ of _TOTAL_ users',
                infoEmpty: 'No users to show',
                zeroRecords: 'No matching users found.',
                processing: 'Loading users...'
            }
        });

        $('#roleFilter').on('change', function () {
            table.ajax.reload();
        });

        function showModal(selector) {
            const element = document.querySelector(selector);

            if (!element) {
                return;
            }

            if (window.bootstrap && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(element).show();
                return;
            }

            $(selector).modal('show');
        }

        function hideModal(selector) {
            const element = document.querySelector(selector);

            if (!element) {
                return;
            }

            if (window.bootstrap && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(element).hide();
                return;
            }

            $(selector).modal('hide');
        }

        function showAccessMessage(title, message, type) {
            if (window.Swal) {
                Swal.fire(title, message, type);
                return;
            }

            alert(message);
        }

        function userInitials(name) {
            const words = (name || '').trim().split(/\s+/).filter(Boolean);

            if (!words.length) {
                return '--';
            }

            return words.slice(0, 2).map(function (word) {
                return word.charAt(0).toUpperCase();
            }).join('');
        }

        function setAccessLoading(isLoading) {
            const button = $('#saveAccess');
            const label = button.find('span');

            button.prop('disabled', isLoading);
            label.text(isLoading ? 'Saving...' : 'Save Access');
        }

        const accessFields = [
            'can_edit',
            'can_add',
            'can_delete',
            'can_edit_rewards',
            'can_add_rewards',
            'can_delete_rewards',
            'can_access_transactions',
            'can_access_distributors',
            'can_access_dealers',
            'can_access_customers',
            'can_access_purchase_orders',
            'can_access_inventory',
            'can_access_reports',
            'can_access_settings'
        ];

        function setAccessFieldsFromResponse(res) {
            accessFields.forEach(function (field) {
                $('#' + field).prop('checked', res[field] === 'on');
            });

            $('.access-permission-check').prop('checked', false);

            if (res.access_permissions && Object.keys(res.access_permissions).length) {
                Object.keys(res.access_permissions).forEach(function (module) {
                    Object.keys(res.access_permissions[module] || {}).forEach(function (submodule) {
                        (res.access_permissions[module][submodule] || []).forEach(function (action) {
                            $('.access-permission-check[data-module="' + module + '"][data-submodule="' + submodule + '"][data-action="' + action + '"]').prop('checked', true);
                        });
                    });
                });
                return;
            }

            $('#can_add').prop('checked', res.can_add === 'on');
            $('#can_edit').prop('checked', res.can_edit === 'on');
            $('#can_delete').prop('checked', res.can_delete === 'on');
            $('#can_add_rewards').prop('checked', res.can_add_rewards === 'on');
            $('#can_edit_rewards').prop('checked', res.can_edit_rewards === 'on');
            $('#can_delete_rewards').prop('checked', res.can_delete_rewards === 'on');
            setLegacyModuleView('transactions', res.can_access_transactions);
            setLegacyModuleView('distributors', res.can_access_distributors);
            setLegacyModuleView('dealers', res.can_access_dealers);
            setLegacyModuleView('customers', res.can_access_customers);
            setLegacyModuleView('purchase_orders', res.can_access_purchase_orders);
            setLegacyModuleView('inventory', res.can_access_inventory);
            setLegacyModuleView('reports', res.can_access_reports);
            setLegacyModuleView('settings', res.can_access_settings);
        }

        function setLegacyModuleView(module, value) {
            if (value !== 'on') {
                return;
            }

            $('.access-permission-check[data-module="' + module + '"][data-action="view"]').prop('checked', true);
        }

        function getDetailedAccessPermissions() {
            const permissions = {};

            $('.access-permission-check:checked').each(function () {
                const module = $(this).data('module');
                const submodule = $(this).data('submodule');
                const action = $(this).data('action');

                if (!permissions[module]) {
                    permissions[module] = {};
                }

                if (!permissions[module][submodule]) {
                    permissions[module][submodule] = [];
                }

                permissions[module][submodule].push(action);
            });

            return permissions;
        }

        function getAccessPayload() {
            const payload = {
                id: $('#access_user_id').val(),
                access_permissions: JSON.stringify(getDetailedAccessPermissions())
            };

            accessFields.forEach(function (field) {
                payload[field] = $('#' + field).is(':checked') ? 'on' : 'off';
            });

            return payload;
        }

        $('#checkAllAccess').on('click', function () {
            $('.access-permission-check').prop('checked', true);
        });

        $('#clearAllAccess').on('click', function () {
            $('.access-permission-check').prop('checked', false);
        });

        $(document).on('change', '.access-permission-check', function () {
            const action = $(this).data('action');

            if (!this.checked || action === 'view') {
                return;
            }

            $('.access-permission-check[data-module="' + $(this).data('module') + '"][data-submodule="' + $(this).data('submodule') + '"][data-action="view"]').prop('checked', true);
        });

        $(document).on('click', '.btn-edit-user', function () {

            let id = $(this).data('id');

            $.get('/users/' + id + '/show', function (res) {

                $('#edit_user_id').val(res.id);
                $('#edit_name').val(res.name);
                $('#edit_email').val(res.email);
                $('#edit_address').val(res.address);

                showModal('#editUserModal');
            });
        });

        $('#saveUser').click(function () {

            $.post('/users/update', {
                id: $('#edit_user_id').val(),
                name: $('#edit_name').val(),
                email: $('#edit_email').val(),
                address: $('#edit_address').val()
            }, function (res) {

                if (res.success) {
                    hideModal('#editUserModal');
                    $('#example').DataTable().ajax.reload(null, false);
                } else {
                    alert(res.message);
                }
            });
        });

        $(document).on('click', '.btn-access-user', function () {

            let id = $(this).data('id');

            $.get('/users/' + id + '/show', function (res) {

                $('#access_user_id').val(res.id);
                $('#accessUserName').text(res.name || 'Unnamed user');
                $('#accessUserEmail').text(res.email || 'No email');
                $('#accessUserRole').text(res.role || 'Role');
                $('#accessUserInitials').text(userInitials(res.name));

                setAccessFieldsFromResponse(res);

                showModal('#accessUserModal');
            }).fail(function () {
                showAccessMessage('Error', 'Unable to load user access.', 'error');
            });
        });

        $('#saveAccess').click(function () {
            setAccessLoading(true);

            $.post('/users/access-update', getAccessPayload(), function (res) {

                if (res.success) {
                    hideModal('#accessUserModal');
                    $('#example').DataTable().ajax.reload(null, false);
                    showAccessMessage('Saved', res.message || 'User access updated successfully.', 'success');
                } else {
                    showAccessMessage('Error', res.message || 'Unable to update user access.', 'error');
                }
            }).fail(function (xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Unable to update user access.';

                showAccessMessage('Error', message, 'error');
            }).always(function () {
                setAccessLoading(false);
            });
        });

    });
    function toggleFieldRequired() {
        const contact = document.getElementById("contact_number");
        const facebook = document.getElementById("facebook");
        const contactMark = document.getElementById("contactRequiredMark");
        const facebookMark = document.getElementById("facebookRequiredMark");

        if (!contact || !facebook) return;

        const hasContact = contact.value.trim() !== "";
        const hasFacebook = facebook.value.trim() !== "";

        // Contact entered: Facebook is not required.
        if (hasContact) {
            facebook.removeAttribute("required");
            if (facebookMark) facebookMark.style.display = "none";
        } else {
            facebook.setAttribute("required", "required");
            if (facebookMark) facebookMark.style.display = "inline";
        }

        // Facebook entered: contact number is not required.
        if (hasFacebook) {
            contact.removeAttribute("required");
            if (contactMark) contactMark.style.display = "none";
        } else {
            contact.setAttribute("required", "required");
            if (contactMark) contactMark.style.display = "inline";
        }
    }
</script>

<script>
    $(function () {

        const roleFilter = $('#roleFilter2');

        const businessFields = $('.business-fields');
        const projectTagFields = $('.project-tag-fields');
        const attachmentFields = $('.attachment-field');
        const adminFields = $('.admin-fields');
        const adminRequiredFields = $('.admin-required');
        const adminOnlyFields = $('.admin-only-fields');
        const adminOnlyEmploymentFields = $('.admin-only-employment');
        const adminLikeRequiredFields = $('.admin-like-required');
        const adminOnlyRequiredFields = $('.admin-only-required');
        const adminDesignationWrapper = $('.admin-designation-wrapper');
        const adminDesignationField = $('.admin-designation-field');
        const sedpDesignationWrapper = $('.sedp-designation-wrapper');
        const sedpDesignationField = $('.sedp-designation-field');
        const sedpFields = $('.sedp-fields');
        const sedpCenterSelect = $('#sedp_center');
        const nonAdminPersonalFields = $('.non-admin-personal-fields');
        const locationFields = $('.location-fields');
        const nonAdminPersonalInputs = nonAdminPersonalFields.find('input, select, textarea');
        const locationInputs = locationFields.find('input, select, textarea');
        const distributorDeliveryFields = $('.distributor-delivery-fields');
        const distributorDeliveryRequiredFields = $('.distributor-delivery-required');
        const areaField = $('.project-area-field');

        const areaSelect = $('#area_name');
        const businessName = $('#business_name');
        const businessType = $('#business_type');
        const partnerCode = $('#store_code');
        const attachment = $('#attachment');

        const dynamicAreaWrapper = $('#dynamic-area-wrapper');
        const projectRows = $('#projectRows');
        const areaRoles = ['Provincial Distributor', 'Area Distributor', 'Mega Dealer'];

        function canHaveAwardedAreas(role) {
            return areaRoles.includes(role);
        }

        function hasSelectedProjectTag() {
            return $('input[name="type[]"]:checked').length > 0;
        }

        function clearSedpCenters() {
            sedpCenterSelect.val(null).trigger('change');
            sedpCenterSelect.removeClass('is-invalid');
            $('.sedp-center-feedback').removeClass('is-visible');
        }

        function clearProjectAreas() {
            projectRows.find('select').prop('required', false).val(null).trigger('change');
            projectRows.find('input[name="joining_date[]"]').prop('required', false).val('');
            projectRows.html('');
            dynamicAreaWrapper.hide();
        }

        function toggleProjectAreas() {
            $('.project-row').each(function () {
                $(this).find('.project-area').show();
                $(this).find('select[name="area_name[]"]').prop('required', true);
            });
        }

        function addProjectRow() {
            const template = document.querySelector('#project-row-template');
            const clone = template.content.cloneNode(true);

            const $row = $(clone).children('.project-row');
            projectRows.append($row);

            if (typeof initSelect2 === 'function') {
                initSelect2($row);
            }

            toggleProjectAreas();
        }

        $('#addProjectRow').on('click', function () {
            addProjectRow();
        });

        $(document).on('click', '.remove-row', function () {
            if ($('.project-row').length > 1) {
                $(this).closest('.project-row').remove();
            } else {
                alert('At least one row is required.');
            }
        });

        function refreshProjectVisibility() {

            const role = roleFilter.val();
            const canShowAreas = canHaveAwardedAreas(role);
            const hasProjectTag = hasSelectedProjectTag();

            if (!canShowAreas || !hasProjectTag) {
                clearProjectAreas();
                return;
            }

            dynamicAreaWrapper.show();

            if ($('.project-row').length === 0) {
                addProjectRow();
            }

            projectRows.find('input[name="joining_date[]"]').prop('required', true);
            toggleProjectAreas();
        }

        function toggleBusinessFields() {

            const selectedRole = roleFilter.val();

            if (!selectedRole) {
                businessFields.hide();
                attachmentFields.hide();
                adminFields.hide();
                adminOnlyFields.hide();
                adminOnlyEmploymentFields.hide();
                sedpFields.hide();
                nonAdminPersonalFields.show();
                locationFields.show();
                distributorDeliveryFields.hide();
                areaField.hide();

                businessName.prop('required', false);
                businessType.prop('required', false);
                partnerCode.prop('required', false);
                adminRequiredFields.prop('required', false);
                adminLikeRequiredFields.prop('required', false);
                adminOnlyRequiredFields.prop('required', false);
                adminDesignationWrapper.show();
                adminDesignationField.prop('disabled', false).prop('required', false).val('');
                sedpDesignationWrapper.hide();
                sedpDesignationField.prop('disabled', true).prop('required', false).val(null).trigger('change');
                nonAdminPersonalInputs.prop('disabled', false);
                locationInputs.prop('disabled', false);
                distributorDeliveryRequiredFields.prop('required', false);
                areaSelect.prop('required', false);
                attachment.prop('required', false);

                partnerCode.val('');
                $('input[name="warehouse"]').prop('checked', false);
                clearSedpCenters();
                $('#delivery_address').val('').prop('readonly', false);
                $('#same_as_address').prop('checked', false);
                $('#same_as_delivery_address').prop('checked', false);
                return;
            }

            const isAdmin = selectedRole === 'Admin';
            const isSedp = selectedRole === 'SEDP';
            const isAdminLike = isAdmin || isSedp;
            const isProvincialDistributor = selectedRole === 'Provincial Distributor';
            const isAreaDistributor = selectedRole === 'Area Distributor';
            const canShowAreas = canHaveAwardedAreas(selectedRole);
            const needsDeliveryAddress = isProvincialDistributor || isAreaDistributor;

            businessFields.toggle(!isAdminLike);
            projectTagFields.toggle(canShowAreas);
            attachmentFields.toggle(isProvincialDistributor || isAreaDistributor);
            adminFields.toggle(isAdminLike);
            adminOnlyFields.toggle(isAdmin);
            adminOnlyEmploymentFields.toggle(isAdmin);
            sedpFields.toggle(isSedp);
            if (isSedp && typeof window.ensureSedpCenterSelect2 === 'function') {
                setTimeout(window.ensureSedpCenterSelect2, 0);
            }
            nonAdminPersonalFields.toggle(!isAdminLike);
            locationFields.toggle(!isAdminLike);
            distributorDeliveryFields.toggle(needsDeliveryAddress);
            areaField.toggle(canShowAreas);

            businessName.prop('required', !isAdminLike);
            businessType.prop('required', !isAdminLike);
            partnerCode.prop('required', !isAdminLike);
            adminRequiredFields.prop('required', false);
            adminLikeRequiredFields.prop('required', isAdminLike);
            adminOnlyRequiredFields.prop('required', isAdmin);
            adminDesignationWrapper.toggle(isAdmin);
            sedpDesignationWrapper.toggle(isSedp);
            adminDesignationField.prop('disabled', !isAdmin).prop('required', isAdmin);
            sedpDesignationField.prop('disabled', !isSedp).prop('required', isSedp);
            nonAdminPersonalInputs.prop('disabled', isAdminLike);
            locationInputs.prop('disabled', isAdminLike);
            distributorDeliveryRequiredFields.prop('required', needsDeliveryAddress);

            if (isSedp && typeof initSelect2 === 'function') {
                initSelect2(sedpDesignationField.parent());
            }

            areaSelect.prop('required', canShowAreas);
            areaSelect.prop('disabled', !canShowAreas);

            attachment.prop('required', isProvincialDistributor || isAreaDistributor);

            if (!canShowAreas) {
                $('input[name="type[]"]').prop('checked', false);
                areaSelect.val(null).trigger('change');
                clearProjectAreas();
            }

            if (!isAdminLike) {
                $('input[name="warehouse"]').prop('checked', false);
                $('#same_as_address').prop('checked', false);
                adminRequiredFields.val('');
                adminDesignationField.val('');
                sedpDesignationField.val(null).trigger('change');
                clearSedpCenters();
            } else {
                nonAdminPersonalInputs.filter(':not([type="hidden"])').val('');
                locationInputs.filter(':not([type="hidden"])').val('');
                if (!isAdmin) {
                    $('input[name="warehouse"]').prop('checked', false);
                    adminOnlyRequiredFields.val('');
                    adminDesignationField.val('');
                }
                if (!isSedp) {
                    sedpDesignationField.val(null).trigger('change');
                    clearSedpCenters();
                }
                if (typeof toggleContactRequired === 'function') {
                    toggleContactRequired();
                }
            }

            if (!needsDeliveryAddress) {
                $('#delivery_address').val('').prop('readonly', false);
                $('#same_as_delivery_address').prop('checked', false);
            }

            if (!isAdminLike) {
                generatePartnerCode();
            }
        }

        function generatePartnerCode() {

            const role = roleFilter.val();

            if (!role || role === 'Admin' || role === 'SEDP') {
                partnerCode.val('');
                return;
            }

            $.ajax({
                url: "{{ route('generate.partner.code') }}",
                type: "POST",
                data: {
                    role: role,
                    _token: "{{ csrf_token() }}"
                },
                success: function (res) {
                    partnerCode.val(res.success ? res.code : '');
                },
                error: function () {
                    partnerCode.val('');
                }
            });
        }

        roleFilter.on('change', function () {
            toggleBusinessFields();
            refreshProjectVisibility();
        });

        $(document).on('change', 'input[name="type[]"]', function () {
            toggleBusinessFields();
            refreshProjectVisibility();
            toggleProjectAreas();
        });

        $('#new_users').on('shown.bs.modal', function () {
            toggleBusinessFields();
            refreshProjectVisibility();
        });

        toggleBusinessFields();
        refreshProjectVisibility();
        toggleProjectAreas();
        

    });
</script>
@endsection
