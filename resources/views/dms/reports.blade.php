@extends('layouts.dashboard')

@section('title', 'Reports')

@section('content')
    <div class="module-header">
        <div>
            <h1>Reports</h1>
            <p>Executive summary of distributor network, sales, inventory, and TimePay status.</p>
        </div>
    </div>

    <section class="mini-grid">
        <div class="mini-panel card border-0"><span class="small-muted">Dealer Units</span><strong>{{ number_format($dashboard['sales']['units']) }}</strong></div>
        <div class="mini-panel card border-0"><span class="small-muted">Sales Value</span><strong>P{{ number_format($dashboard['sales']['value'], 2) }}</strong></div>
        <div class="mini-panel card border-0"><span class="small-muted">Active Dealers</span><strong>{{ number_format($dashboard['dealers']['active']) }}</strong></div>
        <div class="mini-panel card border-0"><span class="small-muted">Active Employees</span><strong>{{ number_format($dashboard['timepay']['active_employees']) }}</strong></div>
    </section>

    <article class="panel card border-0" style="margin-top: 12px;">
        <h2 class="panel-title">Database Health</h2>
        <div class="mini-grid">
            @foreach ($connections as $connection)
                <div class="mini-panel card border-0"><span class="small-muted">{{ $connection['name'] }}</span><strong>{{ $connection['status'] }}</strong></div>
            @endforeach
        </div>
    </article>
@endsection


