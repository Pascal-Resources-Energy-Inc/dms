@extends('layouts.header')

@section('css')
<style>
    .ldr-list-page {
        --ldr-blue: #2563eb;
        --ldr-ink: #101828;
        --ldr-muted: #667085;
        --ldr-line: #e4e7ec;
        display: grid;
        gap: 16px;
        padding-top: 18px;
    }
    .ldr-list-head, .ldr-list-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
    .ldr-list-head { padding: 20px 22px; background: linear-gradient(135deg, #fff, #f8fbff); border: 1px solid var(--ldr-line); border-radius: 12px; box-shadow: 0 8px 24px rgba(15, 23, 42, .045); }
    .ldr-list-head h2 { margin: 0; color: var(--ldr-ink); font-size: 24px; font-weight: 900; }
    .ldr-list-head p { margin: 5px 0 0; color: var(--ldr-muted); font-size: 12px; }
    .ldr-list-create { min-height: 40px; display: inline-flex; align-items: center; gap: 7px; padding: 9px 15px; color: #fff; font-size: 12px; font-weight: 900; background: var(--ldr-blue); border: 0; border-radius: 9px; text-decoration: none; box-shadow: 0 7px 16px rgba(37, 99, 235, .2); }
    .ldr-list-create:hover { color: #fff; background: #1d4ed8; }
    .ldr-list-stats { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; }
    .ldr-list-stat { min-width: 0; display: flex; align-items: center; gap: 13px; padding: 16px; background: #fff; border: 1px solid var(--ldr-line); border-radius: 12px; box-shadow: 0 8px 20px rgba(15, 23, 42, .045); }
    .ldr-list-stat-icon { width: 44px; height: 44px; display: grid; place-items: center; flex: 0 0 44px; border-radius: 11px; font-size: 21px; }
    .ldr-list-stat-icon.pending { color: #92400e; background: #fef3c7; }
    .ldr-list-stat-icon.approved { color: #1d4ed8; background: #dbeafe; }
    .ldr-list-stat-icon.processed { color: #166534; background: #dcfce7; }
    .ldr-list-stat-icon.declined { color: #991b1b; background: #fee2e2; }
    .ldr-list-stat-icon.cancelled { color: #475467; background: #f2f4f7; }
    .ldr-list-stat-value { color: var(--ldr-ink); font-size: 22px; font-weight: 900; line-height: 1.1; }
    .ldr-list-stat-label { margin-top: 3px; color: var(--ldr-muted); font-size: 11px; font-weight: 800; }
    .ldr-list-filter, .ldr-list-panel { overflow: hidden; background: #fff; border: 1px solid var(--ldr-line); border-radius: 12px; box-shadow: 0 8px 24px rgba(15, 23, 42, .045); }
    .ldr-list-filter form { display: grid; grid-template-columns: minmax(220px, 1fr) minmax(170px, .35fr) auto auto; gap: 9px; align-items: end; padding: 15px 17px; }
    .ldr-list-field label { display: block; margin-bottom: 5px; color: var(--ldr-muted); font-size: 11px; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; }
    .ldr-list-field .form-control, .ldr-list-field .form-select { min-height: 40px; font-size: 12px; border-color: #dfe4ea; border-radius: 8px; }
    .ldr-list-filter .btn { min-height: 40px; font-size: 12px; font-weight: 800; border-radius: 8px; }
    .ldr-list-panel-head { padding: 16px 18px; background: #f8fafc; border-bottom: 1px solid #edf0f5; }
    .ldr-list-panel-head h5 { margin: 0; font-size: 15px; font-weight: 900; }
    .ldr-list-count { padding: 6px 10px; color: #344054; font-size: 11px; font-weight: 800; background: #fff; border: 1px solid var(--ldr-line); border-radius: 999px; }
    .ldr-list-table { min-width: 980px; margin: 0; }
    .ldr-list-table thead th { padding: 12px 13px; color: var(--ldr-muted); font-size: 11px; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; white-space: nowrap; background: #f8fafc; border-bottom: 1px solid #e8ecf2; }
    .ldr-list-table tbody td { padding: 13px; color: #344054; font-size: 12px; vertical-align: middle; border-color: #f0f2f5; }
    .ldr-reference { color: #1d4ed8; font-weight: 900; white-space: nowrap; }
    .ldr-requestor strong { display: block; color: var(--ldr-ink); font-size: 12px; }
    .ldr-requestor small { display: block; margin-top: 2px; color: var(--ldr-muted); }
    .ldr-status { display: inline-flex; padding: 6px 9px; font-size: 11px; font-weight: 900; border-radius: 999px; }
    .ldr-status.pending { color: #92400e; background: #fef3c7; }
    .ldr-status.approved { color: #1d4ed8; background: #dbeafe; }
    .ldr-status.processed { color: #166534; background: #dcfce7; }
    .ldr-status.declined { color: #991b1b; background: #fee2e2; }
    .ldr-status.cancelled { color: #475467; background: #f2f4f7; }
    .ldr-list-actions { display: flex; align-items: center; gap: 6px; white-space: nowrap; }
    .ldr-list-actions form { margin: 0; }
    .ldr-edit-action { width: 34px; height: 34px; display: inline-grid; place-items: center; color: #1d4ed8; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; text-decoration: none; }
    .ldr-edit-action:hover { color: #fff; background: #2563eb; border-color: #2563eb; }
    .ldr-edit-action.is-edit { color: #b45309; background: #fff7ed; border-color: #fed7aa; }
    .ldr-edit-action.is-edit:hover { color: #fff; background: #d97706; border-color: #d97706; }
    .ldr-cancel-action { width: 34px; height: 34px; display: inline-grid; place-items: center; padding: 0; color: #b42318; background: #fef3f2; border: 1px solid #fecdca; border-radius: 8px; }
    .ldr-cancel-action:hover { color: #fff; background: #dc2626; border-color: #dc2626; }
    .ldr-list-empty { padding: 55px 18px; color: var(--ldr-muted); text-align: center; }
    .ldr-list-empty i { display: block; margin-bottom: 8px; color: #d0d5dd; font-size: 36px; }
    .ldr-list-empty strong { display: block; margin-bottom: 4px; color: #344054; }
    .ldr-list-pagination { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 13px 16px; border-top: 1px solid #edf0f5; }
    .ldr-list-pagination span { color: var(--ldr-muted); font-size: 12px; font-weight: 700; }
    @media (max-width: 760px) {
        .ldr-list-head { align-items: flex-start; flex-direction: column; }
        .ldr-list-create { width: 100%; justify-content: center; }
        .ldr-list-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .ldr-list-filter form { grid-template-columns: 1fr; }
    }
    @media (max-width: 420px) { .ldr-list-stats { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
<div class="ldr-list-page">
    <header class="ldr-list-head">
        <div>
            <h2>Layout Design Requests</h2>
            <p>CEO-MBD-FOR-001-000</p>
        </div>
        <a href="{{ route('layout.create') }}" class="ldr-list-create">
            <i class="ti ti-plus"></i> New Request
        </a>
    </header>

    <section class="ldr-list-stats" aria-label="Request summary">
        <div class="ldr-list-stat">
            <div class="ldr-list-stat-icon pending"><i class="ti ti-clock"></i></div>
            <div>
                <div class="ldr-list-stat-value">{{ number_format($summary['pending'] ?? 0) }}</div>
                <div class="ldr-list-stat-label">Pending</div>
            </div>
        </div>
        <div class="ldr-list-stat">
            <div class="ldr-list-stat-icon approved"><i class="ti ti-circle-check"></i></div>
            <div>
                <div class="ldr-list-stat-value">{{ number_format($summary['approved'] ?? 0) }}</div>
                <div class="ldr-list-stat-label">Approved</div>
            </div>
        </div>
        <div class="ldr-list-stat">
            <div class="ldr-list-stat-icon processed"><i class="ti ti-checks"></i></div>
            <div>
                <div class="ldr-list-stat-value">{{ number_format($summary['processed'] ?? 0) }}</div>
                <div class="ldr-list-stat-label">Processed</div>
            </div>
        </div>
        <div class="ldr-list-stat">
            <div class="ldr-list-stat-icon declined"><i class="ti ti-circle-x"></i></div>
            <div>
                <div class="ldr-list-stat-value">{{ number_format($summary['declined'] ?? 0) }}</div>
                <div class="ldr-list-stat-label">Declined</div>
            </div>
        </div>
        <div class="ldr-list-stat">
            <div class="ldr-list-stat-icon cancelled"><i class="ti ti-ban"></i></div>
            <div>
                <div class="ldr-list-stat-value">{{ number_format($summary['cancelled'] ?? 0) }}</div>
                <div class="ldr-list-stat-label">Cancelled</div>
            </div>
        </div>
    </section>

    <section class="ldr-list-filter">
        <form method="GET" action="{{ route('layout.index') }}">
            <div class="ldr-list-field">
                <label for="ldrSearch">Search</label>
                <input type="search" name="search" id="ldrSearch" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Reference number / Collateral Type">
            </div>
            <div class="ldr-list-field">
                <label for="ldrStatus">Status</label>
                <select name="status" id="ldrStatus" class="form-select">
                    <option value="">All statuses</option>
                    @foreach(['Pending', 'Approved', 'Processed', 'Declined', 'Cancelled'] as $status)
                        <option value="{{ $status }}" @if(($filters['status'] ?? '') === $status) selected @endif>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="ti ti-filter"></i> Filter</button>
            <a href="{{ route('layout.index') }}" class="btn btn-outline-secondary">Clear</a>
        </form>
    </section>

    <section class="ldr-list-panel">
        <div class="ldr-list-panel-head">
            <h5>Submitted Requests</h5>
            <span class="ldr-list-count">{{ number_format($pagination['total'] ?? count($requests)) }} total</span>
        </div>

        @if(empty($requests))
            <div class="ldr-list-empty">
                <i class="ti ti-file-search"></i>
                <strong>No submitted requests found.</strong>
                <div>Submit a new layout design request or adjust the filters.</div>
            </div>
        @else
            <div class="table-responsive">
                <table class="table ldr-list-table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Requestor</th>
                            <th>Partner</th>
                            <th>Medium</th>
                            <th>Collateral</th>
                            <th>Preferred Date Needed</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $item)
                            @php
                                $collateral = $item['marketing_collateral_type'] ?? [];
                                if (!is_array($collateral)) {
                                    $decodedCollateral = json_decode($collateral, true);
                                    $collateral = is_array($decodedCollateral) ? $decodedCollateral : [$collateral];
                                }
                                $storeSignageOptions = [
                                    'Layout file of store signage only',
                                    'Refurbished Store Signage (P5,000)',
                                ];
                                if (array_intersect($storeSignageOptions, $collateral)) {
                                    $collateral = array_values(array_filter($collateral, function ($value) {
                                        return $value !== 'Store Signage';
                                    }));
                                }
                                $status = $item['status'] ?? 'Pending';
                            @endphp
                            <tr>
                                <td><a href="{{ route('layout.show', $item['id']) }}" class="ldr-reference text-decoration-none">{{ $item['reference_no'] ?? '-' }}</a></td>
                                <td class="ldr-requestor">
                                    <strong>{{ trim(($item['requestor_first_name'] ?? '') . ' ' . ($item['requestor_last_name'] ?? '')) ?: '-' }}</strong>
                                    <small>{{ $item['requestor_email'] ?? '-' }}</small>
                                </td>
                                <td>{{ $item['department'] ?? '-' }}</td>
                                <td>{{ $item['medium_type'] ?? '-' }}</td>
                                <td>{{ implode(', ', array_filter($collateral)) ?: '-' }}</td>
                                <td>{{ !empty($item['date_needed']) ? \Carbon\Carbon::parse($item['date_needed'])->format('M d, Y') : '-' }}</td>
                                <td>{{ !empty($item['submitted_at']) ? \Carbon\Carbon::parse($item['submitted_at'])->format('M d, Y') : '-' }}</td>
                                <td><span class="ldr-status {{ strtolower($status) }}">{{ $status }}</span></td>
                                <td>
                                    <div class="ldr-list-actions">
                                        <a href="{{ route('layout.show', $item['id']) }}" class="ldr-edit-action" title="View request" aria-label="View {{ $item['reference_no'] ?? 'request' }}">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                        @if(strcasecmp($status, 'Pending') === 0)
                                            <a href="{{ route('layout.edit', $item['id']) }}" class="ldr-edit-action is-edit" title="Edit request" aria-label="Edit {{ $item['reference_no'] ?? 'request' }}">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                            <form action="{{ route('layout.cancel', $item['id']) }}" method="POST" class="ldr-cancel-form">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="ldr-cancel-action" title="Cancel request" aria-label="Cancel {{ $item['reference_no'] ?? 'request' }}">
                                                    <i class="ti ti-x"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(($pagination['last_page'] ?? 1) > 1)
                <div class="ldr-list-pagination">
                    <span>Page {{ $pagination['current_page'] }} of {{ $pagination['last_page'] }}</span>
                    <div class="d-flex gap-2">
                        @if($pagination['current_page'] > 1)
                            <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['current_page'] - 1]) }}" class="btn btn-sm btn-outline-secondary">Previous</a>
                        @endif
                        @if($pagination['current_page'] < $pagination['last_page'])
                            <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['current_page'] + 1]) }}" class="btn btn-sm btn-outline-secondary">Next</a>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </section>
</div>
@endsection

@section('javascript')
    @include('ldr.partials.sweet_alerts')
@endsection
