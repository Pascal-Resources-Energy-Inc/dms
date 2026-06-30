@extends('layouts.dashboard')

@section('title', 'Settings')

@section('content')
    <div class="module-header">
        <div>
            <h1>Settings</h1>
            <p>Database connection status and system configuration overview.</p>
        </div>
    </div>

    <article class="panel card border-0">
        <h2 class="panel-title">Connected Databases</h2>
        <div class="mini-grid">
            @foreach ($connections as $connection)
                <div class="mini-panel card border-0"><span class="small-muted">{{ $connection['name'] }}</span><strong>{{ $connection['status'] }}</strong></div>
            @endforeach
        </div>
    </article>
@endsection


