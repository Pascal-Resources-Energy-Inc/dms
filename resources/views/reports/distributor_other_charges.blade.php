@extends('layouts.header')

@section('content')
    <style>
        .charges-shell {
            max-width: 100%;
        }

        .charges-hero {
            background: linear-gradient(135deg, #fff8f1 0%, #eef6ff 100%);
            border: 1px solid #e7ebf3;
        }

        .charges-hero-icon {
            width: 52px;
            height: 52px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            background: linear-gradient(135deg, #ff8a3d 0%, #f04f4f 100%);
            color: #ffffff;
            font-size: 24px;
            box-shadow: 0 10px 24px rgba(240, 79, 79, 0.2);
        }

        .charges-stat-card {
            border: 1px solid #e7ebf3;
            border-radius: 18px;
            background: #ffffff;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .charges-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
        }

        .charges-stat-icon {
            width: 46px;
            height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            font-size: 20px;
            margin-bottom: 12px;
        }

        .charges-filter-card {
            border: 1px solid #e7ebf3;
            border-radius: 20px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
        }

        .charges-filter-card .form-control,
        .charges-filter-card .form-select {
            border-radius: 12px;
            border: 1px solid #dfe4ea;
            min-height: 44px;
        }

        .charges-filter-card .form-control:focus,
        .charges-filter-card .form-select:focus {
            border-color: #f04f4f;
            box-shadow: 0 0 0 0.2rem rgba(240, 79, 79, 0.15);
        }

        .charges-btn-primary {
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #f04f4f 0%, #ff8a3d 100%);
            color: #fff;
            box-shadow: 0 10px 24px rgba(240, 79, 79, 0.2);
        }

        .charges-btn-primary:hover {
            color: #fff;
            opacity: 0.95;
        }

        .charges-btn-outline {
            border-radius: 12px;
            border: 1px solid #dfe4ea;
            background: #fff;
        }

        .charges-table thead th {
            background: #f8fafc;
            color: #475467;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .charges-table tbody tr:hover {
            background: #fff8f1;
        }

        .charges-table td {
            vertical-align: middle;
        }

        .charges-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            background: #f8fafc;
            color: #475467;
        }

        .charges-subtle-card {
            border: 1px solid #e7ebf3;
            border-radius: 16px;
            background: linear-gradient(135deg, #ffffff 0%, #fcfdff 100%);
        }

        .charges-modal .modal-content {
            border-radius: 20px;
            border: 0;
        }
    </style>

    <div class="container-fluid py-4 charges-shell">
        <div class="card border-0 shadow-sm rounded-4 mb-4 charges-hero">
            <div class="card-body p-4 p-lg-5">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                    <div class="d-flex align-items-start gap-3">
                        <div class="charges-hero-icon">
                            <i class="ti ti-receipt"></i>
                        </div>
                        <div>
                            <h2 class="fw-bold mb-1 text-dark">Distributor Other Charges</h2>
                            <p class="text-muted mb-0">Review charges and drill down into the transactions where they were applied.</p>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="charges-pill"><i class="ti ti-chart-bar"></i> Performance insights</span>
                        <span class="charges-pill"><i class="ti ti-clock"></i> Filter-ready report</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card charges-stat-card p-3 h-100">
                    <div class="charges-stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="ti ti-coin"></i>
                    </div>
                    <small class="text-muted">Total Charges</small>
                    <h3 class="mt-2 mb-0 fw-bold">{{ number_format($summary['total']) }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card charges-stat-card p-3 h-100">
                    <div class="charges-stat-icon bg-success bg-opacity-10 text-success">
                        <i class="ti ti-checks"></i>
                    </div>
                    <small class="text-muted">Active Charges</small>
                    <h3 class="mt-2 mb-0 fw-bold">{{ number_format($summary['active']) }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card charges-stat-card p-3 h-100">
                    <div class="charges-stat-icon bg-info bg-opacity-10 text-info">
                        <i class="ti ti-arrows-right-left"></i>
                    </div>
                    <small class="text-muted">Fixed Charges</small>
                    <h3 class="mt-2 mb-0 fw-bold">{{ number_format($summary['fixed']) }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card charges-stat-card p-3 h-100">
                    <div class="charges-stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="ti ti-percentage"></i>
                    </div>
                    <small class="text-muted">Percentage / Discount</small>
                    <h3 class="mt-2 mb-0 fw-bold">{{ number_format($summary['percentage'] + $summary['discount']) }}</h3>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4 charges-filter-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="charges-stat-icon bg-danger bg-opacity-10 text-danger" style="width:40px;height:40px;font-size:18px;">
                        <i class="ti ti-filter"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">Filter charges</h5>
                        <small class="text-muted">Refine the report by distributor, date, status, and type.</small>
                    </div>
                </div>
                <form method="GET" action="{{ route('reports.distributor-other-charges') }}">
                    <div class="row g-3 align-items-end">
                        @if(auth()->user()->role === 'Admin')
                            <div class="col-lg-3">
                                <label class="form-label fw-semibold">Area Distributor</label>
                                <select name="ad_user_id" class="form-select form-select-lg">
                                    <option value="">All Area Distributors</option>
                                    @foreach($adUsers as $adUser)
                                        <option value="{{ $adUser->id }}" {{ request('ad_user_id') == $adUser->id ? 'selected' : '' }}>
                                            {{ $adUser->name }}
                                            @if(optional($adUser->ad)->business_name)
                                                - {{ optional($adUser->ad)->business_name }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-lg-2">
                            <label class="form-label fw-semibold">From</label>
                            <input type="date" name="from" class="form-control form-control-lg" value="{{ $from }}">
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label fw-semibold">To</label>
                            <input type="date" name="to" class="form-control form-control-lg" value="{{ $to }}">
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select form-select-lg">
                                <option value="">All</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label fw-semibold">Type</label>
                            <select name="type" class="form-select form-select-lg">
                                <option value="">All Types</option>
                                <option value="fixed" {{ request('type') === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                <option value="percentage" {{ request('type') === 'percentage' ? 'selected' : '' }}>Percentage</option>
                                <option value="discount" {{ request('type') === 'discount' ? 'selected' : '' }}>Discount</option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label fw-semibold">Search</label>
                            <input type="search" name="search" class="form-control form-control-lg" placeholder="Charge code, name, AD" value="{{ request('search') }}">
                        </div>
                        <div class="col-lg-2">
                            <button class="btn charges-btn-primary btn-lg w-100">
                                <i class="ti ti-filter me-1"></i>
                                Apply Filters
                            </button>
                        </div>
                        @if(request()->filled('charge_id'))
                            <input type="hidden" name="charge_id" value="{{ request('charge_id') }}">
                        @endif
                        @if(request()->filled('charge_id'))
                            <div class="col-lg-2">
                                <a href="{{ route('reports.distributor-other-charges') }}" class="btn charges-btn-outline btn-lg w-100">
                                    Clear
                                </a>
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row justify-content-between gap-2 align-items-md-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-1">Charge list</h5>
                        <p class="text-muted small mb-0">Select a charge to view the transactions tied to it.</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 charges-table">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Area Distributor</th>
                                <th>Type</th>
                                <th>Applies To</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($charges as $charge)
                                <tr>
                                    <td class="fw-semibold">{{ $charge->name }}</td>
                                    <td>{{ $charge->code }}</td>
                                    <td>{{ optional($charge->adUser)->name ?? 'N/A' }}</td>
                                    <td>{{ $charge->typeLabel() }}</td>
                                    <td>{{ $charge->appliesToLabel() }}</td>
                                    <td class="fw-semibold">{{ $charge->formattedAmount() }}</td>
                                    <td>
                                        <span class="badge rounded-pill bg-{{ $charge->is_active ? 'success' : 'secondary' }} bg-opacity-10 text-{{ $charge->is_active ? 'success' : 'secondary' }}">
                                            {{ $charge->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('reports.distributor-other-charges', array_merge(request()->except('page'), ['charge_id' => $charge->id])) }}" class="btn btn-sm btn-outline-primary">
                                            View Transactions
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No other charges found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if(request()->filled('charge_id') && !$selectedCharge)
            <div class="alert alert-warning rounded-4 border-0 shadow-sm">
                <strong>Charge not found.</strong> The selected charge may no longer exist or you do not have permission to view it.
            </div>
        @endif

        @if($selectedCharge)
            <div class="card border-0 shadow-sm rounded-4 mb-4 charges-subtle-card">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-start">
                        <div>
                            <h4 class="fw-bold mb-1">{{ $selectedCharge->name }}</h4>
                            <div class="small text-muted">{{ $selectedCharge->description }}</div>
                            <div class="small text-muted mt-1">Charge code: {{ $selectedCharge->code }}</div>
                        </div>
                        <div class="text-md-end">
                            <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary">{{ $selectedCharge->typeLabel() }}</span>
                            <span class="badge rounded-pill bg-info bg-opacity-10 text-info">Applies to {{ $selectedCharge->appliesToLabel() }}</span>
                        </div>
                    </div>

                    <div class="row row-cols-1 row-cols-md-4 g-3 mt-4">
                        <div class="col">
                            <div class="p-3 bg-light rounded-4 h-100">
                                <small class="text-muted">Transactions</small>
                                <div class="h4 mb-0 fw-bold">{{ number_format($transactionSummary['count']) }}</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-3 bg-light rounded-4 h-100">
                                <small class="text-muted">Total Charge Amount</small>
                                <div class="h4 mb-0 fw-bold">PHP {{ number_format($transactionSummary['total_charge'], 2) }}</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-3 bg-light rounded-4 h-100">
                                <small class="text-muted">Total Transaction Value</small>
                                <div class="h4 mb-0 fw-bold">PHP {{ number_format($transactionSummary['total_amount'], 2) }}</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-3 bg-light rounded-4 h-100">
                                <small class="text-muted">Unique Dealers</small>
                                <div class="h4 mb-0 fw-bold">{{ number_format($transactionSummary['dealers']) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold mb-1">Transaction breakdown</h5>
                            <p class="text-muted small mb-0">The charges applied for this selected item.</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 charges-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Transaction #</th>
                                    <th>Dealer</th>
                                    <th>Customer</th>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Delivery Fee</th>
                                    <th>Charge Amount</th>
                                    <th>Total</th>
                                    <th>Payment</th>
                                    <th class="text-end">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction['date'] }}</td>
                                        <td>{{ $transaction['transaction_id'] }}</td>
                                        <td>{{ $transaction['dealer'] }}</td>
                                        <td>{{ $transaction['customer'] }}</td>
                                        <td>{{ $transaction['item'] }}</td>
                                        <td>{{ number_format($transaction['qty']) }}</td>
                                        <td class="text-end">{{ $transaction['delivery_fee'] > 0 ? 'PHP ' . number_format($transaction['delivery_fee'], 2) : '-' }}</td>
                                        <td class="text-end">{{ $transaction['charge_amount'] >= 0 ? 'PHP ' . number_format($transaction['charge_amount'], 2) : '-PHP ' . number_format(abs($transaction['charge_amount']), 2) }}</td>
                                        <td class="text-end">PHP {{ number_format($transaction['total'], 2) }}</td>
                                        <td>{{ $transaction['payment_method'] }}</td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#transactionDetailsModal" data-transaction='@json($transaction)'>
                                                View
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center text-muted py-4">No transactions found for this charge.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal fade charges-modal" id="transactionDetailsModal" tabindex="-1" aria-labelledby="transactionDetailsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content rounded-4 border-0 shadow-sm">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold" id="transactionDetailsModalLabel">Transaction Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <dl class="row g-3">
                                <dt class="col-sm-4 text-muted">Transaction #</dt>
                                <dd class="col-sm-8 fw-semibold" id="detailTransactionId"></dd>

                                <dt class="col-sm-4 text-muted">Date</dt>
                                <dd class="col-sm-8" id="detailDate"></dd>

                                <dt class="col-sm-4 text-muted">Dealer</dt>
                                <dd class="col-sm-8" id="detailDealer"></dd>

                                <dt class="col-sm-4 text-muted">Customer</dt>
                                <dd class="col-sm-8" id="detailCustomer"></dd>

                                <dt class="col-sm-4 text-muted">Item</dt>
                                <dd class="col-sm-8" id="detailItem"></dd>

                                <dt class="col-sm-4 text-muted">Quantity</dt>
                                <dd class="col-sm-8" id="detailQty"></dd>

                                <dt class="col-sm-4 text-muted">Delivery Fee</dt>
                                <dd class="col-sm-8" id="detailDeliveryFee"></dd>

                                <dt class="col-sm-4 text-muted">Charge Amount</dt>
                                <dd class="col-sm-8" id="detailChargeAmount"></dd>

                                <dt class="col-sm-4 text-muted">Transaction Total</dt>
                                <dd class="col-sm-8" id="detailTotal"></dd>

                                <dt class="col-sm-4 text-muted">Payment Method</dt>
                                <dd class="col-sm-8" id="detailPaymentMethod"></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('javascript')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var detailsModal = document.getElementById('transactionDetailsModal');

            if (!detailsModal) {
                return;
            }

            detailsModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var transaction = button ? JSON.parse(button.getAttribute('data-transaction') || '{}') : {};

                document.getElementById('detailTransactionId').textContent = transaction.transaction_id || 'N/A';
                document.getElementById('detailDate').textContent = transaction.date || 'N/A';
                document.getElementById('detailDealer').textContent = transaction.dealer || 'N/A';
                document.getElementById('detailCustomer').textContent = transaction.customer || 'N/A';
                document.getElementById('detailItem').textContent = transaction.item || 'N/A';
                document.getElementById('detailQty').textContent = transaction.qty !== undefined ? transaction.qty : 'N/A';
                document.getElementById('detailDeliveryFee').textContent = transaction.delivery_fee !== undefined ? 'PHP ' + Number(transaction.delivery_fee).toFixed(2) : 'N/A';
                document.getElementById('detailChargeAmount').textContent = transaction.charge_amount !== undefined ? 'PHP ' + Number(transaction.charge_amount).toFixed(2) : 'N/A';
                document.getElementById('detailTotal').textContent = transaction.total !== undefined ? 'PHP ' + Number(transaction.total).toFixed(2) : 'N/A';
                document.getElementById('detailPaymentMethod').textContent = transaction.payment_method || 'N/A';
            });
        });
    </script>
@endsection
