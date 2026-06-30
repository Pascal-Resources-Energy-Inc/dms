@php
    $maxPurchase = max(1, $trend->max('purchases'));
    $maxRefill = max(1, $trend->max('refills'));
@endphp

<div class="chart-lines">
    @foreach ($trend as $day)
        <div class="bar-pair" title="{{ $day['label'] }}">
            <span class="bar purchase" style="height: {{ max(5, round(($day['purchases'] / $maxPurchase) * 100)) }}%;"></span>
            <span class="bar refill" style="height: {{ max(5, round(($day['refills'] / $maxRefill) * 100)) }}%;"></span>
        </div>
    @endforeach
</div>
<div class="bar-labels">
    @foreach ($trend as $day)
        <span>{{ $day['label'] }}</span>
    @endforeach
</div>

