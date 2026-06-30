@extends('layouts.dashboard')

@section('title', 'Gaz Lite Partner Dashboard')

@section('content')
    @include('dashboards.partials.command-center', [
        'dashboard' => $dashboard,
        'heading' => 'Welcome back, ' . $user->name . '!',
        'subheading' => 'Here is how ' . ($user->territory ?: $dashboard['territory']) . ' is performing today.',
        'rolePanelTitle' => 'Partner Access Scope',
        'users' => collect([$user]),
        'roleCounts' => collect([$user->role => 1]),
    ])
@endsection

