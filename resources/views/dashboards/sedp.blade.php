@extends('layouts.header')

@section('css')
<style>
    .sedp-command {
        display: grid;
        gap: 18px;
        margin-bottom: 22px;
        color: #172033;
    }

    .sedp-hero {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        overflow: hidden;
        padding: 28px;
        color: #fff;
        background: linear-gradient(120deg, #0b1220 0%, #12384f 52%, #0e7490 100%);
        border-radius: 18px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .18);
    }

    .sedp-hero::after {
        position: absolute;
        top: -170px;
        right: -110px;
        width: 330px;
        height: 330px;
        content: '';
        border: 58px solid rgba(255, 255, 255, .07);
        border-radius: 50%;
    }

    .sedp-hero-copy,
    .sedp-hero-actions { position: relative; z-index: 1; }

    .sedp-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 8px;
        color: #9ce8f6;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .sedp-title {
        margin: 0;
        color: #fff;
        font-size: clamp(24px, 3vw, 34px);
        font-weight: 900;
        letter-spacing: -.03em;
    }

    .sedp-subtitle {
        max-width: 650px;
        margin: 7px 0 0;
        color: rgba(255, 255, 255, .72);
        font-size: 13px;
    }

    .sedp-date {
        display: grid;
        gap: 3px;
        min-width: 185px;
        padding: 9px 12px;
        background: rgba(255, 255, 255, .1);
        border: 1px solid rgba(255, 255, 255, .16);
        border-radius: 11px;
    }

    .sedp-date label {
        color: #b8eaf5;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .sedp-date input {
        color: #fff;
        font-size: 12px;
        font-weight: 800;
        background: transparent;
        border: 0;
        outline: 0;
        color-scheme: dark;
    }

    .sedp-tabs {
        display: grid;
        grid-template-columns: repeat(2, minmax(150px, 1fr));
        gap: 4px;
        padding: 5px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
    }

    .sedp-tab {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        min-height: 48px;
        padding: 10px 14px;
        color: #172033;
        background: #fff;
        border-radius: 9px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, .06);
        font-size: 12px;
        font-weight: 900;
    }

    .sedp-tab i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        color: var(--project-color);
        background: var(--project-soft);
        border-radius: 8px;
        font-size: 16px;
    }

    .rise { --project-color: #0369a1; --project-soft: #e0f2fe; }
    .genesis { --project-color: #7c3aed; --project-soft: #ede9fe; }

    .sedp-source-bar,
    .sedp-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .sedp-source-bar { padding: 2px 2px 0; }
    .sedp-source-name { margin: 0; color: #172033; font-size: 17px; font-weight: 900; }
    .sedp-source-copy { margin: 3px 0 0; color: #64748b; font-size: 11px; }

    .sedp-asof {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #64748b;
        font-size: 10px;
        font-weight: 800;
    }

    .sedp-asof i { color: #0e7490; }

    .sedp-kpis {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .sedp-kpi {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        align-items: start;
        gap: 12px;
        min-height: 112px;
        padding: 15px;
        background: #fff;
        border: 1px solid #e8ecf2;
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(15, 23, 42, .045);
    }

    .sedp-kpi-icon {
        grid-row: span 3;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        color: var(--metric-color);
        background: var(--metric-soft);
        border-radius: 9px;
        font-size: 17px;
    }

    .sedp-kpi-label {
        display: block;
        color: #64748b;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .045em;
        line-height: 1.2;
        text-transform: uppercase;
    }

    .sedp-kpi-value { display: block; margin-top: 5px; color: #0f172a; font-size: 24px; font-weight: 900; line-height: 1.05; }
    .sedp-kpi-note { display: block; margin-top: 7px; color: #94a3b8; font-size: 10px; font-weight: 700; line-height: 1.35; }
    .sedp-kpi.rise { --metric-color: #0369a1; --metric-soft: #e0f2fe; }
    .sedp-kpi.genesis { --metric-color: #7c3aed; --metric-soft: #ede9fe; }
    .sedp-kpi.refill { --metric-color: #b45309; --metric-soft: #fef3c7; }

    .sedp-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(300px, .6fr);
        gap: 14px;
    }

    .sedp-panel {
        overflow: hidden;
        background: #fff;
        border: 1px solid #e5e9f0;
        border-radius: 16px;
        box-shadow: 0 9px 26px rgba(15, 23, 42, .055);
    }

    .sedp-panel-head { padding: 17px 19px; border-bottom: 1px solid #eef1f5; }
    .sedp-panel-head h5 { margin: 0; color: #172033; font-size: 15px; font-weight: 900; }
    .sedp-panel-head p { margin: 3px 0 0; color: #98a2b3; font-size: 10px; }
    .sedp-project-list { display: grid; }

    .sedp-project-row {
        display: grid;
        grid-template-columns: minmax(125px, 1fr) repeat(3, minmax(80px, .8fr));
        gap: 12px;
        align-items: center;
        padding: 16px 19px;
        border-top: 1px solid #f0f2f5;
    }

    .sedp-project-row:first-child { border-top: 0; }
    .sedp-project-name { display: flex; align-items: center; gap: 9px; color: #344054; font-size: 12px; font-weight: 900; }
    .sedp-project-dot { width: 10px; height: 10px; background: var(--project-color); border-radius: 50%; box-shadow: 0 0 0 5px var(--project-soft); }
    .sedp-project-metric { text-align: right; }
    .sedp-project-metric small { display: block; color: #98a2b3; font-size: 9px; font-weight: 800; text-transform: uppercase; }
    .sedp-project-metric strong { display: block; margin-top: 3px; color: #172033; font-size: 17px; font-weight: 900; }

    .sedp-refill-card {
        min-height: 100%;
        padding: 22px;
        color: #fff;
        /* background: linear-gradient(140deg, #12384f, #0e7490); */
        background: linear-gradient(140deg, #1e7bb3, #012935);
    }

    .sedp-refill-card h5 { margin: 0; color: #fff; font-size: 16px; font-weight: 900; }
    .sedp-refill-card p { margin: 4px 0 0; color: rgba(255, 255, 255, .68); font-size: 11px; }
    .sedp-cylinder-wrap { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 17px; }
    .sedp-refill-total { color: #fff; font-size: 35px; font-weight: 900; line-height: 1; }
    .sedp-refill-total small { display: block; margin-top: 6px; color: #b8eaf5; font-size: 10px; font-weight: 800; }
    .sedp-cylinder { width: 97px; height: 120px; }
    .sedp-refill-rows { display: grid; gap: 8px; margin-top: 17px; }

    .sedp-refill-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 9px;
        color: #d5f2f8;
        border-top: 1px solid rgba(255, 255, 255, .14);
        font-size: 11px;
        font-weight: 700;
    }

    .sedp-refill-row strong { color: #fff; font-size: 14px; }

    @media (max-width: 1000px) {
        .sedp-kpis { grid-template-columns: repeat(2, 1fr); }
        .sedp-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 700px) {
        .sedp-hero,
        .sedp-source-bar,
        .sedp-panel-head { align-items: flex-start; flex-direction: column; }
        .sedp-hero { padding: 22px; }
        .sedp-date { width: 100%; }
        .sedp-tabs,
        .sedp-kpis { grid-template-columns: 1fr; }
        .sedp-project-row { grid-template-columns: 1fr repeat(3, 1fr); gap: 8px; }
        .sedp-project-name { grid-column: 1 / -1; }
        .sedp-project-metric { text-align: left; }
    }
</style>
@endsection

@section('content')
@php
    $metricCards = [
        ['key' => 'beneficiaries', 'label' => 'Changed Lives', 'icon' => 'ti-heart-handshake', 'note' => 'Registered beneficiaries reached'],
        ['key' => 'entrepreneurs', 'label' => 'Micro Entrepreneurs', 'icon' => 'ti-building-store', 'note' => 'Project dealers creating livelihoods'],
        ['key' => 'refills', 'label' => '330g Refills', 'icon' => 'ti-gas-station', 'note' => 'Cylinder refill quantity delivered'],
    ];
@endphp

<section class="sedp-command" style="margin-top: 5.5em;">
    <form method="GET" action="{{ route('home') }}">
        <section class="sedp-hero">
            <div class="sedp-hero-copy">
                <span class="sedp-eyebrow"><i class="ti ti-heart-handshake"></i>Social Enterprise Development Program</span>
                <h1 class="sedp-title">Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 18 ? 'afternoon' : 'evening') }}, {{ auth()->user()->name }}</h1>
                <p class="sedp-subtitle">A live view of the lives changed, livelihoods supported, and 330g LPG refills delivered through SEDP.</p>
            </div>

            <div class="sedp-hero-actions">
                <div class="sedp-date">
                    <label for="as_of">Figures as of</label>
                    <input id="as_of" name="as_of" type="date" value="{{ $asOf->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}" onchange="this.form.submit()">
                </div>
            </div>
        </section>
    </form>

    <div class="sedp-tabs" aria-label="SEDP projects">
        @foreach ($projects as $project)
            <div class="sedp-tab {{ $project['accent'] }}">
                <i class="ti {{ $project['accent'] === 'rise' ? 'ti-trending-up' : 'ti-sparkles' }}"></i>
                {{ $project['label'] }}
            </div>
        @endforeach
    </div>

    <div class="sedp-source-bar">
        <div>
            <h2 class="sedp-source-name">SEDP Impact Overview</h2>
            <p class="sedp-source-copy">Combined contribution of Project Rise and Project Genesis.</p>
        </div>
        <span class="sedp-asof"><i class="ti ti-calendar-event"></i>Updated through {{ $asOf->format('M d, Y') }}</span>
    </div>

    <section class="sedp-kpis">
        @foreach ($metricCards as $metric)
            @foreach ($projects as $project)
                <article class="sedp-kpi {{ $metric['key'] === 'refills' ? 'refill' : $project['accent'] }}">
                    <span class="sedp-kpi-icon"><i class="ti {{ $metric['icon'] }}"></i></span>
                    <span class="sedp-kpi-label">{{ $metric['label'] }} · {{ $project['label'] }}</span>
                    <strong class="sedp-kpi-value">{{ number_format($project[$metric['key']]) }}</strong>
                    <span class="sedp-kpi-note">{{ $metric['note'] }}</span>
                </article>
            @endforeach
        @endforeach
    </section>

    <section class="sedp-grid">
        <article class="sedp-panel">
            <div class="sedp-panel-head">
                <div>
                    <h5>Impact by Project</h5>
                    <p>Key program outcomes through the selected date.</p>
                </div>
            </div>

            <div class="sedp-project-list">
                @foreach ($projects as $project)
                    <div class="sedp-project-row {{ $project['accent'] }}">
                        <div class="sedp-project-name"><span class="sedp-project-dot"></span>{{ $project['label'] }}</div>
                        <div class="sedp-project-metric"><small>Changed lives</small><strong>{{ number_format($project['beneficiaries']) }}</strong></div>
                        <div class="sedp-project-metric"><small>Entrepreneurs</small><strong>{{ number_format($project['entrepreneurs']) }}</strong></div>
                        <div class="sedp-project-metric"><small>330g refills</small><strong>{{ number_format($project['refills']) }}</strong></div>
                    </div>
                @endforeach
            </div>
        </article>

        <aside class="sedp-panel sedp-refill-card">
            <h5>330g Cylinder Refill Count</h5>
            <p>All SEDP refill activity as of {{ $asOf->format('M d, Y') }}.</p>

            <div class="sedp-cylinder-wrap">
                <div class="sedp-refill-total">{{ number_format($totals['refills']) }}<small>Total cylinder refills</small></div>
                {{-- <svg class="sedp-cylinder" viewBox="0 0 100 120" role="img" aria-label="330g LPG cylinder">
                    <path fill="#ffd35a" d="M32 18h36l-5 12h-26z"/>
                    <rect x="26" y="29" width="48" height="78" rx="16" fill="#ffd35a"/>
                    <path fill="#e7b52f" d="M27 49h46v12H27z"/>
                    <rect x="39" y="8" width="22" height="12" rx="3" fill="#f5d677"/>
                    <text x="50" y="72" text-anchor="middle" font-size="10" font-weight="800" fill="#164338">GAZ</text>
                    <text x="50" y="84" text-anchor="middle" font-size="8" font-weight="700" fill="#164338">330g</text>
                </svg> --}}
                <img src="{{ asset('images/330g-removebg.png') }}" alt="330g LPG cylinder" class="sedp-cylinder">
            </div>

            <div class="sedp-refill-rows">
                @foreach ($projects as $project)
                    <div class="sedp-refill-row"><span>{{ $project['label'] }}</span><strong>{{ number_format($project['refills']) }}</strong></div>
                @endforeach
            </div>
        </aside>
    </section>
  </section>
@endsection
