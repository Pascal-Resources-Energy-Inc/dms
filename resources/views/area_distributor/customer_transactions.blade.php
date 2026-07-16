@extends('layouts.header')

@section('content')
<div class="container-fluid py-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
                        <div>
                            <h4 class="fw-bold mb-1">Customer Transaction Overview</h4>
                            <p class="text-muted mb-0">Track sales activity from AD dealers to customers in one place.</p>
                        </div>
                        <div class="text-muted small">
                            <i class="ti ti-users me-1"></i>
                            {{ $transactions->count() }} recorded transactions
                        </div>
                    </div>

                    <?php $tabQuery = request()->except(['project', 'page']); ?>
                    <ul class="nav nav-tabs mb-4" aria-label="Project filter">
                        @foreach($projects as $project)
                            <li class="nav-item">
                                <a class="nav-link {{ $selectedProject === $project ? 'active' : '' }}"
                                   href="{{ route('customer-ads', array_merge($tabQuery, ['project' => $project])) }}">
                                    {{ $project }}
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <form method="GET" action="{{ route('customer-ads') }}" class="row g-3 align-items-end mb-4">
                        <input type="hidden" name="project" value="{{ $selectedProject }}">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Dealer Name</label>
                            <select name="dealer" class="form-select form-select-sm">
                                <option value="">All Dealers</option>
                                @foreach($dealers as $dealer)
                                    <option value="{{ $dealer->user_id }}" {{ request('dealer') == $dealer->user_id ? 'selected' : '' }}>
                                        {{ $dealer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Customer Name</label>
                            <select name="customer" class="form-select form-select-sm">
                                <option value="">All Customers</option>
                                @foreach($customers as $customer)
                                    <?php
                                        $customerTag = strtolower(trim((string) ($customer->client_tag ?? $customer->tag ?? '')));
                                        $customerLabel = $customerTag === 'guest' ? 'GUEST' : $customer->name;
                                    ?>
                                    <option value="{{ $customer->id }}" {{ request('customer') == $customer->id ? 'selected' : '' }}>
                                        {{ $customerLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">From</label>
                            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">To</label>
                            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-danger btn-sm w-100">
                                <i class="ti ti-filter me-1"></i> Filter
                            </button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Dealer Name</th>
                                    <th>Customer Name</th>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Amount</th>
                                    {{-- <th>Dealer Points</th>
                                    <th>Customer Points</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                    <?php
                                        $transactionCustomerTag = strtolower(trim((string) ($transaction->customer->client_tag ?? $transaction->customer->tag ?? '')));
                                        $transactionCustomerName = $transactionCustomerTag === 'guest'
                                            ? 'GUEST'
                                            : ($transaction->customer->name ?? '-');
                                    ?>
                                    <tr>
                                        <td>{{ $transaction->date ? date('M d, Y', strtotime($transaction->date)) : '-' }}</td>
                                        <td>{{ $transaction->dealer->name ?? '-' }}</td>
                                        <td>{{ $transactionCustomerName }}</td>
                                        <td>{{ $transaction->item ?: '-' }}</td>
                                        <td>{{ number_format($transaction->qty ?? 0, 2) }}</td>
                                        <td>PHP {{ number_format(($transaction->qty ?? 0) * ($transaction->price ?? 0), 2) }}</td>
                                        {{-- <td><span class="badge bg-success-subtle text-success">{{ $transaction->points_dealer ?? 0 }}</span></td>
                                        <td><span class="badge bg-info-subtle text-info">{{ $transaction->points_client ?? 0 }}</span></td> --}}
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">No customer transactions found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
