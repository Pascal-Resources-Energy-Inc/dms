@extends('layouts.header')
<style>
    .content-area:has(.welcome-client) {
        margin-top: 90px !important;
    }

    .welcome {
        margin-top: auto !important;
    }

    .transaction-page {
        display: grid;
        gap: 16px;
    }
    .transaction-summary-card {
        min-height: 126px;
        border: 0;
        border-radius: 8px !important;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .08);
    }
    .transaction-summary-card .card-body {
        display: grid;
        align-content: space-between;
        min-height: 126px;
    }
    .transaction-panel {
        border: 1px solid #e6e9ef;
        border-radius: 8px !important;
        box-shadow: 0 10px 26px rgba(15, 23, 42, .06);
        overflow: hidden;
    }
    .transaction-panel .card-body {
        padding: 18px;
    }
    .transaction-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 14px;
    }
    .transaction-toolbar-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }
    .transaction-toolbar .btn,
    .export-btn-custom {
        min-height: 38px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 800;
    }
    .search-name-responsive {
        width: 180px !important;
    }
    .transaction-table {
        margin: 0 !important;
    }
    .transaction-table th {
        color: #667085;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .04em;
        text-align: center;
        text-transform: uppercase;
        background: #f8fafc;
        border-bottom: 1px solid #edf0f5 !important;
        white-space: nowrap;
    }
    .transaction-table td {
        color: #344054;
        font-size: 13px;
        vertical-align: middle;
        border-color: #f1f3f6;
    }
    .transaction-table tbody tr:hover {
        background: #f8fbff;
    }
    .transaction-table .form-check-input,
    .transaction-table input[type="checkbox"] {
        cursor: pointer;
    }
    .dataTables_wrapper {
        display: grid;
        gap: 12px;
    }
    .dataTables_wrapper .row:first-child,
    .dataTables_wrapper .row:last-child {
        align-items: center;
        row-gap: 10px;
    }
    .dataTables_wrapper .dataTables_length label,
    .dataTables_wrapper .dataTables_filter label,
    .dataTables_wrapper .dataTables_info {
        color: #667085;
        font-size: 12px;
        font-weight: 700;
    }
    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select {
        min-height: 36px;
        border: 1px solid #dfe4ea;
        border-radius: 8px;
        box-shadow: none;
    }
    .dataTables_wrapper .dataTables_filter input {
        min-width: 230px;
        margin-left: 8px;
        padding: 6px 10px;
    }
    .dataTables_wrapper .dataTables_length select {
        padding: 4px 28px 4px 8px;
    }
    .dataTables_wrapper .pagination {
        justify-content: flex-end;
        gap: 4px;
        margin: 0;
    }
    .dataTables_wrapper .page-link {
        min-width: 34px;
        color: #475467;
        font-size: 12px;
        text-align: center;
        border-color: #dfe4ea;
        border-radius: 7px !important;
    }
    .dataTables_wrapper .page-item.active .page-link {
        color: #fff;
        background: #5BC2E7;
        border-color: #5BC2E7;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:focus,
    .dataTables_wrapper .page-link:focus {
        box-shadow: none;
        outline: none;
    }
    @media (max-width: 768px) {
        .modal-dialog {
            margin: 1rem;
            max-width: 100%;
        }
        .transaction-toolbar {
            align-items: stretch;
            flex-direction: column;
        }
        .transaction-toolbar-actions,
        .transaction-toolbar-actions .btn,
        #exportExcelContainer,
        #exportExcelContainer .btn {
            width: 100%;
        }
        .search-name-responsive {
            width: 100% !important;
        }
        .dataTables_wrapper .dataTables_filter input {
            min-width: 0;
            width: 100%;
            margin: 6px 0 0;
        }
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            text-align: left;
        }
        .dataTables_wrapper .pagination {
            justify-content: flex-start;
        }
    }
