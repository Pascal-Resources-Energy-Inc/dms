@extends('layouts.header')

@section('css')
@include('bmc.partials.field_styles')
<style>
    .bmc-list-page { --line: #e4e7ec; --muted: #667085; --ink: #101828; display: grid; gap: 16px; padding: 18px 0 30px; }
    .bmc-list-head, .bmc-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
    .bmc-list-head { padding: 20px 22px; background: linear-gradient(135deg, #fff, #f8fbff); border: 1px solid var(--line); border-radius: 12px; box-shadow: 0 8px 24px rgba(15, 23, 42, .045); }
    .bmc-list-head h2 { margin: 0; color: var(--ink); font-size: 24px; font-weight: 900; }
    .bmc-list-head p { margin: 5px 0 0; color: var(--muted); font-size: 12px; }
    .bmc-list-create { min-height: 40px; display: inline-flex; align-items: center; gap: 7px; padding: 9px 15px; color: #fff; font-size: 12px; font-weight: 900; background: #2563eb; border-radius: 9px; text-decoration: none; box-shadow: 0 7px 16px rgba(37, 99, 235, .2); }
    .bmc-list-create:hover { color: #fff; background: #1d4ed8; }
    .bmc-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
    .bmc-stat { min-width: 0; display: flex; align-items: center; gap: 13px; padding: 16px; background: #fff; border: 1px solid var(--line); border-radius: 12px; box-shadow: 0 8px 20px rgba(15, 23, 42, .045); }
    .bmc-stat-icon { width: 44px; height: 44px; display: grid; place-items: center; flex: 0 0 44px; border-radius: 11px; font-size: 21px; }
    .bmc-stat-value { color: var(--ink); font-size: 22px; font-weight: 900; line-height: 1.1; }
    .bmc-stat-label { margin-top: 3px; color: var(--muted); font-size: 11px; font-weight: 800; }
    .bmc-tone-pending { color: #92400e; background: #fef3c7; }
    .bmc-tone-approved { color: #1d4ed8; background: #dbeafe; }
    .bmc-tone-partial { color: #7e22ce; background: #f3e8ff; }
    .bmc-tone-released { color: #0e7490; background: #cffafe; }
    .bmc-tone-closed { color: #166534; background: #dcfce7; }
    .bmc-tone-declined { color: #991b1b; background: #fee2e2; }
    .bmc-tone-cancelled { color: #475467; background: #f2f4f7; }
    .bmc-filter, .bmc-panel { overflow: hidden; background: #fff; border: 1px solid var(--line); border-radius: 12px; box-shadow: 0 8px 24px rgba(15, 23, 42, .045); }
    .bmc-filter form { display: grid; grid-template-columns: minmax(220px, 1fr) minmax(170px, .35fr) auto auto; gap: 9px; align-items: end; padding: 15px 17px; }
    .bmc-field label { display: block; margin-bottom: 5px; color: var(--muted); font-size: 11px; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; }
    .bmc-field .form-control, .bmc-field .form-select { min-height: 40px; font-size: 12px; border-color: #dfe4ea; border-radius: 8px; }
    .bmc-filter .btn { min-height: 40px; font-size: 12px; font-weight: 800; border-radius: 8px; }
    .bmc-panel-head { padding: 16px 18px; background: #f8fafc; border-bottom: 1px solid #edf0f5; }
    .bmc-panel-head h5 { margin: 0; font-size: 15px; font-weight: 900; }
    .bmc-count { padding: 6px 10px; color: #344054; font-size: 11px; font-weight: 800; background: #fff; border: 1px solid var(--line); border-radius: 999px; }
    .bmc-table { min-width: 1250px; margin: 0; }
    .bmc-table thead th { padding: 12px 13px; color: var(--muted); font-size: 11px; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; white-space: nowrap; background: #f8fafc; border-bottom: 1px solid #e8ecf2; }
    .bmc-table tbody td { padding: 13px; color: #344054; font-size: 12px; vertical-align: middle; border-color: #f0f2f5; }
    .bmc-reference { color: #1d4ed8; font-weight: 900; white-space: nowrap; }
    .bmc-table-detail { display: block; margin-top: 3px; color: var(--muted); font-size: 11px; }
    .bmc-dates { white-space: nowrap; font-size: 12px; line-height: 1.7; }
    .bmc-dates-label { color: #667085; }
    .bmc-dates-value { color: #2563eb; }
    .bmc-remarks { min-width: 180px; max-width: 240px; white-space: pre-wrap; overflow-wrap: anywhere; }
    .bmc-items { min-width: 170px; font-size: 11px; line-height: 1.6; white-space: nowrap; }
    .bmc-item-row { display: flex; justify-content: space-between; gap: 12px; font-weight: 700; }
    .bmc-item-released { color: blue; }
    .bmc-item-pending { color: red; }
    .bmc-signature { white-space: nowrap; }
    .bmc-signed { color: #166534; font-weight: 700; }
    .bmc-unsigned { color: #b42318; font-weight: 700; }
    .bmc-status { white-space: nowrap; display: inline-flex; padding: 6px 9px; font-size: 11px; font-weight: 900; border-radius: 999px; }
    .bmc-actions { display: flex; gap: 6px; white-space: nowrap; }
    .bmc-action { width: 34px; height: 34px; display: inline-grid; place-items: center; color: #1d4ed8; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; text-decoration: none; }
    .bmc-action:hover { color: #fff; background: #2563eb; }
    .bmc-action.edit { color: #b45309; background: #fff7ed; border-color: #fed7aa; }
    .bmc-action.edit:hover { color: #fff; background: #d97706; }
    .bmc-empty { padding: 55px 18px; color: var(--muted); text-align: center; }
    .bmc-empty i { display: block; margin-bottom: 8px; color: #d0d5dd; font-size: 36px; }
    .bmc-empty strong { display: block; margin-bottom: 4px; color: #344054; }
    .bmc-pagination { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 13px 16px; border-top: 1px solid #edf0f5; }
    .bmc-pagination span { color: var(--muted); font-size: 12px; font-weight: 700; }
    @media (max-width: 900px) { .bmc-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 760px) {
        .bmc-list-head { align-items: flex-start; flex-direction: column; }
        .bmc-list-create { width: 100%; justify-content: center; }
        .bmc-filter form { grid-template-columns: 1fr; }
    }
    @media (max-width: 420px) { .bmc-stats { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
@php
    $cards = [
        'For Approval' => ['pending', 'ti-clock'],
        'Approved' => ['approved', 'ti-circle-check'],
        'Partially Released' => ['partial', 'ti-package'],
        'Released' => ['released', 'ti-truck-delivery'],
        'Closed' => ['closed', 'ti-checks'],
        'Declined' => ['declined', 'ti-circle-x'],
        'Cancelled' => ['cancelled', 'ti-ban'],
    ];
@endphp
<div class="bmc-list-page">
    <header class="bmc-list-head">
        <div>
            <h2>Borrowing Marketing Collateral</h2>
            <p>CEO-MBD-FOR-012-001.B</p>
        </div>
        <a href="{{ route('bmc.create') }}" class="bmc-list-create"><i class="ti ti-plus"></i> New Request</a>
    </header>

    @if($errors->any())
        <div class="alert alert-danger mb-0" role="alert">{{ $errors->first() }}</div>
    @endif
    @if($serviceError)
        <div class="alert alert-danger mb-0" role="alert">{{ $serviceError }}</div>
    @endif

    <section class="bmc-stats" aria-label="Request summary">
        @foreach($cards as $status => $style)
            <div class="bmc-stat">
                <div class="bmc-stat-icon bmc-tone-{{ $style[0] }}"><i class="ti {{ $style[1] }}"></i></div>
                <div>
                    <div class="bmc-stat-value">{{ $summary === null ? '—' : number_format($summary[$status] ?? 0) }}</div>
                    <div class="bmc-stat-label">{{ $status }}</div>
                </div>
            </div>
        @endforeach
    </section>
    @if($summary === null && !$serviceError)
        <div class="alert alert-warning mb-0" role="status">Status totals are temporarily unavailable.</div>
    @endif

    <section class="bmc-filter">
        <form method="GET" action="{{ route('bmc.index') }}">
            <div class="bmc-field">
                <label for="bmcSearch">Search</label>
                <input type="search" name="search" id="bmcSearch" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Reference No. / Event name">
            </div>
            <div class="bmc-field">
                <label for="bmcStatus">Status</label>
                <select name="status" id="bmcStatus" class="form-select">
                    <option value="">All statuses</option>
                    @foreach($cards as $status => $style)
                        <option value="{{ $status }}" @if(($filters['status'] ?? '') === $status) selected @endif>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="ti ti-filter"></i> Filter</button>
            <a href="{{ route('bmc.index') }}" class="btn btn-outline-secondary">Clear</a>
        </form>
    </section>

    <section class="bmc-panel">
        <div class="bmc-panel-head">
            <h5>Submitted Requests</h5>
            @if(!$serviceError)<span class="bmc-count">{{ number_format($meta['total']) }} total</span>@endif
        </div>
        @if($serviceError)
            <div class="bmc-empty"><strong>Requests are unavailable.</strong><div>Please try again later.</div></div>
        @elseif(empty($requests))
            <div class="bmc-empty"><i class="ti ti-file-search"></i><strong>No submitted requests found.</strong><div>Submit a new request or adjust the filters.</div></div>
        @else
            <div class="table-responsive">
                <table class="table bmc-table">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Reference No.</th>
                            <th>Borrower</th>
                            <th>Signed Accountability</th>
                            <th>Event Name</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Remarks</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $item)
                            @php
                                $status = $item['status'] ?? '';
                                $hasAccountabilityData = array_key_exists('accountabilities', $item) && is_array($item['accountabilities']);
                                $accountabilities = collect($hasAccountabilityData ? $item['accountabilities'] : []);
                                $activeAccountabilities = $accountabilities->filter(function ($accountability) {
                                    return in_array($accountability['status'] ?? '', ['Released', 'For Return Confirmation', 'Partially Returned'], true);
                                });
                                $displayAccountability = $activeAccountabilities->sortBy(function ($accountability) {
                                    return $accountability['expected_return_at'] ?? '';
                                })->first();
                                if (!$displayAccountability) {
                                    $displayAccountability = $accountabilities->sortByDesc('released_at')->first();
                                }
                                $expectedReturn = $displayAccountability['expected_return_at'] ?? null;
                            @endphp
                            <tr>
                                <td>
                                    <div class="bmc-actions">
                                        <a class="bmc-action" href="{{ route('bmc.show', $item['id']) }}" title="View request" aria-label="View {{ $item['reference_no'] ?? 'request' }}"><i class="ti ti-eye"></i></a>
                                        @if($status === 'For Approval')
                                            <a class="bmc-action edit" href="{{ route('bmc.edit', $item['id']) }}" title="Edit request" aria-label="Edit {{ $item['reference_no'] ?? 'request' }}"><i class="ti ti-edit"></i></a>
                                            <form action="{{ route('bmc.cancel', $item['id']) }}" method="POST" onsubmit="return confirm('Cancel this borrowing request?');">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="bmc-action" title="Cancel request" aria-label="Cancel {{ $item['reference_no'] ?? 'request' }}"><i class="ti ti-x"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                                <td><a class="bmc-reference text-decoration-none" href="{{ route('bmc.show', $item['id']) }}">{{ $item['reference_no'] ?? '-' }}</a></td>
                                <td>
                                    {{ trim(($item['borrower_first_name'] ?? '') . ' ' . ($item['borrower_last_name'] ?? '')) ?: '-' }}
                                    <span class="bmc-table-detail">{{ $item['borrower_profile'] ?? '' }}</span>
                                </td>
                                <td class="bmc-signature">
                                    @if(!$hasAccountabilityData)
                                        <span>-</span>
                                    @else
                                        @forelse($accountabilities as $accountability)
                                            @php
                                                $signatureAvailable = array_key_exists('is_signed', $accountability) || array_key_exists('borrower_signature', $accountability);
                                                $isSigned = $accountability['is_signed'] ?? !empty($accountability['borrower_signature']);
                                            @endphp
                                            <div>{{ $accountability['accountability_ref'] ?? '-' }}:
                                                @if($signatureAvailable)
                                                    <span class="{{ $isSigned ? 'bmc-signed' : 'bmc-unsigned' }}">{{ $isSigned ? 'Signed' : 'Not signed' }}</span>
                                                @else
                                                    <span class="text-muted">Unavailable</span>
                                                @endif
                                            </div>
                                        @empty
                                            <span class="text-muted">-</span>
                                        @endforelse
                                    @endif
                                </td>
                                <td>{{ $item['event_name'] ?? '-' }}</td>
                                <td class="bmc-dates">
                                    <div><span class="bmc-dates-label">Date Needed:</span> <span class="bmc-dates-value">{{ !empty($item['date_needed']) ? \Carbon\Carbon::parse($item['date_needed'])->format('M d, Y') : '-' }}</span></div>
                                    <div><span class="bmc-dates-label">Expected Return:</span> <span class="bmc-dates-value">{{ !$hasAccountabilityData ? 'Unavailable' : ($expectedReturn ? \Carbon\Carbon::parse($expectedReturn)->format('M d, Y') : 'Set upon release') }}</span></div>
                                </td>
                                <td class="bmc-items">
                                    @php
                                        $releasedByItem = collect($item['accountabilities'] ?? [])->flatMap(function ($release) {
                                            return $release['items'] ?? [];
                                        })->groupBy('item_name')->map(function ($releasedItems) {
                                            return $releasedItems->sum(function ($releasedItem) {
                                                return (int) ($releasedItem['released_quantity'] ?? 0);
                                            });
                                        });
                                        $usesApprovedQuantity = in_array($status, ['Approved', 'Partially Released', 'Released', 'Closed'], true);
                                        $displayedItems = collect($item['borrowed_items'] ?? [])->filter(function ($borrowedItem) use ($usesApprovedQuantity) {
                                            return (int) ($usesApprovedQuantity ? ($borrowedItem['approved_quantity'] ?? 0) : ($borrowedItem['requested_quantity'] ?? 0)) > 0;
                                        });
                                    @endphp
                                    @forelse($displayedItems as $borrowedItem)
                                        @php
                                            $itemName = $borrowedItem['item_name'] ?? '';
                                            $itemLabel = ['Negosyo Partner Roll Up Banner' => 'Partner Banner', 'End User Roll Up Banner' => 'End User Banner'][$itemName] ?? $itemName;
                                            $targetQuantity = (int) ($usesApprovedQuantity ? ($borrowedItem['approved_quantity'] ?? 0) : ($borrowedItem['requested_quantity'] ?? 0));
                                            $releasedQuantity = (int) $releasedByItem->get($itemName, 0);
                                        @endphp
                                        <div class="bmc-item-row">
                                            <span title="{{ $itemName }}">{{ $itemLabel }}:</span>
                                            @if($hasAccountabilityData)
                                                <span class="{{ $releasedQuantity >= $targetQuantity ? 'bmc-item-released' : 'bmc-item-pending' }}">{{ $releasedQuantity }}/{{ $targetQuantity }}</span>
                                            @else
                                                <span class="text-muted">Unavailable</span>
                                            @endif
                                        </div>
                                    @empty
                                        <span class="text-muted">-</span>
                                    @endforelse
                                </td>
                                <td class="bmc-remarks">{{ ($item['mbd_remarks'] ?? null) ?: '-' }}</td>
                                <td><span class="bmc-status bmc-tone-{{ $cards[$status][0] ?? 'cancelled' }}">{{ $status ?: '-' }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($meta['last_page'] > 1)
                <div class="bmc-pagination">
                    <span>Page {{ $meta['current_page'] }} of {{ $meta['last_page'] }}</span>
                    <div class="d-flex gap-2">
                        @if($meta['current_page'] > 1)<a class="btn btn-sm btn-outline-secondary" href="{{ route('bmc.index', array_merge($filters, ['page' => $meta['current_page'] - 1])) }}">Previous</a>@endif
                        @if($meta['current_page'] < $meta['last_page'])<a class="btn btn-sm btn-outline-secondary" href="{{ route('bmc.index', array_merge($filters, ['page' => $meta['current_page'] + 1])) }}">Next</a>@endif
                    </div>
                </div>
            @endif
        @endif
    </section>
</div>
@endsection
