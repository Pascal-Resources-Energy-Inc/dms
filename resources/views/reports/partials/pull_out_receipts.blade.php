@forelse($releases as $release)
    <form method="POST" class="js-confirm-replacement-form por-receipt" action="{{ route('pull-out-report.confirm-replacement', $release->id) }}">
        @csrf
        <div><strong>{{ $release->item_name }}</strong>
            <small>DR {{ $release->replacement_dr_number ?: '—' }}</small></div>
        <button type="submit" class="btn btn-sm btn-success" data-qty="{{ number_format($release->qty) }}">
            Confirm {{ number_format($release->qty) }} pcs
        </button>
    </form>
@empty
    —
@endforelse
