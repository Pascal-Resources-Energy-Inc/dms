@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('design/assets/css/customer-transactions.css') }}">
@endsection

@section('content')
@php
    $transactionCount = $transactions->count();
    $hasFilters = collect(['dealer', 'customer', 'date_from', 'date_to'])->contains(function ($field) {
        return request()->filled($field);
    });
    $tabQuery = request()->except(['project', 'page', 'dealer', 'customer']);
    $clearFiltersUrl = route('customer-ads', ['project' => $selectedProject]);
@endphp
<section class="cto-page" aria-labelledby="customerTransactionsTitle">
    <header class="cto-heading">
        <div>
            <div class="cto-eyebrow"><i class="bi bi-receipt" aria-hidden="true"></i> Sales activity</div>
            <h1 id="customerTransactionsTitle">Customer Transaction Overview</h1>
            <p>Customer sales from dealers, mega dealers, and center chiefs.</p>
        </div>
        <span class="cto-count"><i class="bi bi-arrow-left-right" aria-hidden="true"></i> {{ number_format($transactionCount) }} {{ $transactionCount === 1 ? 'transaction' : 'transactions' }}</span>
    </header>

    <div class="cto-panel">
        <nav class="cto-projects" aria-label="Transaction project">
            @foreach($projects as $project)
                <a class="cto-project {{ $selectedProject === $project ? 'is-active' : '' }}"
                   href="{{ route('customer-ads', array_merge($tabQuery, ['project' => $project])) }}"
                   @if($selectedProject === $project) aria-current="page" @endif>
                    {{ $project }}
                </a>
            @endforeach
        </nav>

        <form method="GET" action="{{ route('customer-ads') }}" class="cto-filters" aria-label="Filter customer transactions">
            <input type="hidden" name="project" value="{{ $selectedProject }}">
            <div class="cto-field cto-person-field">
                <label for="ctoSeller">Seller</label>
                <select name="dealer" id="ctoSeller" class="cto-control">
                    <option value="">All sellers</option>
                    @foreach($dealers as $dealer)
                        <option value="{{ $dealer->user_id }}" {{ request('dealer') == $dealer->user_id ? 'selected' : '' }}>
                            {{ $dealer->name }}{{ ($dealer->seller_type ?? 'Dealer') !== 'Dealer' ? ' (' . $dealer->seller_type . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="cto-field cto-person-field">
                <label for="ctoCustomer">Customer</label>
                <select name="customer" id="ctoCustomer" class="cto-control">
                    <option value="">All customers</option>
                    @foreach($customers as $customer)
                        @php
                            $customerTag = strtolower(trim((string) ($customer->client_tag ?? $customer->tag ?? '')));
                            $customerLabel = $customerTag === 'guest' ? 'GUEST' : $customer->name;
                        @endphp
                        <option value="{{ $customer->id }}" {{ request('customer') == $customer->id ? 'selected' : '' }}>
                            {{ $customerLabel }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="cto-field">
                <label for="ctoDateFrom">From date</label>
                <input type="date" name="date_from" id="ctoDateFrom" class="cto-control" value="{{ request('date_from') }}">
            </div>
            <div class="cto-field">
                <label for="ctoDateTo">To date</label>
                <input type="date" name="date_to" id="ctoDateTo" class="cto-control" value="{{ request('date_to') }}">
            </div>
            <div class="cto-filter-actions">
                <button type="submit" class="cto-button cto-button-primary"><i class="bi bi-funnel" aria-hidden="true"></i> Apply filters</button>
                @if($hasFilters)
                    <a href="{{ $clearFiltersUrl }}" class="cto-button cto-button-reset">Clear</a>
                @endif
            </div>
        </form>

        <div class="cto-results-heading">
            <h2>{{ $selectedProject }} transactions</h2>
            <span>{{ $hasFilters ? 'Filtered results' : 'All dates' }}</span>
        </div>
        <div class="cto-table-wrap">
            <table class="cto-table" aria-label="{{ $selectedProject }} customer transactions">
                <thead>
                    <tr>
                        <th scope="col" class="cto-date">Date</th>
                        <th scope="col" class="cto-seller">Seller</th>
                        <th scope="col" class="cto-customer">Customer</th>
                        <th scope="col" class="cto-item">Product</th>
                        <th scope="col" class="cto-qty">Qty</th>
                        <th scope="col" class="cto-amount">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        @php
                            $transactionCustomerTag = strtolower(trim((string) ($transaction->customer->client_tag ?? $transaction->customer->tag ?? '')));
                            $transactionCustomerName = $transactionCustomerTag === 'guest'
                                ? 'GUEST'
                                : ($transaction->customer->name ?? '-');
                        @endphp
                        <tr class="cto-transaction">
                            <td class="cto-date" data-label="Date">{{ $transaction->date ? date('M d, Y', strtotime($transaction->date)) : '-' }}</td>
                            <td class="cto-seller" data-label="Seller">
                                <span class="cto-person-name">{{ $transaction->dealer->name ?? '-' }}</span>
                                <span class="cto-seller-type">{{ $transaction->dealer->seller_type ?? 'Dealer' }}</span>
                            </td>
                            <td class="cto-customer" data-label="Customer"><span class="cto-person-name">{{ $transactionCustomerName }}</span></td>
                            <td class="cto-item" data-label="Product">{{ $transaction->item ?: '-' }}</td>
                            <td class="cto-qty" data-label="Qty">{{ number_format($transaction->qty ?? 0, 2) }}</td>
                            <td class="cto-amount" data-label="Amount">PHP {{ number_format(($transaction->qty ?? 0) * ($transaction->price ?? 0), 2) }}</td>
                        </tr>
                    @empty
                        <tr class="cto-empty-row">
                            <td colspan="6">
                                <div class="cto-empty">
                                    <span class="cto-empty-icon"><i class="bi bi-receipt" aria-hidden="true"></i></span>
                                    <h3>{{ $hasFilters ? 'No matching transactions' : 'No transactions yet' }}</h3>
                                    <p>{{ $hasFilters ? 'Try another seller, customer, or date range to find a transaction.' : 'Customer transactions for ' . $selectedProject . ' will appear here when available.' }}</p>
                                    @if($hasFilters)
                                        <a class="cto-button cto-button-secondary" href="{{ $clearFiltersUrl }}">Clear filters</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactionCount > 0)
            <div class="cto-footer"><i class="bi bi-info-circle" aria-hidden="true"></i> Amounts are shown in Philippine pesos (PHP).</div>
        @endif
    </div>
</section>
@endsection