</style>
<link rel="stylesheet" href="{{ asset('design/vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection
@section('content')
<div class="welcome transaction-page @if(auth()->user()->role === 'Dealer') welcome-client @endif">
    <div class="row">
        <!-- Cards Section - All 4 cards in one row -->
        <div class="col-12">
            <div class="row mb-0 cards">
                <!-- Total Sales -->
                  <div class="col-sm-6 col-lg-4 col-xl-2">
                    <div class="card transaction-summary-card warning-card overflow-hidden text-bg-primary w-100">
                        <div class="card-body p-4">
                          <div class="mb-7">
                            <i class="ti ti-brand-producthunt fs-8 fw-lighter"></i>
                          </div>
                          <h5 class="text-white fw-bold fs-14 text-nowrap">
                          {{ $transactions->sum(function($transaction) {
                                return $transaction->price * $transaction->qty;
                            }) }}
                          </h5>
                          <p class="opacity-50 mb-0 ">TOTAL SALES</p>
                        </div>
                    </div>
                </div>
                  <div class="col-sm-6 col-lg-4 col-xl-2">
                    <div class="card transaction-summary-card danger-card overflow-hidden text-bg-primary w-100">
                        <div class="card-body p-4">
                          <div class="mb-7">
                            <i class="ti ti-brand-producthunt fs-8 fw-lighter"></i>
                          </div>
                          <h5 class="text-white fw-bold fs-14 text-nowrap">
                            {{$transactions->count()}}
                          </h5>
                          <p class="opacity-50 mb-0 ">TRANSACTIONS</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4 col-xl-2">
                    <div class="card transaction-summary-card info-card overflow-hidden text-bg-primary w-100">
                        <div class="card-body p-4">
                          <div class="mb-7">
                            <i class="ti ti-brand-producthunt fs-8 fw-lighter"></i>
                          </div>
                          <h5 class="text-white fw-bold fs-14 text-nowrap">
                            {{$transactions->sum('qty')}}
                          </h5>
                          <p class="opacity-50 mb-0 ">QTY SOLD</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4 col-xl-2">
                    <div class="card transaction-summary-card info-card overflow-hidden text-bg-primary w-100">
                        <div class="card-body p-4">
                          <div class="mb-7">
                            <i class="ti ti-brand-producthunt fs-8 fw-lighter"></i>
                          </div>
                          <h5 class="text-white fw-bold fs-14 text-nowrap">
                            {{$transactions->sum('points_dealer') + $transactions->sum('points_client')}}
                          </h5>
                          <p class="opacity-50 mb-0 ">TOTAL POINTS</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12">
            <div class="card transaction-panel w-100">  
                <div class="card-body">
                  <div class="transaction-toolbar">
                    <h5 class="mb-0 fw-bold">Transactions</h5>
                    <div class="transaction-toolbar-actions">
                      @if(auth()->user()->role == "Admin")
                          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTransactionModalAdmin">
                              <i class="bi bi-plus-lg"></i> Search Name
                          </button>
                          <div id="exportExcelContainer"></div>
                          <button type="button" class="btn btn-danger btn-sm" id="deleteSelectedBtn" title="Delete Selected" style="display: none; height: 38px;">
                              <i class="bi bi-trash"></i> Delete All
                          </button>
                      @else
                          <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#qrScannerModal">
                              Scan QR
                          </button>
                          <button type="button" class="btn btn-primary search-name-responsive" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
                              <i class="bi bi-plus-lg"></i> Search Name
                          </button>
                      @endif
                    </div>
                  </div>

                  <div class="table-responsive">
                      <table class="table table-hover align-middle transaction-table" id="example" style="width:100%">
                          <thead class="table-light">
                              <tr>
                                  @if(auth()->user()->role == "Admin" && auth()->user()->can_delete === "on")
                                      <th scope="col" style="width: 50px; text-align: center;">
                                          <div class="d-flex align-items-center justify-content-center">
                                              <input type="checkbox" id="selectAll" title="Select All" style="cursor: pointer;">
                                          </div>
                                      </th>
                                  @endif
                                  <th scope="col">ID</th>
                                  <th scope="col">Date</th>
                                  <th scope="col">Quantity</th>
                                  <th scope="col">Amount</th>
                                  <th scope="col">Dealer</th>
                                  <th scope="col">Customer</th>
                                  <th scope="col">Dealer Points</th>
                                  <th scope="col">Customer Points</th>
                                  <th scope="col">Item</th>
                                  @if(auth()->user()->role == "Admin" && auth()->user()->can_delete === "on")
                                      <th scope="col" style="width: 80px; text-align: center;">Actions</th>
                                  @endif
                              </tr>
                          </thead>
                          <tbody id="transactionBody">
                              @foreach($transactions as $transaction)
                                  <tr id="transaction-row-{{$transaction->id}}">
                                      @if(auth()->user()->role == "Admin" && auth()->user()->can_delete === "on")
                                          <td style="text-align: center;">
                                              <input type="checkbox" class="checkbox-item" data-id="{{$transaction->id}}" style="cursor: pointer;">
                                          </td>
                                      @endif
                                      <td>{{$transaction->id}}</td>
                                      <td>{{ date('M d, Y', strtotime($transaction->date)) }}</td>
                                      <td>{{ number_format($transaction->qty, 2) }}</td>
                                      <td>{{ number_format($transaction->qty * $transaction->price, 2) }}</td>
                                      <td>{{ $transaction->dealer->name ?? '' }}</td>
                                      <td>{{ $transaction->customer->name ?? '' }}</td>
                                      <td><span class='text-success'>{{ $transaction->points_dealer }}</span></td>
                                      <td><span class='text-success'>{{ $transaction->points_client }}</span></td>
                                      <td>{{ strtoupper($transaction->item) }}</td>
                                      @if(auth()->user()->role == "Admin" && auth()->user()->can_delete === "on")
                                          <td style="text-align: center;">
                                              <button type="button" class="btn btn-danger btn-sm delete-single" 
                                                      data-id="{{ $transaction->id }}" 
                                                      title="Delete"
                                                      style="cursor: pointer;">
                                                  <i class="bi bi-trash"></i>
                                              </button>
                                          </td>
                                      @endif
                                  </tr>
                              @endforeach
                          </tbody>
                      </table>
                  </div>
                </div>
            </div>
        </div>
    </div>
