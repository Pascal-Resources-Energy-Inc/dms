@extends('layouts.dashboard')

@section('title', 'Gaz Lite Super Admin Dashboard')

@section('content')
    @include('dashboards.partials.command-center', [
        'dashboard' => $dashboard,
        'heading' => 'Welcome back, ' . auth()->user()->name . '!',
        'subheading' => 'Here is how the full Gaz Lite distributor network is performing today.',
        'rolePanelTitle' => 'DMS User Roles',
        'users' => $users,
        'roleCounts' => $roleCounts,
    ])
@endsection

