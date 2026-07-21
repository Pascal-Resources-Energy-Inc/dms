<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gaz Lite DMS')</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        :root {
            --brand: #075fc3;
            --brand-dark: #064b99;
            --ink: #10233f;
            --muted: #6c7a90;
            --line: #e5eaf2;
            --bg: #f5f7fb;
            --panel: #ffffff;
            --soft-blue: #eef6ff;
        }

        body {
            background: var(--bg);
            color: var(--ink);
            font-family: "Segoe UI", Arial, Helvetica, sans-serif;
            font-size: 14px;
        }

        .app-shell {
            min-height: 100vh;
            display: flex;
        }

        .sidebar {
            width: 268px;
            flex: 0 0 268px;
            background:
                linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,0) 140px),
                linear-gradient(180deg, #075fc3 0%, #064f9f 100%);
            color: #fff;
            min-height: 100vh;
            position: sticky;
            top: 0;
            overflow-y: auto;
            border-right: 1px solid rgba(255,255,255,.12);
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .brand-logo img {
            width: 138px;
            max-width: 100%;
            height: auto;
            display: block;
            background: #fff;
            border-radius: .55rem;
            padding: .35rem .45rem;
        }

        .brand-subtitle {
            color: rgba(255,255,255,.78);
            font-size: 12px;
            font-weight: 700;
            margin-top: .65rem;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,.86);
            border-radius: .6rem;
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: .55rem;
            padding: .68rem .75rem;
            margin-bottom: .2rem;
            line-height: 1.2;
        }

        .sidebar .nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,.14);
            text-decoration: none;
        }

        .sidebar .nav-link.active {
            background: #fff;
            color: var(--brand);
            box-shadow: 0 .25rem .75rem rgba(0, 38, 92, .16);
        }

        .nav-icon {
            width: 34px;
            height: 34px;
            min-width: 34px;
            border-radius: .55rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255,255,255,.35);
            background: rgba(255,255,255,.1);
            color: currentColor;
        }

        .nav-icon svg {
            width: 17px;
            height: 17px;
            stroke: currentColor;
            stroke-width: 2;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .sidebar .nav-link.active .nav-icon {
            border-color: #cfe2ff;
            background: #eef6ff;
            color: var(--brand);
        }

        .sidebar-card {
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.15);
            border-radius: .75rem;
            padding: 1rem;
            font-size: 13px;
            line-height: 1.55;
            font-weight: 700;
        }

        .sidebar-card-title {
            color: rgba(255,255,255,.72);
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: .35rem;
        }

        .sidebar-user {
            display: flex;
            gap: .7rem;
            align-items: center;
        }

        .sidebar-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            color: var(--brand);
            font-weight: 900;
            flex: 0 0 38px;
        }

        .sidebar-help {
            display: flex;
            gap: .7rem;
            align-items: flex-start;
        }

        .sidebar-help-icon {
            width: 34px;
            height: 34px;
            border-radius: .55rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,.16);
            border: 1px solid rgba(255,255,255,.18);
            flex: 0 0 34px;
        }

        .sidebar-help-icon svg {
            width: 17px;
            height: 17px;
            stroke: #fff;
            stroke-width: 2;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .main-content {
            min-width: 0;
            flex: 1;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .mobile-nav-bar { display: none; }
        .mobile-nav-overlay { display: none; }

        .page-content {
            flex: 1 0 auto;
        }

        .topbar,
        .module-header {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: .6rem;
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            box-shadow: 0 .125rem .35rem rgba(16, 35, 63, .035);
        }

        .welcome h1,
        .module-header h1 {
            font-size: 1.45rem;
            font-weight: 800;
            margin-bottom: .25rem;
        }

        .welcome p,
        .module-header p {
            margin: 0;
            color: var(--muted);
            font-size: .9rem;
            font-weight: 600;
        }

        .filters {
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .filter,
        .profile {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: .5rem;
            padding: .55rem .75rem;
            display: flex;
            align-items: center;
            gap: .6rem;
            min-height: 48px;
        }

        .filter .nav-icon {
            border-color: #cfe2ff;
            background: var(--soft-blue);
            color: var(--brand);
        }

        .filter small,
        .profile small,
        .small-muted,
        .kpi-label,
        .kpi-sub,
        .trend-note {
            color: var(--muted);
            font-size: .78rem;
            font-weight: 700;
        }

        .filter strong,
        .profile strong {
            display: block;
            font-size: .9rem;
        }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--brand);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
        }

        .db-status {
            display: flex;
            gap: .4rem;
            flex-wrap: wrap;
            margin-top: .6rem;
        }

        .db-pill,
        .status-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            background: var(--soft-blue);
            color: var(--brand);
            padding: .25rem .55rem;
            font-size: .75rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .kpi-grid,
        .mini-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem;
        }

        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 1rem;
        }

        .wide,
        .full {
            grid-column: 1 / -1;
        }

        .card,
        .kpi,
        .panel,
        .mini-panel {
            border: 1px solid var(--line) !important;
            border-radius: .6rem;
            background: #fff;
            box-shadow: 0 .125rem .35rem rgba(16, 35, 63, .035);
        }

        .kpi,
        .panel,
        .mini-panel {
            padding: 1rem;
        }

        .kpi-head {
            display: flex;
            gap: .75rem;
            align-items: flex-start;
        }

        .icon {
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            border-radius: .45rem;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            background: var(--brand);
        }

        .tone-blue,
        .tone-green,
        .tone-violet,
        .tone-orange,
        .tone-teal,
        .tone-pink,
        .tone-purple {
            background: var(--brand);
        }

        .kpi-value {
            font-size: 1.45rem;
            line-height: 1.15;
            font-weight: 800;
            margin: 0;
            color: var(--ink);
        }

        .trend-up {
            color: #07936f;
            font-size: .8rem;
            font-weight: 800;
        }

        .panel-title {
            font-size: .98rem;
            font-weight: 800;
            margin-bottom: .85rem;
            display: flex;
            align-items: center;
            gap: .5rem;
            color: var(--ink);
        }

        .section-no {
            background: var(--brand);
            color: #fff;
            width: 22px;
            height: 22px;
            border-radius: .25rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .75rem;
        }

        .chart-lines {
            height: 190px;
            display: grid;
            grid-template-columns: repeat(14, minmax(10px, 1fr));
            align-items: end;
            gap: .35rem;
            padding-top: .75rem;
            border-bottom: 1px solid var(--line);
            background: repeating-linear-gradient(180deg, transparent 0, transparent 37px, #f0f3f8 38px);
        }

        .bar-pair {
            height: 100%;
            display: flex;
            align-items: end;
            justify-content: center;
            gap: 3px;
        }

        .bar {
            width: 8px;
            min-height: 5px;
            border-radius: 4px 4px 0 0;
        }

        .bar.purchase { background: var(--brand); }
        .bar.refill { background: #07936f; }

        .bar-labels {
            display: grid;
            grid-template-columns: repeat(14, minmax(10px, 1fr));
            gap: .35rem;
            margin-top: .4rem;
            color: var(--muted);
            font-size: .7rem;
            text-align: center;
            font-weight: 700;
        }

        .growth-box {
            background: #f8fafc;
            border: 1px solid var(--line);
            border-radius: .5rem;
            padding: 1rem;
            min-height: 190px;
            display: grid;
            align-content: center;
            gap: .75rem;
        }

        .growth-box strong {
            color: #07936f;
            font-size: 1.8rem;
        }

        .table-wrap {
            border: 1px solid var(--line);
            border-radius: .5rem;
            overflow: auto;
            background: #fff;
        }

        .table {
            background: #fff;
            color: var(--ink);
        }

        .table thead th,
        th {
            background: #f8fafc;
            color: var(--muted);
            font-size: .72rem;
            text-transform: uppercase;
            border-top: 0;
            white-space: nowrap;
        }

        .table td,
        .table th {
            vertical-align: middle;
            border-color: var(--line);
            white-space: nowrap;
        }

        .table tbody tr:hover {
            background: #f8fbff;
        }

        .search-form {
            display: flex;
            gap: .5rem;
            min-width: 340px;
            align-items: center;
        }

        .search-form .form-control {
            min-height: 40px;
            border-color: var(--line);
            border-radius: .45rem;
            font-weight: 600;
        }

        .search-form .form-control:focus {
            border-color: #8dbfff;
            box-shadow: 0 0 0 .2rem rgba(7, 95, 195, .12);
        }

        .btn-primary {
            background: var(--brand);
            border-color: var(--brand);
            font-weight: 800;
            border-radius: .45rem;
        }

        .btn-primary:hover {
            background: var(--brand-dark);
            border-color: var(--brand-dark);
        }

        .alert-list,
        .insight-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: .65rem;
        }

        .alert-list li,
        .insight-list li {
            display: grid;
            grid-template-columns: 24px 1fr auto;
            gap: .5rem;
            align-items: center;
            font-size: .85rem;
            font-weight: 700;
        }

        .dot {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: var(--brand);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .7rem;
            font-weight: 800;
        }

        .dot.danger { background: #dc3545; }
        .dot.warning { background: #ffc107; color: #343a40; }
        .dot.info { background: var(--brand); }

        .footer-band {
            flex-shrink: 0;
            margin: 1.5rem -1.25rem -1.25rem;
            padding: 1rem 1.25rem;
            background: #fff;
            border-top: 1px solid var(--line);
            color: var(--muted);
            font-size: .82rem;
            font-weight: 800;
        }

        .footer-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .footer-brand {
            color: var(--ink);
            font-weight: 900;
        }

        .footer-tagline {
            color: var(--muted);
            text-align: center;
        }

        .footer-version {
            color: var(--brand);
            text-align: right;
            white-space: nowrap;
        }

        .badge {
            font-weight: 800;
        }

        @media (max-width: 1199.98px) {
            .kpi-grid,
            .mini-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .dashboard-grid,
            .two-col {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 991.98px) {
            .app-shell {
                display: block;
            }

            .app-shell { display: block; }
            .sidebar {
                position: fixed;
                z-index: 1050;
                top: 0;
                bottom: 0;
                left: 0;
                width: min(86vw, 320px);
                min-height: 100dvh;
                padding: 1rem !important;
                transform: translateX(-105%);
                transition: transform .25s ease;
                box-shadow: 18px 0 50px rgba(15, 23, 42, .25);
            }

            .sidebar.is-open { transform: translateX(0); }
            .sidebar .nav { display: block; }
            .mobile-nav-bar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                margin: -1.25rem -1.25rem 1rem;
                padding: .75rem 1rem;
                color: #fff;
                background: var(--brand);
                box-shadow: 0 .15rem .5rem rgba(7, 95, 195, .2);
            }
            .mobile-nav-brand { font-weight: 800; font-size: .95rem; letter-spacing: .01em; }
            .mobile-nav-toggle { min-width: 42px; min-height: 42px; color: var(--brand); background: #fff; border: 0; border-radius: .5rem; font-size: 1.2rem; }
            .mobile-nav-overlay { position: fixed; z-index: 1040; inset: 0; background: rgba(15, 23, 42, .46); }
            .mobile-nav-overlay.is-active { display: block; }

            .topbar,
            .module-header {
                display: block;
            }

            .filters {
                justify-content: flex-start;
                margin-top: 1rem;
            }

            .search-form {
                min-width: 0;
                margin-top: 1rem;
                width: 100%;
            }
        }

        @media (max-width: 575.98px) {
            .main-content {
                padding: 1rem;
            }

            .mobile-nav-bar { margin: -1rem -1rem 1rem; }
            .module-header { padding: .9rem; }
            .module-header h1 { font-size: 1.25rem; }
            .panel, .kpi, .mini-panel { padding: .85rem; }
            .table-wrap { margin: 0 -.15rem; border-radius: .4rem; }
            .table { font-size: .8rem; }
            .filters .filter, .filters .profile { width: 100%; }

            .kpi-grid,
            .mini-grid,
            .sidebar .nav {
                grid-template-columns: 1fr;
            }

            .search-form {
                display: block;
            }

            .search-form .btn {
                width: 100%;
                margin-top: .5rem;
            }

            .footer-band {
                margin-left: -1rem;
                margin-right: -1rem;
                margin-bottom: -1rem;
            }

            .footer-inner {
                display: block;
                text-align: center;
            }

            .footer-tagline,
            .footer-version {
                margin-top: .35rem;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    @php
        $currentRoute = request()->route() ? request()->route()->getName() : '';
    @endphp
    <svg width="0" height="0" style="position:absolute">
        <symbol id="icon-dashboard" viewBox="0 0 24 24"><path d="M3 13h8V3H3v10z"></path><path d="M13 21h8V11h-8v10z"></path><path d="M13 3v6h8V3h-8z"></path><path d="M3 21h8v-6H3v6z"></path></symbol>
        <symbol id="icon-analytics" viewBox="0 0 24 24"><path d="M4 19V5"></path><path d="M4 19h16"></path><path d="M8 16v-5"></path><path d="M12 16V8"></path><path d="M16 16v-7"></path><path d="M20 16v-3"></path></symbol>
        <symbol id="icon-inventory" viewBox="0 0 24 24"><path d="M21 8l-9-5-9 5 9 5 9-5z"></path><path d="M3 8v8l9 5 9-5V8"></path><path d="M12 13v8"></path></symbol>
        <symbol id="icon-purchases" viewBox="0 0 24 24"><path d="M6 2l1.5 4h9L18 2"></path><path d="M4 6h16l-1.5 14h-13L4 6z"></path><path d="M9 11h6"></path></symbol>
        <symbol id="icon-orders" viewBox="0 0 24 24"><path d="M8 3h8l2 3H6l2-3z"></path><path d="M6 6h12v15H6z"></path><path d="M9 11h6"></path><path d="M9 15h4"></path></symbol>
        <symbol id="icon-sales" viewBox="0 0 24 24"><path d="M4 18l5-5 4 4 7-9"></path><path d="M15 8h5v5"></path></symbol>
        <symbol id="icon-dealers" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"></path><circle cx="9.5" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></symbol>
        <symbol id="icon-outlets" viewBox="0 0 24 24"><path d="M4 10h16l-1-6H5l-1 6z"></path><path d="M5 10v10h14V10"></path><path d="M9 20v-6h6v6"></path></symbol>
        <symbol id="icon-pricing" viewBox="0 0 24 24"><path d="M20 13l-7 7-9-9V4h7l9 9z"></path><circle cx="8.5" cy="8.5" r="1.5"></circle></symbol>
        <symbol id="icon-promotions" viewBox="0 0 24 24"><path d="M20 12v9H4v-9"></path><path d="M2 7h20v5H2z"></path><path d="M12 22V7"></path><path d="M12 7H7.5A2.5 2.5 0 1 1 12 4.5V7z"></path><path d="M12 7h4.5A2.5 2.5 0 1 0 12 4.5V7z"></path></symbol>
        <symbol id="icon-users" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></symbol>
        <symbol id="icon-reports" viewBox="0 0 24 24"><path d="M7 3h7l5 5v13H7z"></path><path d="M14 3v5h5"></path><path d="M10 13h6"></path><path d="M10 17h4"></path></symbol>
        <symbol id="icon-alerts" viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></symbol>
        <symbol id="icon-settings" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21a2 2 0 0 1-4 0v-.09A1.7 1.7 0 0 0 8.6 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1.1-.4H3a2 2 0 0 1 0-4h.09A1.7 1.7 0 0 0 4.6 8.6a1.7 1.7 0 0 0-.34-1.88l-.06-.06A2 2 0 1 1 7.03 3.83l.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1.1V3a2 2 0 0 1 4 0v.09A1.7 1.7 0 0 0 15.4 4.6a1.7 1.7 0 0 0 1.88-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.4 9c.34.3.56.67.6 1.1H21a2 2 0 0 1 0 4h-.09A1.7 1.7 0 0 0 19.4 15z"></path></symbol>
        <symbol id="icon-help" viewBox="0 0 24 24"><path d="M4 12a8 8 0 0 1 16 0v5a3 3 0 0 1-3 3h-2"></path><path d="M4 12v5a3 3 0 0 0 3 3h2"></path><path d="M9 18h6"></path><path d="M9 9a3 3 0 0 1 6 0c0 2-3 2-3 5"></path></symbol>
    </svg>
    <div class="app-shell">
        <aside class="sidebar p-3" id="dashboardSidebar" aria-label="Primary navigation">
            <div class="mb-4">
                <div class="brand-logo">
                    <img src="{{ asset('images/gazlite.png') }}" alt="GazLite">
                </div>
                <div class="brand-subtitle">Ang Sachet ng LPG</div>
            </div>

            <nav class="nav nav-pills flex-column mb-4">
                <a class="nav-link {{ in_array($currentRoute, ['dashboard', 'super-admin.dashboard', 'partner.dashboard']) ? 'active' : '' }}" href="{{ route('dashboard') }}"><span class="nav-icon"><svg><use href="#icon-dashboard"></use></svg></span>Dashboard</a>
                <a class="nav-link {{ $currentRoute === 'dms.analytics' ? 'active' : '' }}" href="{{ route('dms.analytics') }}"><span class="nav-icon"><svg><use href="#icon-analytics"></use></svg></span>Analytics</a>
                <a class="nav-link {{ $currentRoute === 'dms.inventory' ? 'active' : '' }}" href="{{ route('dms.inventory') }}"><span class="nav-icon"><svg><use href="#icon-inventory"></use></svg></span>Inventory</a>
                <a class="nav-link {{ $currentRoute === 'dms.purchases' ? 'active' : '' }}" href="{{ route('dms.purchases') }}"><span class="nav-icon"><svg><use href="#icon-purchases"></use></svg></span>Purchases</a>
                <a class="nav-link {{ $currentRoute === 'dms.orders' ? 'active' : '' }}" href="{{ route('dms.orders') }}"><span class="nav-icon"><svg><use href="#icon-orders"></use></svg></span>Orders</a>
                <a class="nav-link {{ $currentRoute === 'dms.sales' ? 'active' : '' }}" href="{{ route('dms.sales') }}"><span class="nav-icon"><svg><use href="#icon-sales"></use></svg></span>Sales / Sell Through</a>
                <a class="nav-link {{ $currentRoute === 'dms.dealers' ? 'active' : '' }}" href="{{ route('dms.dealers') }}"><span class="nav-icon"><svg><use href="#icon-dealers"></use></svg></span>Dealer Management</a>
                <a class="nav-link {{ $currentRoute === 'dms.outlets' ? 'active' : '' }}" href="{{ route('dms.outlets') }}"><span class="nav-icon"><svg><use href="#icon-outlets"></use></svg></span>Outlet Performance</a>
                <a class="nav-link {{ $currentRoute === 'dms.pricing' ? 'active' : '' }}" href="{{ route('dms.pricing') }}"><span class="nav-icon"><svg><use href="#icon-pricing"></use></svg></span>Tactical Pricing <span class="badge badge-light ml-auto">New</span></a>
                <a class="nav-link {{ $currentRoute === 'dms.promotions' ? 'active' : '' }}" href="{{ route('dms.promotions') }}"><span class="nav-icon"><svg><use href="#icon-promotions"></use></svg></span>Promotions</a>
                @if (auth()->user()->isSuperAdmin())
                    <a class="nav-link {{ $currentRoute === 'dms.users' ? 'active' : '' }}" href="{{ route('dms.users') }}"><span class="nav-icon"><svg><use href="#icon-users"></use></svg></span>Users <span class="badge badge-light ml-auto">{{ \App\User::count() }}</span></a>
                @endif
                <a class="nav-link {{ $currentRoute === 'dms.reports' ? 'active' : '' }}" href="{{ route('dms.reports') }}"><span class="nav-icon"><svg><use href="#icon-reports"></use></svg></span>Reports</a>
                {{-- <a class="nav-link {{ $currentRoute === 'dms.alerts' ? 'active' : '' }}" href="{{ route('dms.alerts') }}"><span class="nav-icon"><svg><use href="#icon-alerts"></use></svg></span>Alerts <span class="badge badge-light ml-auto">12</span></a> --}}
                <a class="nav-link {{ $currentRoute === 'dms.settings' ? 'active' : '' }}" href="{{ route('dms.settings') }}"><span class="nav-icon"><svg><use href="#icon-settings"></use></svg></span>Settings</a>
            </nav>

            <div class="sidebar-card mb-3">
                <div class="sidebar-card-title">Signed in as</div>
                <div class="sidebar-user">
                    <span class="sidebar-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    <div>
                        <div>{{ auth()->user()->name }}</div>
                        <div style="color: rgba(255,255,255,.75); font-size: .78rem;">{{ auth()->user()->roleName() }}</div>
                    </div>
                </div>
            </div>

            <div class="sidebar-card mb-3">
                <div class="sidebar-help">
                    <span class="sidebar-help-icon"><svg><use href="#icon-help"></use></svg></span>
                    <div>
                        <div>Need Help?</div>
                        <div style="color: rgba(255,255,255,.75); font-size: .78rem;">support@gazlite.com</div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-light btn-block font-weight-bold" type="submit">Logout</button>
            </form>
        </aside>

        <div class="mobile-nav-overlay" id="mobileNavOverlay"></div>
        <main class="main-content container-fluid">
            <div class="mobile-nav-bar">
                <span class="mobile-nav-brand">Gaz Lite DMS</span>
                <button class="mobile-nav-toggle" id="mobileNavToggle" type="button" aria-label="Open navigation" aria-controls="dashboardSidebar" aria-expanded="false">☰</button>
            </div>
            <div class="page-content">
                @yield('content')
            </div>
            <footer class="footer-band">
                <div class="footer-inner">
                    <span class="footer-brand">PASCAL RESOURCES ENERGY, INC.</span>
                    <span class="footer-tagline">ONE HARDWARE. ONE CYLINDER. ONE SYSTEM.</span>
                    <span class="footer-version">Distributor Management System (DMS) v1.0</span>
                </div>
            </footer>
        </main>
    </div>
    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        (function () {
            const sidebar = document.getElementById('dashboardSidebar');
            const toggle = document.getElementById('mobileNavToggle');
            const overlay = document.getElementById('mobileNavOverlay');
            if (!sidebar || !toggle || !overlay) return;

            const close = function () {
                sidebar.classList.remove('is-open');
                overlay.classList.remove('is-active');
                toggle.setAttribute('aria-expanded', 'false');
            };
            toggle.addEventListener('click', function () {
                const isOpen = sidebar.classList.toggle('is-open');
                overlay.classList.toggle('is-active', isOpen);
                toggle.setAttribute('aria-expanded', String(isOpen));
            });
            overlay.addEventListener('click', close);
            document.addEventListener('keydown', function (event) { if (event.key === 'Escape') close(); });
            window.addEventListener('resize', function () { if (window.innerWidth > 991) close(); });
        }());
    </script>
</body>
</html>