</section>

@if(auth()->user()->role == "Admin")
  @include('new_transaction_admin')
@else
  @include('new_transaction')
@endif
@include('qr_scanner')

@endsection

@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- <script>
 $(document).ready(function() {
  $('#customerSelect').select2({
    dropdownParent: $('#addTransactionModal') // ✅ replace with your modal's ID
  });
  $('#customerSelect123').select2({
    dropdownParent: $('#addTransactionModalAdmin') // ✅ replace with your modal's ID
  });
  $('#dealer').select2({
    dropdownParent: $('#addTransactionModalAdmin') // ✅ replace with your modal's ID
  });
});
</script> --}}
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (window.TomSelect && document.querySelector('#customerSelect')) {
      new TomSelect('#customerSelect', {
        create: false,
        allowEmptyOption: true,
        placeholder: "Search Customer"
      });
    }

    if (window.TomSelect && document.querySelector('#dealer')) {
      new TomSelect('#dealer', {
        create: false,
        allowEmptyOption: true,
        placeholder: "Search Dealer"
      });
    }
  });
</script>
<script>
(async function() {
    if (!window.jQuery) {
        return;
    }

    function loadScript(src) {
        return new Promise(function(resolve, reject) {
            if (document.querySelector('script[src="' + src + '"]')) {
                resolve();
                return;
            }

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

        var sources = [
            "{{ asset('design/assets/libs/datatables.net/js/jquery.dataTables.min.js') }}",
            "{{ asset('design/vendors/datatables.net/jquery.dataTables.js') }}"
        ];

        for (var i = 0; i < sources.length; i++) {
            try {
                await loadScript(sources[i]);
                if ($.fn && $.fn.DataTable) {
                    break;
                }
            } catch (error) {
                console.warn('Unable to load DataTables from:', sources[i]);
            }
        }

        if (!$.fn || !$.fn.DataTable) {
            return false;
        }

        try {
            await loadScript("{{ asset('design/vendors/datatables.net-bs4/dataTables.bootstrap4.js') }}");
        } catch (error) {
            console.warn('DataTables Bootstrap integration did not load. Continuing with core DataTables.');
        }

        return true;
    }

$(async function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    });

    if (!await ensureDataTables()) {
        console.error('DataTables failed to load.');
        return;
    }

    if ($.fn.DataTable.isDataTable('#example')) {
        $('#example').DataTable().destroy();
    }

    var actionColumnIndex = $('#example thead th').length - 1;
    var hasSelectColumn = $('#selectAll').length > 0;
    var orderColumnIndex = hasSelectColumn ? 1 : 0;
    var disabledOrderTargets = hasSelectColumn ? [0, actionColumnIndex] : [];

    const table = $('#example').DataTable({
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        autoWidth: false,
        responsive: false,
        columnDefs: [
            { 
                orderable: false, 
                targets: disabledOrderTargets
            },
            {
                className: 'text-center',
                targets: hasSelectColumn ? [0] : []
            }
        ],
        destroy: true,
        order: [[orderColumnIndex, 'desc']],
        language: {
            search: 'Search transactions:',
            searchPlaceholder: 'ID, dealer, customer, item...',
            lengthMenu: 'Show _MENU_ records',
            info: 'Showing _START_ to _END_ of _TOTAL_ transactions',
            infoEmpty: 'No transactions to show',
            zeroRecords: 'No matching transactions found.',
            paginate: {
                previous: 'Previous',
                next: 'Next'
            }
        }
    });

    var $exportContainer = $('#exportExcelContainer');
    if ($exportContainer.length) {
        $('<button type="button" class="btn btn-sm btn-success export-btn-custom"><i class="bi bi-file-earmark-spreadsheet"></i> Export CSV</button>')
            .appendTo($exportContainer)
            .on('click', function() {
                var rows = table.rows({ search: 'applied' }).data().toArray();
                var headers = $('#example thead th').map(function() {
                    return $(this).text().trim();
                }).get();
                var excludedIndexes = hasSelectColumn ? [0, headers.length - 1] : [];
                var visibleHeaders = headers.filter(function(_, index) {
                    return excludedIndexes.indexOf(index) === -1;
                });

                var csvRows = rows.map(function(row) {
                    return row.filter(function(_, index) {
                        return excludedIndexes.indexOf(index) === -1;
                    }).map(function(cell) {
                        return '"' + $('<div>').html(cell).text().trim().replace(/"/g, '""') + '"';
                    }).join(',');
                });

                var csv = visibleHeaders.map(function(header) {
                    return '"' + header.replace(/"/g, '""') + '"';
                }).join(',') + '\n' + csvRows.join('\n');
                var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                var link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'transactions.csv';
                link.click();
                URL.revokeObjectURL(link.href);
            });
    }

    $(document).on('change', '.checkbox-item', function() {
        updateUI();
    });

    $(document).on('change', '#selectAll', function() {
        const isChecked = $(this).prop('checked');
        $('.checkbox-item').prop('checked', isChecked);
        updateUI();
    });

    $(document).on('click', '#deleteSelectedBtn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        performBulkDelete();
    });

    $(document).on('click', '.delete-single', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const transactionId = $(this).data('id');
        performSingleDelete(transactionId);
    });

    function updateUI() {
        const $checkboxes = $('.checkbox-item');
        const $checked = $('.checkbox-item:checked');
        const checkedCount = $checked.length;
        const totalCount = $checkboxes.length;
        
        if (checkedCount > 0) {
            $('#deleteSelectedBtn').show();
        } else {
            $('#deleteSelectedBtn').hide();
        }
        
        const $selectAll = $('#selectAll');
        if (checkedCount === totalCount && totalCount > 0) {
            $selectAll.prop('checked', true);
            $selectAll.prop('indeterminate', false);
        } else if (checkedCount > 0) {
            $selectAll.prop('checked', false);
            $selectAll.prop('indeterminate', true);
        } else {
            $selectAll.prop('checked', false);
            $selectAll.prop('indeterminate', false);
        }
    }

    function performSingleDelete(transactionId) {
        if (!transactionId || isNaN(transactionId)) {
            Swal.fire('Error!', 'Invalid transaction ID', 'error');
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: 'This transaction will be permanently deleted!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Deleting...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const deleteUrl = `{{ url('/transactions') }}/${transactionId}`;

                $.ajax({
                    url: deleteUrl,
                    type: 'POST',
                    data: {
                        _token: csrfToken,
                        _method: 'DELETE'
                    },
                    success: function(response) {
                        Swal.fire('Deleted!', response.success || 'Transaction deleted successfully', 'success').then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr, status, error) {
                        let message = 'An error occurred while deleting the transaction';
                        
                        if (xhr.status === 404) {
                            message = 'Route not found. Please check your routes configuration.';
                        } else if (xhr.status === 405) {
                            message = 'Method not allowed. Check if the route accepts DELETE method.';
                        } else if (xhr.status === 403) {
                            message = 'You are not authorized to delete this transaction.';
                        } else if (xhr.status === 500) {
                            message = 'Server error. Please check the server logs.';
                        }
                        
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.error) {
                                message = response.error;
                            } else if (response.message) {
                                message = response.message;
                            }
                        } catch (e) {
                            // Use default message
                        }
                        
                        Swal.fire('Error!', message, 'error');
                    }
                });
            }
        });
    }

    function performBulkDelete() {
        const selectedIds = $('.checkbox-item:checked').map(function() {
            return parseInt($(this).data('id'));
        }).get();

        if (selectedIds.length === 0) {
            Swal.fire('No Selection', 'Please select at least one transaction to delete.', 'warning');
            return;
        }

        const invalidIds = selectedIds.filter(id => isNaN(id) || id <= 0);
        if (invalidIds.length > 0) {
            Swal.fire('Error!', 'Some transaction IDs are invalid', 'error');
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete ${selectedIds.length} transaction(s). This cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete them!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait while we delete the selected transactions.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const bulkDeleteUrl = '{{ url("/transactions/bulk-delete") }}';

                $.ajax({
                    url: bulkDeleteUrl,
                    type: 'POST',
                    data: {
                        ids: selectedIds,
                        _token: csrfToken
                    },
                    success: function(response) {
                        Swal.fire('Deleted!', response.success || 'Transactions deleted successfully', 'success').then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr, status, error) {
                        let message = 'An error occurred while deleting the transactions';
                        
                        if (xhr.status === 404) {
                            message = 'Route not found. Please check your routes configuration.';
                        } else if (xhr.status === 405) {
                            message = 'Method not allowed. Check if the route accepts POST method.';
                        } else if (xhr.status === 403) {
                            message = 'You are not authorized to delete these transactions.';
                        } else if (xhr.status === 500) {
                            message = 'Server error. Please check the server logs.';
                        }
                        
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.error) {
                                message = response.error;
                            } else if (response.message) {
                                message = response.message;
                            }
                        } catch (e) {
                            // Use default message
                        }
                        
                        Swal.fire('Error!', message, 'error');
                    }
                });
            }
        });
    }

    setTimeout(function() {
        updateUI();
    }, 100);
});
})();
</script>

<script>
 let html5QrcodeScanner = null;

function startScanner() {
    if (!html5QrcodeScanner) {
        document.getElementById("reader").innerHTML = "";
        html5QrcodeScanner = new Html5Qrcode("reader");
    }

    const config = { fps: 10, qrbox: 250 };

    html5QrcodeScanner.start(
        { facingMode: "environment" }, 
        config,
        qrCodeMessage => {
            document.getElementById('result').innerText = qrCodeMessage;
            fetchUserInfo(qrCodeMessage);
            html5QrcodeScanner.stop();
        },
        errorMessage => {
            // optional: handle scanning errors
        }
    ).catch(err => {
        console.error("Unable to start scanning.", err);
    });
}

function stopScanner() {
    if (html5QrcodeScanner) {
        html5QrcodeScanner.stop().then(() => {
            html5QrcodeScanner.clear();
            html5QrcodeScanner = null;
        }).catch(err => {
            console.warn("Failed to stop scanner", err);
            html5QrcodeScanner = null;
        });
    }
}

function fetchUserInfo(userId) {
      fetch(`{{ url('get-user') }}/${userId}`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('userId').value = data.user.id;
            document.getElementById('userName').value = data.user.name;

            var qrModal = bootstrap.Modal.getInstance(document.getElementById('qrScannerModal'));
            qrModal.hide();
            var transactionModal = new bootstrap.Modal(document.getElementById('addTransactionModaldd'));
            transactionModal.show();
        } else {
            alert("User not found");
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error fetching user info:', error);
        alert("Error fetching user info");
    });
}

document.getElementById('qrScannerModal').addEventListener('shown.bs.modal', startScanner);
document.getElementById('qrScannerModal').addEventListener('hidden.bs.modal', stopScanner);
</script>

@endsection
