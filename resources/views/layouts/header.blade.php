<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light" data-color-theme="Green_Theme" data-layout="vertical">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Favicon icon-->
    <link rel="shortcut icon" type="image/png" href="{{asset('images/icon.png')}}" />

    <!-- Core Css -->
    <link rel="stylesheet" href="{{asset('design/assets/css/styles.css')}}" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{asset('design/assets/libs/jvectormap/jquery-jvectormap.css')}}">
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
    
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-light: #3b82f6;
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 78px;
            --topbar-height: 80px;
            --bg-light: #f8fafc;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --transition-duration: 0.3s;
            --content-padding: 32px;
            --sidebar-bg: #ffffff;
            --sidebar-bg-soft: #f0f8ff;
            --sidebar-border: #e2e8f0;
            --sidebar-text: #334155;
            --sidebar-muted: #94a3b8;
            --sidebar-hover: #f0f8ff;
            --sidebar-active: #5dade2;
            --sidebar-active-soft: rgba(93, 173, 226, .16);
        }

        /* Layout Reset - Higher specificity to prevent conflicts */
        /* .main-layout * {
            box-sizing: border-box;
        } */

        .main-layout {
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #D8E9F0;
            min-height: 100vh;
            display: flex; /* Add this */
            flex-direction: column; /* Add this */
        }

        body.main-layout {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
            background-color: #D8E9F0 !important;
            /* background-color: #f3f0f0 !important; */
            overflow-x: hidden !important;
        }

        /* Override existing layout styles */
        .container-scroller {
            position: relative;
            width: 100%;
            height: 100vh;
            overflow: hidden;
        }

        .layout-container {
            position: relative;
            width: 100%;
            min-height: 100vh;
            display: flex;
        }


        .badges {
            color: #FFF;
        }

        .loader {
            position: fixed;
            left: 0px;
            top: 0px;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background: url("{{ asset('/images/loader.gif') }}") 50% 50% no-repeat white;
            opacity: .8;
            background-size: 120px 120px;
        }

        /* Sidebar layout: header and profile stay fixed; only menu items scroll. */
        .main-layout .sidebar {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            height: 100vh !important;
            height: 100dvh !important;
            width: var(--sidebar-width) !important;
            display: flex !important;
            flex-direction: column !important;
            background: var(--sidebar-bg) !important;
            border-right: 1px solid var(--sidebar-border) !important;
            box-shadow: 10px 0 30px rgba(15, 23, 42, .06) !important;
            transition: width var(--transition-duration) cubic-bezier(.4, 0, .2, 1),
                        transform var(--transition-duration) cubic-bezier(.4, 0, .2, 1) !important;
            z-index: 1000 !important;
            overflow-y: hidden !important;
            overflow-x: hidden !important;
        }

        .main-layout .sidebar.collapsed {
            width: var(--sidebar-collapsed-width) !important;
        }

        .main-layout .sidebar-header {
            flex: 0 0 var(--topbar-height) !important;
            min-height: var(--topbar-height) !important;
            padding: 14px 20px !important;
            border-bottom: 1px solid var(--sidebar-border) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .notification-dropdown
        {
            cursor: pointer;
            background: none !important;
        }

        .notification-dropdown:hover
        {
            background: none !important;
        }

        .notification-dropdown .notif-badge
        {
            min-width: 22px; 
            height: 22px; 
            padding: 0 6px;
            font-size: 10px; 
            border-radius: 999px;
            display: inline-flex; 
            align-items: center; 
            justify-content: center;
            background-color: #ff6b59;
            border: 2px solid #fff;
            box-shadow: 0 6px 16px rgba(255, 107, 89, .35);
        }

        .notification-bell-btn {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: 0 10px 24px rgba(220, 38, 38, .22);
        }

        .notification-menu {
            width: min(430px, calc(100vw - 24px));
            border: 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 22px 60px rgba(15, 23, 42, .22);
        }

        .notification-head {
            background: #fff;
            border-bottom: 1px solid #eef2f7;
        }

        .notification-list {
            max-height: 390px;
            overflow-y: auto;
            background: #f8fafc;
        }

        .notification-row {
            display: flex;
            gap: 12px;
            padding: 14px;
            margin-bottom: 8px;
            border: 1px solid #edf2f7;
            border-radius: 8px;
            background: #fff;
            cursor: pointer;
            transition: background-color .15s ease, border-color .15s ease, transform .15s ease;
        }

        .notification-row:hover {
            background: #f9fbff;
            border-color: #dbeafe;
            transform: translateY(-1px);
        }

        .notification-row > .d-flex.align-items-center {
            flex: 1;
            min-width: 0;
        }

        .notification-row > .d-flex.justify-content-end {
            margin-top: 0 !important;
            align-items: center;
            flex: 0 0 auto;
        }

        .notification-row.is-unread {
            border-left: 4px solid #2563eb;
        }

        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
        }

        .notification-icon.client {
            background: #e8f1ff;
            color: #2563eb;
        }

        .notification-icon.transaction {
            background: #e9f8ef;
            color: #16a34a;
        }

        .notification-icon.order {
            background: #fff4de;
            color: #d97706;
        }

        .notification-copy {
            min-width: 0;
            flex: 1;
        }

        .notification-title {
            font-size: 13px;
            line-height: 1.35;
            color: #0f172a;
            margin: 0;
            overflow-wrap: anywhere;
        }

        .notification-row p.fs-3 {
            font-size: 13px !important;
            line-height: 1.35;
            color: #0f172a;
        }

        .notification-row .badges {
            border-radius: 999px;
            padding: 3px 7px;
            font-weight: 700;
        }

        .notification-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 7px;
            align-items: center;
        }

        .notification-chip {
            display: inline-flex;
            align-items: center;
            min-height: 20px;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            background: #eef2f7;
            color: #475569;
        }

        .notification-chip.new {
            background: #e0ecff;
            color: #1d4ed8;
        }

        .notification-action {
            align-self: center;
            flex: 0 0 auto;
        }

        .notification-action .btn {
            border-radius: 999px;
            padding: 5px 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .notification-empty {
            background: #fff;
            padding: 34px 20px;
        }

        .notification-live-toast {
            position: fixed;
            top: 92px;
            right: 22px;
            z-index: 2000;
            display: none;
            min-width: 260px;
            max-width: calc(100vw - 44px);
            padding: 12px 14px;
            border-radius: 8px;
            background: #111827;
            color: #fff;
            box-shadow: 0 18px 45px rgba(15, 23, 42, .32);
        }

        .notification-live-toast.show {
            display: block;
            animation: notificationToastIn .2s ease-out;
        }

        @keyframes notificationToastIn {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .main-layout .sidebar .logo {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 100% !important;
            min-width: 0 !important;
            text-decoration: none !important;
            filter: drop-shadow(0 8px 16px rgba(15, 23, 42, .08));
        }

        .main-layout .sidebar .logo-full {
            display: block;
            width: auto !important;
            max-width: 204px !important;
            height: 46px !important;
            object-fit: contain;
        }

        .main-layout .sidebar .logo-mini {
            display: none;
            width: 44px !important;
            height: 44px !important;
            object-fit: contain;
        }

        .main-layout .sidebar.collapsed .logo-full {
            display: none;
        }

        .main-layout .sidebar.collapsed .logo-mini {
            display: block;
        }

        .main-layout .sidebar-nav {
            flex: 1 1 auto;
            min-height: 0;
            padding: 18px 14px 24px;
            overflow-x: hidden;
            overflow-y: auto;
            overscroll-behavior: contain;
            scrollbar-width: thin;
            scrollbar-color: rgba(148, 163, 184, .48) transparent;
        }

        .main-layout .sidebar-nav::-webkit-scrollbar {
            width: 5px;
        }

        .main-layout .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, .48);
            border-radius: 999px;
        }

        .main-layout .sidebar .nav-section {
            margin: 0;
        }

        .main-layout .sidebar .nav-section-title {
            padding: 0 12px 12px;
            color: var(--sidebar-muted);
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            transition: opacity .2s ease;
        }

        .main-layout .sidebar.collapsed .nav-section-title {
            opacity: 0;
        }

        .main-layout .sidebar .nav-item {
            margin: 4px 0;
        }

        .main-layout .sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            min-height: 46px;
            padding: 10px 12px;
            color: var(--sidebar-text);
            text-decoration: none;
            border: 1px solid transparent;
            border-radius: 8px;
            transition: color .18s ease, background-color .18s ease, border-color .18s ease, box-shadow .18s ease, transform .18s ease;
            font-size: 13px;
            font-weight: 700;
            position: relative;
        }

        .main-layout .sidebar .nav-link:hover,
        .main-layout .sidebar .nav-link:focus-visible {
            background: var(--sidebar-hover);
            border-color: rgba(93, 173, 226, .28);
            color: #1d4f73;
            outline: none;
            transform: translateX(2px);
        }

        .main-layout .sidebar .nav-link.active {
            color: #1d4f73;
            background: linear-gradient(135deg, rgba(93, 173, 226, .18) 0%, rgba(240, 248, 255, .95) 100%);
            border-color: rgba(93, 173, 226, .38);
            box-shadow: inset 3px 0 0 var(--sidebar-active), 0 10px 24px rgba(93, 173, 226, .12);
        }

        .main-layout .sidebar .nav-link[aria-expanded="true"] {
            color: #1d4f73;
            background: #f8fbff;
            border-color: rgba(93, 173, 226, .22);
        }

        .main-layout .sidebar .nav-link > .ti-chevron-down {
            color: var(--sidebar-muted);
            font-size: 12px;
            transition: transform .2s ease, color .2s ease, opacity .2s ease;
        }

        .main-layout .sidebar .nav-link[aria-expanded="true"] > .ti-chevron-down {
            color: var(--sidebar-active);
            transform: rotate(180deg);
        }

        .main-layout .sidebar .nav-icon {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #64748b;
            font-size: 17px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            transition: color .18s ease, background-color .18s ease, border-color .18s ease;
        }

        .main-layout .sidebar .nav-link:hover .nav-icon,
        .main-layout .sidebar .nav-link.active .nav-icon,
        .main-layout .sidebar .nav-link[aria-expanded="true"] .nav-icon {
            color: #1d4f73;
            background: rgba(93, 173, 226, .18);
            border-color: rgba(93, 173, 226, .32);
        }

        .main-layout .sidebar .nav-text {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            transition: opacity .2s ease, width .2s ease;
        }

        .main-layout .sidebar.collapsed .nav-text,
        .main-layout .sidebar.collapsed .nav-link > .ti-chevron-down {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }

        .main-layout .sidebar.collapsed .nav-link {
            justify-content: center;
            gap: 0;
            padding-inline: 10px;
            transform: none;
        }

        .main-layout .sidebar.collapsed .collapse {
            display: none !important;
        }

        .main-layout .sidebar .collapse .nav {
            margin: 6px 0 8px 15px !important;
            padding: 6px 0 6px 17px;
            border-left: 1px solid rgba(93, 173, 226, .28);
        }

        .main-layout .sidebar .collapse .nav-link {
            min-height: 36px;
            padding: 8px 11px;
            font-size: 12px !important;
            color: #64748b;
            border-radius: 8px;
            font-weight: 650;
        }

        .main-layout .sidebar .collapse .nav-link::before {
            width: 5px;
            height: 5px;
            content: "";
            flex: 0 0 auto;
            border-radius: 999px;
            background: rgba(100, 116, 139, .45);
        }

        .main-layout .sidebar .collapse .nav-link:hover,
        .main-layout .sidebar .collapse .nav-link.active {
            color: #1d4f73;
            background: rgba(93, 173, 226, .13);
            box-shadow: none;
        }

        .main-layout .sidebar .collapse .nav-link.active::before {
            background: var(--sidebar-active);
        }

        .main-layout .sidebar .badge {
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--sidebar-bg);
            box-shadow: 0 8px 18px rgba(220, 38, 38, .35);
        }

        .main-layout .sidebar .nav-badge {
            position: absolute;
            top: -7px;
            right: -9px;
            z-index: 2;
            font-size: 10px;
            line-height: 1;
        }

        .main-layout .sidebar-footer {
            position: static;
            flex: 0 0 auto;
            padding: 14px;
            border-top: 1px solid var(--sidebar-border);
            background: var(--sidebar-bg);
        }

        .main-layout .sidebar .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            padding: 11px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fbff;
            transition: padding .2s ease;
            position: relative;
        }

        .main-layout .sidebar .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            flex-shrink: 0;
            object-fit: cover;
            border: 2px solid #ffffff;
            box-shadow: 0 0 0 2px rgba(93, 173, 226, .35);
        }

        .main-layout .sidebar .user-info {
            flex: 1;
            min-width: 0;
            transition: opacity .2s ease, width .2s ease;
        }

        .main-layout .sidebar .user-name {
            overflow: hidden;
            margin: 0;
            color: #1e293b;
            font-size: 13px;
            font-weight: 700;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .main-layout .sidebar .user-role {
            overflow: hidden;
            margin: 2px 0 0;
            color: var(--text-muted);
            font-size: 10px;
            font-weight: 600;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .main-layout .sidebar .user-profile::after {
            position: absolute;
            left: 39px;
            top: 36px;
            width: 10px;
            height: 10px;
            content: "";
            background: #22c55e;
            border: 2px solid #ffffff;
            border-radius: 50%;
        }

        .main-layout .sidebar.collapsed .user-info {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }

        .main-layout .sidebar.collapsed .sidebar-footer {
            padding: 10px;
        }

        .main-layout .sidebar.collapsed .user-profile {
            padding: 8px 6px;
            justify-content: center;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s ease;
            padding-top: 80px; /* Add top padding to account for fixed topbar */
        }

        .sidebar.collapsed ~ .main-content {
            margin-left: var(--sidebar-collapsed-width);
        }
        .main-layout .topbar {
            /* background: #5BC2E7 !important; */
            background: linear-gradient(135deg, rgba(3, 58, 128, .94), rgba(7, 95, 195, .9)), linear-gradient(135deg, #063f8b, #0a74d7);
            border-bottom: 1px solid var(--border-color) !important;
            padding: 0 30px !important;
            height: 80px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            position: fixed !important;
            top: 0 !important;
            left: var(--sidebar-width) !important;
            width: calc(100% - var(--sidebar-width)) !important;
            z-index: 999 !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
            border-radius: 0 !important;
            transition: all 0.3s ease !important;
            isolation: isolate;
        }

        .main-layout .topbar::after {
            position: absolute;
            inset: auto 0 0;
            height: 1px;
            content: "";
            background: rgba(255, 255, 255, .14);
            pointer-events: none;
        }

        .sidebar.collapsed ~ .main-content .topbar {
            left: var(--sidebar-collapsed-width) !important;
            width: calc(100% - var(--sidebar-collapsed-width)) !important;
        }

        .main-layout .topbar-left {
            display: flex !important;
            align-items: center !important;
            gap: 16px !important;
            min-width: 0;
        }

        .main-layout .sidebar-toggle {
            background: none !important;
            border: none !important;
            padding: 8px !important;
            border-radius: 6px !important;
            cursor: pointer !important;
            color: #FFFF !important;
            transition: all 0.2s ease !important;
        }

        .main-layout .sidebar-toggle:hover {
            color: #FFFF !important;
            color: var(--primary-color) !important;
        }

        .main-layout .welcome-message h6 {
            margin: 0 !important;
            color: #FFFFFF !important;
            font-weight: 600 !important;
            font-size: 16px !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
        }

        .main-layout .welcome-message small {
            color: #FFFFFF !important;
            font-size: 13px !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
        }

        .main-layout .topbar-right {
            display: flex !important;
            align-items: center !important;
            gap: 16px !important;
            min-width: 0;
            justify-content: flex-end;
        }

        .main-layout .topbar-action {
            flex: 0 0 auto;
        }

        .main-layout .mobile-search-toggle {
            display: none !important;
            width: 40px;
            height: 40px;
            align-items: center;
            justify-content: center;
            padding: 0;
            color: #fff;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .2);
            border-radius: 10px;
            transition: background .2s ease, transform .2s ease;
        }

        .main-layout .mobile-search-toggle:hover,
        .main-layout .mobile-search-toggle:focus-visible {
            color: #fff;
            background: rgba(255, 255, 255, .22);
            transform: translateY(-1px);
        }

        .main-layout .guest-order-link {
            min-height: 40px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            padding: 8px 14px !important;
            color: #fff !important;
            font-size: 13px !important;
            font-weight: 800 !important;
            text-decoration: none !important;
            white-space: nowrap !important;
            background: #16a34a !important;
            border: 1px solid rgba(255, 255, 255, .22) !important;
            border-radius: 8px !important;
            box-shadow: 0 10px 22px rgba(22, 163, 74, .22) !important;
        }

        .main-layout .guest-order-link:hover {
            color: #fff !important;
            background: #15803d !important;
        }

        .main-layout .loyalty-scan-link {
            min-height: 40px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            padding: 8px 14px !important;
            color: #fff !important;
            font-size: 13px !important;
            font-weight: 800 !important;
            text-decoration: none !important;
            white-space: nowrap !important;
            background: #0f766e !important;
            border: 1px solid rgba(255, 255, 255, .22) !important;
            border-radius: 8px !important;
            box-shadow: 0 10px 22px rgba(15, 118, 110, .22) !important;
        }

        .main-layout .loyalty-scan-link:hover {
            color: #fff !important;
            background: #115e59 !important;
        }

        .loyalty-scan-modal .modal-content {
            border: 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .22);
        }

        .loyalty-scan-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 18px 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e4e7ec;
        }

        .loyalty-scan-reader {
            overflow: hidden;
            min-height: 260px;
            background: #0f172a;
            border-radius: 8px;
        }

        .loyalty-scan-status {
            padding: 12px 14px;
            color: #475467;
            font-size: 12px;
            font-weight: 700;
            background: #f8fafc;
            border: 1px solid #e4e7ec;
            border-radius: 8px;
        }

        .loyalty-scan-status.is-success {
            color: #166534;
            background: #f0fdf4;
            border-color: #bbf7d0;
        }

        .loyalty-scan-status.is-error {
            color: #991b1b;
            background: #fef2f2;
            border-color: #fecaca;
        }

        .main-layout .search-container {
            position: relative !important;
        }

        .main-layout .search-input {
            width: 300px !important;
            height: 40px !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 20px !important;
            padding: 0 16px 0 30px !important;
            /* background: #D8E9F0 !important; */
            transition: all 0.2s ease !important;
            font-size: 14px !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
            outline: none !important;
            color: #1e293b !important;
        }

        .main-layout .search-input::placeholder{
            color: #5BC2E7 !important;
        }

        .main-layout .search-input:focus {
            border-color: var(--danger-color) !important;
            background: white !important;
        }

        .main-layout .search-icon {
            position: absolute !important;
            right: 16px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            color: #5BC2E7 !important;
        }

        .main-layout .profile-dropdown {
            position: relative !important;
            background: none !important;
            border: none !important;
            padding: 8px !important;
            border-radius: 50% !important;
            cursor: pointer !important;
            color: #FFFF !important;
            transition: all 0.2s ease !important;
        }

        .main-layout .profile-dropdown:hover {
            color: var(--primary-color) !important;
        }

        .main-layout .sidebar-toggle,
        .main-layout .profile-dropdown,
        .main-layout .notification-bell-btn,
        .main-layout .mobile-search-toggle {
            flex: 0 0 auto;
        }

        .main-layout .sidebar-toggle:focus-visible,
        .main-layout .profile-dropdown:focus-visible,
        .main-layout .notification-bell-btn:focus-visible,
        .main-layout .guest-order-link:focus-visible,
        .main-layout .loyalty-scan-link:focus-visible {
            outline: 3px solid rgba(255, 255, 255, .72);
            outline-offset: 2px;
        }

        .main-layout .notification-badge {
            position: absolute !important;
            top: 0 !important;
            right: 0 !important;
            width: 18px !important;
            height: 18px !important;
            background: #ef4444 !important;
            color: white !important;
            border-radius: 50% !important;
            font-size: 10px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-weight: 600 !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
        }

        .main-layout .profile-img {
            width: 36px !important;
            height: 36px !important;
            border-radius: 50% !important;
            border: 2px solid var(--border-color) !important;
        }

        .content-area.rewards-page {
            padding: 20px;
            position: static !important;
            left: auto !important;
            right: auto !important;
            transition: none !important;
        }

        /* Content Area */
        .content-area {
            padding: 30px 30px 10px 30px;
            position: absolute;
            left: var(--sidebar-width);
            right: 0;
            transition: all 0.3s ease;
        }

        .sidebar.collapsed ~ .main-content .content-area {
            left: var(--sidebar-collapsed-width);
        }

        @media (max-width: 768px) {
            .content-area {
                left: 0;
            }
        }

        .main-layout .dropdown-menu {
            border: none !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
            border-radius: 12px !important;
            padding: 16px 0 !important;
            min-width: 280px !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
        }

        .main-layout .dropdown-item {
            padding: 12px 20px !important;
            border: none !important;
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            transition: all 0.2s ease !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
            font-size: 14px !important;
            color: #334155 !important;
        }

        .main-layout .dropdown-item:hover {
            background: #f1f5f9 !important;
            color: var(--primary-color) !important;
        }

        .main-layout .profile-info {
            padding: 15px 20px !important;
            border-bottom: 1px solid var(--border-color) !important;
            margin-bottom: 8px !important;
        }

        .main-layout .profile-info h6 {
            margin: 0 !important;
            font-weight: 600 !important;
            color: #1e293b !important;
            font-size: 14px !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
        }

        .main-layout .profile-info small {
            color: var(--text-muted) !important;
            font-size: 12px !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
        }

        /* Mobile Responsiveness */
        @media (max-width: 1024px) {
            .main-layout .topbar {
                padding: 0 20px !important;
            }

            .main-layout .topbar-right {
                gap: 10px !important;
            }

            .main-layout .search-input {
                width: min(24vw, 240px) !important;
            }

            .main-layout .guest-order-link,
            .main-layout .loyalty-scan-link {
                min-height: 38px !important;
                padding: 8px 11px !important;
            }
        }

        @media (max-width: 900px) and (min-width: 769px) {
            .main-layout .topbar { padding: 0 16px !important; }
            .main-layout .topbar-right { gap: 8px !important; }
            .main-layout .guest-order-link,
            .main-layout .loyalty-scan-link {
                width: 40px !important;
                padding: 0 !important;
                justify-content: center;
            }
            .main-layout .guest-order-link span,
            .main-layout .loyalty-scan-link span {
                position: absolute;
                width: 1px;
                height: 1px;
                padding: 0;
                margin: -1px;
                overflow: hidden;
                clip: rect(0, 0, 0, 0);
                white-space: nowrap;
                border: 0;
            }
            .main-layout .search-input { width: min(21vw, 190px) !important; }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.mobile-open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .content-area {
                padding: 16px;
            }
            .main-layout .topbar {
                left: 0 !important;
                right: 0 !important;
                width: 100% !important;
                padding: 0 16px !important;
                height: 64px !important;
            }
            
            .main-content {
                margin-left: 0;
                padding-top: 64px;
            }

            .main-layout .topbar-left {
                gap: 10px !important;
            }

            .main-layout .welcome-message h6 {
                font-size: 14px !important;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .main-layout .welcome-message small {
                display: none;
            }

            .main-layout .mobile-search-toggle {
                display: inline-flex !important;
            }

            .main-layout .topbar-right .search-container {
                display: none !important;
            }

            .main-layout .topbar.search-active .topbar-left,
            .main-layout .topbar.search-active > .dropdown,
            .main-layout .topbar.search-active > .topbar-right > :not(.search-container) {
                display: none !important;
            }

            .main-layout .topbar.search-active .topbar-right {
                display: flex !important;
                flex: 1 1 auto;
                min-width: 0;
            }

            .main-layout .topbar.search-active .topbar-right .search-container {
                display: block !important;
                flex: 1 1 auto;
                margin: 0 !important;
            }

            .main-layout .topbar.search-active .search-input {
                width: 100% !important;
                height: 42px !important;
                padding-right: 42px !important;
            }

            .main-layout .guest-order-link,
            .main-layout .loyalty-scan-link {
                width: 40px !important;
                min-height: 40px !important;
                justify-content: center;
                padding: 0 !important;
                border-radius: 10px !important;
            }

            .main-layout .guest-order-link span,
            .main-layout .loyalty-scan-link span {
                position: absolute;
                width: 1px;
                height: 1px;
                padding: 0;
                margin: -1px;
                overflow: hidden;
                clip: rect(0, 0, 0, 0);
                white-space: nowrap;
                border: 0;
            }
        }

        @media (max-width: 480px) {
            .main-layout .topbar {
                padding: 0 12px !important;
            }

            .main-layout .welcome-message {
                display: none;
            }

            .main-layout .topbar-right {
                gap: 6px !important;
            }
        }

        @media (max-width: 360px) {
            .main-layout .topbar { padding: 0 8px !important; }
            .main-layout .topbar-right { gap: 4px !important; }
            .main-layout .sidebar-toggle,
            .main-layout .mobile-search-toggle,
            .main-layout .notification-bell-btn,
            .main-layout .guest-order-link,
            .main-layout .loyalty-scan-link { width: 38px !important; height: 38px !important; min-height: 38px !important; }
            .main-layout .profile-dropdown { padding: 4px !important; }
            .main-layout .profile-img { width: 34px !important; height: 34px !important; }
        }

        /* Overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        @media (max-width: 768px) {
            .sidebar-overlay.active {
                display: block;
            }
        }

        /* Custom responsive font sizes */
        @media (max-width: 480px) {
            .font {
                font-size: 8px;
            }
        }

        /* Shared modal responsiveness: keeps every dialog usable on narrow and short screens. */
        .modal {
            --bs-modal-border-radius: 14px;
        }

        .modal .modal-content {
            max-height: calc(100dvh - 32px);
            overflow: hidden;
        }

        .modal .modal-header,
        .modal .modal-footer {
            flex: 0 0 auto;
        }

        .modal .modal-header > :first-child,
        .modal .modal-title {
            min-width: 0;
        }

        .modal .modal-title {
            overflow-wrap: anywhere;
        }

        .modal .modal-body {
            min-height: 0;
            overflow-x: hidden;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .modal-dialog-scrollable {
            height: calc(100dvh - 3.5rem);
        }

        .modal-dialog-scrollable .modal-content {
            display: flex;
            flex-direction: column;
            max-height: 100%;
        }

        .modal-dialog-scrollable .modal-body {
            flex: 1 1 auto;
            min-height: 0;
            overscroll-behavior: contain;
            scrollbar-width: thin;
            scrollbar-color: #98a2b3 transparent;
        }

        .modal-dialog-scrollable .modal-body::-webkit-scrollbar {
            width: 8px;
        }

        .modal-dialog-scrollable .modal-body::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border: 2px solid transparent;
            border-radius: 999px;
            background-clip: padding-box;
        }

        .modal .modal-body img,
        .modal .modal-body video,
        .modal .modal-body canvas {
            max-width: 100%;
            height: auto;
        }

        .modal .form-control,
        .modal .form-select,
        .modal .btn,
        .modal .form-check-input {
            touch-action: manipulation;
        }

        .modal .btn:focus-visible,
        .modal .form-control:focus,
        .modal .form-select:focus {
            position: relative;
            z-index: 1;
        }

        @media (max-width: 992px) {
            .modal .modal-dialog:not([class*="modal-fullscreen"]) {
                width: calc(100% - 32px);
                max-width: min(720px, calc(100% - 32px));
                margin: 16px auto;
            }

            .modal .modal-content {
                max-height: calc(100dvh - 32px);
            }

            .modal .modal-dialog-scrollable .modal-content {
                max-height: calc(100dvh - 32px);
            }

            .modal .modal-dialog-scrollable {
                height: calc(100dvh - 32px);
            }
        }

        @media (max-width: 576px) {
            .modal .modal-dialog:not([class*="modal-fullscreen"]) {
                width: calc(100% - 16px);
                max-width: calc(100% - 16px);
                margin: 8px auto;
            }

            .modal .modal-content,
            .modal .modal-dialog-scrollable .modal-content {
                max-height: calc(100dvh - 16px) !important;
                border-radius: 14px !important;
            }

            .modal .modal-dialog-scrollable {
                height: calc(100dvh - 16px);
            }

            .modal .modal-header {
                align-items: flex-start;
                gap: 10px;
                padding: 14px 16px !important;
            }

            .modal .modal-title {
                font-size: 16px !important;
                line-height: 1.3;
            }

            .modal .modal-body {
                padding: 14px 16px !important;
            }

            .modal .modal-footer {
                gap: 8px;
                padding: 12px 16px 16px !important;
            }

            .modal .modal-footer .btn {
                min-height: 42px;
            }

            .modal .form-control,
            .modal .form-select,
            .modal .input-group-text {
                min-height: 42px;
            }

            .modal .row {
                --bs-gutter-x: 1rem;
            }
        }

        @media (max-height: 640px) and (max-width: 768px) {
            .modal .modal-dialog:not([class*="modal-fullscreen"]) {
                margin: 6px auto;
            }

            .modal .modal-content,
            .modal .modal-dialog-scrollable .modal-content {
                max-height: calc(100dvh - 12px);
            }

            .modal .modal-dialog-scrollable {
                height: calc(100dvh - 12px);
            }

            .modal .modal-header,
            .modal .modal-body,
            .modal .modal-footer {
                padding-top: 10px;
                padding-bottom: 10px;
            }
        }

        @media (max-width: 768px) {
            .font {
                font-size: 12px;
            }
        }
        transaction-item .row {
            min-height: 40px !important; /* Adjust this value */
            padding: 11px 15px !important; /* Reduce padding */
        }

        .transaction-item .avatar-circle {
            width: 35px !important;
            height: 35px !important;
        }

        .transaction-item h6 {
            font-size: 13px !important;
            line-height: 1 !important;
        }

        .footer {
            border-top: 1px solid #676565ff;
            padding: 2rem 0 1.5rem 0;
            margin-top: 50px !important; /* Change this from 170px to auto */
            margin-left: 0;
            width: 100%;
            background: none;
            position: relative; /* Add this to ensure proper positioning */
            clear: both; /* Add this to clear any floating elements */
        }

      .page-wrapper .footer {
          position: relative;
          width: 100%;
      }

      .footer .container-fluid {
          padding-left: 1rem;
          padding-right: 1rem;
      }

      .footer .footer-content {
          display: flex;
          flex-direction: column;
          align-items: center;
          gap: 1.5rem;
          position: relative;
      }

      .footer .company-info {
          display: flex;
          align-items: center;
          gap: 0.75rem;
          position: absolute;
          left: 0;
          top: 0;
      }


      .footer-right-image img {
        position: absolute; 
        width: 150px; /* Adjust size as needed */
        height: auto;
        margin-left: 435px;
        margin-top: -43px;
      }

      @media (max-width: 768px) {
         .footer-right-image img {
           margin-left: -75px;
           margin-top: -2px;
        }

        .footer-right-image {
          margin-top: 1rem;
        }
      }


      .footer .nav-links-container {
          display: flex;
          flex-direction: column;
          align-items: center;
          gap: 0.4rem;
          margin-top: 0;
      }
      .footer .company-logo img {
          width: 350px !important;
          height: 70px !important;
      }

      .footer .company-logo {
          width: 24px;
          height: 24px;
          border-radius: 4px;
          margin-left: 80px; 
          display: flex;
          align-items: center;
          justify-content: center;
          color: white;
          font-weight: bold;
          font-size: 12px;
      }

      @media (max-width: 768px) {
         .footer .company-logo img {
           margin-left: -57px;
           margin-top: -5px;
        }
      }

      .footer .company-text {
          color: #6c757d;
          font-size: 14px;
          margin: 0;
      }

      .footer .nav-links {
          display: flex;
          gap: 2rem;
          margin: 0;
          padding: 0;
          list-style: none;
          flex-wrap: wrap;
          justify-content: center;
      }

      .footer .nav-links a {
          color: #6c757d;
          text-decoration: none;
          font-size: 14px;
          transition: color 0.2s ease;
          font-weight: 500;
      }

      .footer .nav-links a:hover {
          color: #2e7fe1ff;
      }

      .footer .divider {
          width: 100%;
          max-width: 300px;
          height: 1px;
          background-color: #ffffff4b;
          margin: 0.5rem 0;
      }

      .footer .social-links {
          display: flex;
          gap: 1.5rem;
          margin: 0;
          padding: 0;
          list-style: none;
      }

      .footer .social-links a {
          color: #6c757d;
          font-size: 20px;
          transition: color 0.2s ease;
          display: flex;
          align-items: center;
          justify-content: center;
      }

      .footer .social-links a:hover {
          color: #2e7fe1ff;
      }

    .modal-select2 .modal-body {
        max-height: calc(100vh - 200px);
        overflow-y: auto;
    }

    @media (max-width: 768px) {
        .footer .company-info {
            position: static;
            align-self: center;
            margin-bottom: 1rem;
        }
        
        .footer .footer-content {
            align-items: center;
        }
        
        .footer .nav-links-container {
            margin-top: 0;
        }
        
        .footer .nav-links {
            gap: 1.5rem;
        }
        
        .footer .nav-links a {
            font-size: 13px;
        }
        
        .footer .social-links {
            gap: 1rem;
        }
        
        .footer .social-links a {
            font-size: 18px;
        }
    }

    @media (max-width: 480px) {
        .footer .nav-links {
            gap: 1rem;
        }
        
        .footer .nav-links a {
            font-size: 12px;
        }
    }
    </style>
    
    @yield('css')
</head>
<body class="main-layout">
    <div id="loader" style="display:none;" class="loader"></div>
    
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar" aria-label="Main navigation">
        <div class="sidebar-header">
            <a href="{{url('/')}}" class="logo" aria-label="Go to dashboard">
                <!-- Full logo (for expanded sidebar) -->
                {{-- <img src="{{asset('images/logo_mo.png')}}"
                    class="logo-full"
                    alt="Logo-Full" /> --}}
                <img src="{{asset('images/gazlite.png')}}" class="logo-full" alt="Logo-Full" />

                <!-- Mini logo (for collapsed sidebar) -->
                <img src="{{asset('images/logo_nya.png')}}"
                    class="logo-mini"
                    alt="" />
            </a>
        </div>
        <div class="sidebar-nav">
            @php
                $sidebarUser = auth()->user();
                $sidebarRole = $sidebarUser->role ?? '';
                $sidebarWarehouse = strtolower((string) ($sidebarUser->warehouse ?? ''));
                $sidebarIsAdmin = $sidebarRole === 'Admin';
                $sidebarIsSedp = $sidebarRole === 'SEDP';
                $sedpCanAccess = function ($field) use ($sidebarUser, $sidebarIsAdmin, $sidebarIsSedp) {
                    if ($sidebarIsAdmin) {
                        return true;
                    }

                    return $sidebarIsSedp && (($sidebarUser->{$field} ?? null) === 'on');
                };
                $sidebarAccessPermissions = json_decode($sidebarUser->access_permissions ?? '{}', true);
                $sidebarAccessPermissions = is_array($sidebarAccessPermissions) ? $sidebarAccessPermissions : [];
                $sedpCanAccessPermission = function ($module, $submodule, $action = 'view') use ($sidebarAccessPermissions, $sidebarIsAdmin, $sidebarIsSedp) {
                    if ($sidebarIsAdmin) {
                        return true;
                    }

                    if (!$sidebarIsSedp) {
                        return false;
                    }

                    $actions = $sidebarAccessPermissions[$module][$submodule] ?? [];

                    return is_array($actions) && in_array($action, $actions, true);
                };
                $hasDetailedSidebarAccess = !empty($sidebarAccessPermissions);
                $canAccessStandardReports = $sidebarIsAdmin || (
                    $sidebarIsSedp && (
                        $sedpCanAccessPermission('reports', 'sales') ||
                        $sedpCanAccessPermission('reports', 'operations') ||
                        (!$hasDetailedSidebarAccess && $sedpCanAccess('can_access_reports'))
                    )
                );
                $canAccessSedpReports = $sidebarIsAdmin || (
                    $sidebarIsSedp && (
                        $sedpCanAccessPermission('reports', 'sedp') ||
                        (!$hasDetailedSidebarAccess && $sedpCanAccess('can_access_reports'))
                    )
                );
                $canAccessAnyReports = $canAccessStandardReports || $canAccessSedpReports;
                $standardReportRoutes = ['dsr', 'aging', 'dpo', 'isl', 'monthly-sales', 'voucher-history'];
                $sedpReportRoutes = ['signup-incentives', 'repeat-purchase-incentives', 'aging-report-dealer', 'aging-report-customer'];
                $visibleReportRoutes = array_merge(
                    $canAccessStandardReports ? $standardReportRoutes : [],
                    $canAccessSedpReports ? $sedpReportRoutes : []
                );
                $canAccessUsersModule = $sidebarIsAdmin || (
                    $sidebarIsSedp &&
                    (
                        ($sidebarUser->can_add ?? null) === 'on' ||
                        ($sidebarUser->can_edit ?? null) === 'on' ||
                        ($sidebarUser->can_delete ?? null) === 'on'
                    )
                );
            @endphp
            <div class="nav-section">
                <div class="nav-section-title">HOME</div>
                @if($sidebarIsAdmin || $sidebarIsSedp)
                <div class="nav-item">
                    <a href="{{url('/')}}" class="nav-link @if(Route::currentRouteName() == 'home')active @endif">
                        <div class="nav-icon">
                            <i class="ti ti-layout-dashboard"></i>
                        </div>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>
                @elseif(auth()->user()->role == "Area Distributor")
                    <div class="nav-item">
                        <a href="{{url('ad-dashboard')}}" class="nav-link @if(Route::currentRouteName() == 'ad-dashboard') active @endif">
                            <div class="nav-icon">
                                <i class="ti ti-layout-dashboard"></i>
                            </div>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </div>
                    {{-- <div class="nav-item">
                        <a href="{{url('/ad-transactions')}}" class="nav-link @if(Route::currentRouteName() == 'ad-transactions') active @endif">
                            <div class="nav-icon">
                                <i class="ti ti-cash"></i>
                            </div>
                            <span class="nav-text">Transactions</span>
                        </a>
                    </div> --}}
                    <div class="nav-item">
                        <a href="javascript:void(0)" class="nav-link" data-bs-toggle="collapse" data-bs-target="#suppliesMenu" aria-expanded="{{ in_array(Route::currentRouteName(), ['product','inventories','inventory-transfers.index']) ? 'true' : 'false' }}">
                            <div class="nav-icon">
                                <i class="ti ti-shopping-cart-plus"></i>
                            </div>
                            <span class="nav-text">Inventory and Pricing</span>
                            <i class="ti ti-chevron-down ms-auto"></i>
                        </a>

                        <div class="collapse @if(in_array(Route::currentRouteName(), ['product','inventories','inventory-transfers.index'])) show @endif"
                            id="suppliesMenu">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item">
                                    <a href="{{ url('/products') }}" class="nav-link @if(Route::currentRouteName() == 'products') active @endif" style="font-size: 14px">Products</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('charges') }}" class="nav-link @if(Route::currentRouteName() == 'charges')active @endif" style="font-size: 14px">Charges and Discount</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('inventory-transfers.index') }}" class="nav-link @if(Route::currentRouteName() == 'inventory-transfers.index') active @endif" style="font-size: 14px">Inventory Movement</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="nav-item">
                        <a href="javascript:void(0)" class="nav-link" data-bs-toggle="collapse" data-bs-target="#partnersMenu" aria-expanded="{{ in_array(Route::currentRouteName(), ['md-ads','dealer-ads','customer-ads','my-customers','charges']) ? 'true' : 'false' }}">
                            <div class="nav-icon">
                                <i class="ti ti-id-badge-2"></i>
                            </div>
                            <span class="nav-text">Partners</span>
                            <i class="ti ti-chevron-down ms-auto"></i>
                        </a>

                        <div class="collapse @if(in_array(Route::currentRouteName(), ['md-ads','dealer-ads','customer-ads','my-customers','charges'])) show @endif"
                            id="partnersMenu">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item">
                                    <a href="{{url('/md-ads')}}" class="nav-link @if(Route::currentRouteName() == 'md-ads')active @endif" style="font-size: 14px">Mega Dealer</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{url('/dealer-ads')}}" class="nav-link @if(Route::currentRouteName() == 'dealer-ads')active @endif" style="font-size: 14px">Dealer</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('customer-ads') }}" class="nav-link @if(Route::currentRouteName() == 'customer-ads') active @endif" style="font-size: 14px">Customers</a>
                                </li>
                                {{-- <li class="nav-item">
                                    <a href="{{url('/my-customers')}}" class="nav-link @if(Route::currentRouteName() == 'my-customers')active @endif" style="font-size: 14px">My Customers</a>
                                </li> --}}
                                
                            </ul>
                        </div>
                    </div>
                    {{-- <div class="nav-item">
                        <a href="{{url('/dealer-ads')}}" class="nav-link @if(Route::currentRouteName() == 'dealer-ads')active @endif">
                            <div class="nav-icon">
                                <i class="ti ti-id-badge-2"></i>
                            </div>
                            <span class="nav-text">My Dealers</span>
                        </a>
                    </div> --}}
                    {{-- <div class="nav-item">
                        <a href="{{url('/orders')}}" class="nav-link @if(Route::currentRouteName() == 'orders')active @endif">
                            <div class="nav-icon">
                                <i class="ti ti-building-store"></i>
                            </div>
                            <span class="nav-text">Orders</span>
                        </a>
                    </div> --}}
                    <div class="nav-item">
                        <a href="{{ route('orders') }}" class="nav-link {{ request()->routeIs('orders*') ? 'active' : '' }}">
                            <div class="nav-icon position-relative">
                                <i class="ti ti-building-store"></i>
                                @if(($pendingOrdersCount ?? 0) > 0)
                                    <span class="badge rounded-pill bg-danger nav-badge">
                                        {{ $pendingOrdersCount > 99 ? '99+' : $pendingOrdersCount }}
                                    </span>
                                @endif
                            </div>
                            <span class="nav-text">Sales Orders</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="{{ route('ad-purchase-orders.index') }}"
                            class="nav-link @if(in_array(Route::currentRouteName(), ['ad-purchase-orders.index','ad-purchase-orders.create','ad-purchase-orders.show'])) active @endif">
                            <div class="nav-icon">
                                <i class="ti ti-receipt"></i>
                            </div>
                            <span class="nav-text">Purchase Orders</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="javascript:void(0)" class="nav-link" data-bs-toggle="collapse" data-bs-target="#formsMenu" aria-expanded="{{ in_array(Route::currentRouteName(), ['md-ads','dealer-ads','customer-ads','my-customers','charges']) ? 'true' : 'false' }}">
                            <div class="nav-icon">
                                <i class="ti ti-clipboard"></i>
                            </div>
                            <span class="nav-text">Forms</span>
                            <i class="ti ti-chevron-down ms-auto"></i>
                        </a>

                        <div class="collapse @if(in_array(Route::currentRouteName(), ['arf','ir','borrowing','layout','referral', 'application', 'training'])) show @endif"
                            id="formsMenu">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item">
                                    <a href="https://docs.google.com/forms/d/e/1FAIpQLScJ3kuD2UiWAMXyvQe3r4AZHlvY39N5_2bz68jY868uqjCxyQ/viewform?usp=send_form" target="_blank" class="nav-link @if(Route::currentRouteName() == 'arf')active @endif" style="font-size: 14px">Access Request Form</a>
                                </li>
                                <li class="nav-item">
                                    <a href="https://docs.google.com/forms/d/e/1FAIpQLSdlOeSHBVOcAASbWkVOQpeVNbI1R36oVlEln-BctX7ekDlUnw/viewform" target="_blank" class="nav-link @if(Route::currentRouteName() == 'ir')active @endif" style="font-size: 14px">Incident Report</a>
                                </li>
                                <li class="nav-item">
                                    <a href="https://form.jotform.com/241338243724050" target="_blank" class="nav-link @if(Route::currentRouteName() == 'borrowing') active @endif" style="font-size: 14px">Borrowing Marketing Collateral Form</a>
                                </li>
                                <li class="nav-item">
                                    <a href="https://form.jotform.com/240291186323048" target="_blank" class="nav-link @if(Route::currentRouteName() == 'layout') active @endif" style="font-size: 14px">Layout Design Request Form</a>
                                </li>
                                <li class="nav-item">
                                    <a href="https://docs.google.com/forms/d/e/1FAIpQLScDxCoJdMlFDfr12wcrFcG2K7F1djvSJl3JKs-aAozqAp8bww/viewform" target="_blank" class="nav-link @if(Route::currentRouteName() == 'referral') active @endif" style="font-size: 14px">Area Distributor Referral Sheet</a>
                                </li>
                                <li class="nav-item">
                                    <a href="https://www.jotform.com/form/233230867016048#preview" target="_blank" class="nav-link @if(Route::currentRouteName() == 'application')active @endif" style="font-size: 14px">Area Distributor Application</a>
                                </li>
                                <li class="nav-item">
                                    <a href="https://docs.google.com/forms/d/e/1FAIpQLSe_o7mnARbbttceB-vlm5P7_HjHZ1QGFKgq9AGIMZTRwbixgA/viewform" target="_blank" class="nav-link @if(Route::currentRouteName() == 'training')active @endif" style="font-size: 14px">Sign Up Sheet | Repair Training Course</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="nav-item">
                        <a href="javascript:void(0)" class="nav-link" data-bs-toggle="collapse" data-bs-target="#reportsMenu" aria-expanded="{{ in_array(Route::currentRouteName(), ['dsr','aging','reports.distributor-other-charges']) ? 'true' : 'false' }}">
                            <div class="nav-icon">
                                <i class="ti ti-clipboard-data"></i>
                            </div>
                            <span class="nav-text">Reports</span>
                            <i class="ti ti-chevron-down ms-auto"></i>
                        </a>

                        <div class="collapse @if(in_array(Route::currentRouteName(), ['dsr','aging','reports.distributor-other-charges'])) show @endif"
                            id="reportsMenu">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item">
                                    <a href="{{ url('/reports/daily-sales') }}" class="nav-link @if(Route::currentRouteName() == 'dsr') active @endif" style="font-size: 14px">Daily Sales & Remittance Report</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('/reports/aging') }}" class="nav-link @if(Route::currentRouteName() == 'aging') active @endif" style="font-size: 14px">Aging Report</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('/reports/distributor-other-charges') }}" class="nav-link @if(Route::currentRouteName() == 'reports.distributor-other-charges') active @endif" style="font-size: 14px">Other Charges Transactions</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                @endif
                @if($sidebarIsAdmin && $sidebarWarehouse === 'guinobatan')
                    @php
                        $pendingAdPurchaseOrdersCount = \App\AdPurchaseOrder::where('status', 'Pending')
                            ->where(function ($query) {
                                $regionVNeedles = [
                                    'region v',
                                    'region 5',
                                    'region-5',
                                    'region-v',
                                    'bicol',
                                    'albay',
                                    'camarines norte',
                                    'camarines sur',
                                    'catanduanes',
                                    'masbate',
                                    'sorsogon',
                                ];

                                foreach ($regionVNeedles as $needle) {
                                    $query->orWhereRaw('LOWER(delivery_address) LIKE ?', ['%' . $needle . '%'])
                                        ->orWhereHas('ad', function ($adQuery) use ($needle) {
                                            $adQuery->whereRaw('LOWER(delivery_address) LIKE ?', ['%' . $needle . '%'])
                                                ->orWhereRaw('LOWER(location_region) LIKE ?', ['%' . $needle . '%']);
                                        });
                                }
                            })
                            ->count();
                    @endphp
                    <div class="nav-item">
                        <a href="{{ route('warehouse-ad-purchase-orders.region-v') }}"
                            class="nav-link @if(in_array(Route::currentRouteName(), ['warehouse-ad-purchase-orders.region-v','ad-purchase-orders.show'])) active @endif position-relative">
                            <div class="nav-icon position-relative">
                                <i class="ti ti-receipt"></i>
                                @if($pendingAdPurchaseOrdersCount > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        {{ $pendingAdPurchaseOrdersCount > 99 ? '99+' : $pendingAdPurchaseOrdersCount }}
                                    </span>
                                @endif
                            </div>
                            <span class="nav-text">Purchase Orders</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="javascript:void(0)" class="nav-link" data-bs-toggle="collapse" data-bs-target="#reportsMenu" aria-expanded="{{ in_array(Route::currentRouteName(), ['dpo','isl']) ? 'true' : 'false' }}">
                            <div class="nav-icon">
                                <i class="ti ti-clipboard-data"></i>
                            </div>
                            <span class="nav-text">Reports</span>
                            <i class="ti ti-chevron-down ms-auto"></i>
                        </a>

                        <div class="collapse @if(in_array(Route::currentRouteName(), ['dpo','isl'])) show @endif"
                            id="reportsMenu">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item">
                                    <a href="{{ url('/reports/dpo-report') }}" class="nav-link @if(Route::currentRouteName() == 'dpo') active @endif" style="font-size: 14px">Distributor Purchase Order Report</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('isl') }}" class="nav-link @if(Route::currentRouteName() == 'isl') active @endif" style="font-size: 14px">Inventory Stock Level Report</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                @elseif($sidebarIsAdmin && $sidebarWarehouse === 'lubao')
                    @php
                        $pendingAdPurchaseOrdersCount = \App\AdPurchaseOrder::where('status', 'Pending')->count();
                    @endphp
                    <div class="nav-item">
                        <a href="{{ route('ad-purchase-orders.index') }}"
                            class="nav-link @if(in_array(Route::currentRouteName(), ['ad-purchase-orders.index','ad-purchase-orders.create','ad-purchase-orders.show'])) active @endif position-relative">
                            <div class="nav-icon position-relative">
                                <i class="ti ti-receipt"></i>
                                @if($pendingAdPurchaseOrdersCount > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        {{ $pendingAdPurchaseOrdersCount > 99 ? '99+' : $pendingAdPurchaseOrdersCount }}
                                    </span>
                                @endif
                            </div>
                            <span class="nav-text">Purchase Orders</span>
                        </a>
                    </div>
                @endif
                @if($sidebarIsSedp && $sedpCanAccess('can_access_purchase_orders'))
                    @php
                        $pendingAdPurchaseOrdersCount = \App\AdPurchaseOrder::where('status', 'Pending')->count();
                    @endphp
                    <div class="nav-item">
                        <a href="{{ route('ad-purchase-orders.index') }}"
                            class="nav-link @if(in_array(Route::currentRouteName(), ['ad-purchase-orders.index','ad-purchase-orders.create','ad-purchase-orders.show'])) active @endif position-relative">
                            <div class="nav-icon position-relative">
                                <i class="ti ti-receipt"></i>
                                @if($pendingAdPurchaseOrdersCount > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        {{ $pendingAdPurchaseOrdersCount > 99 ? '99+' : $pendingAdPurchaseOrdersCount }}
                                    </span>
                                @endif
                            </div>
                            <span class="nav-text">Purchase Orders</span>
                        </a>
                    </div>
                @endif
                @if(($sidebarIsAdmin && $sidebarWarehouse === 'lubao') || ($sidebarIsSedp && $sedpCanAccess('can_access_inventory')))
                    <div class="nav-item">
                        <a href="{{ route('isl') }}" class="nav-link @if(Route::currentRouteName() == 'isl') active @endif">
                            <div class="nav-icon">
                                <i class="ti ti-box-seam"></i>
                            </div>
                            <span class="nav-text">Inventory Stock Report</span>
                        </a>
                    </div>
                @endif
                {{-- @if((auth()->user()->role == "Admin") || (auth()->user()->role == "Dealer") && !in_array(auth()->user()->warehouse, ['lubao', 'guinobatan'])) --}}
                @if((($sidebarIsAdmin || auth()->user()->role == "Dealer") && !in_array($sidebarWarehouse, ['lubao', 'guinobatan'])) || ($sidebarIsSedp && $sedpCanAccess('can_access_transactions')))
                    <div class="nav-item">
                        <a href="{{url('/transactions')}}" class="nav-link @if(Route::currentRouteName() == 'transactions')active @endif">
                            <div class="nav-icon">
                                <i class="ti ti-cash"></i>
                            </div>
                            <span class="nav-text">Transactions</span>
                        </a>
                    </div>
                @endif
                
                {{-- @if(auth()->user()->role == "Admin" && auth()->user()->warehouse != ["lubao", "guinobatan"]) --}}
                @if(($sidebarIsAdmin && !in_array($sidebarWarehouse, ['lubao', 'guinobatan'])) || ($sidebarIsSedp && (
                    $sedpCanAccess('can_access_distributors') ||
                    $sedpCanAccess('can_access_dealers') ||
                    $sedpCanAccess('can_access_customers') ||
                    $sedpCanAccess('can_access_settings') ||
                    $canAccessAnyReports ||
                    $canAccessUsersModule
                )))
                    @if($sidebarIsAdmin || $sedpCanAccess('can_access_distributors'))
                    <div class="nav-item">
                        <a href="javascript:void(0)" class="nav-link" data-bs-toggle="collapse" data-bs-target="#partnersMenu" aria-expanded="{{ in_array(Route::currentRouteName(), ['ads','pds','mds']) ? 'true' : 'false' }}">
                            <div class="nav-icon">
                                <i class="ti ti-users"></i>
                            </div>
                            <span class="nav-text">Authorized Distributors</span>
                            <i class="ti ti-chevron-down ms-auto"></i>
                        </a>

                        <div class="collapse @if(in_array(Route::currentRouteName(), ['ads','pds'])) show @endif"
                            id="partnersMenu">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item">
                                    <a href="{{ url('/pds') }}" class="nav-link @if(Route::currentRouteName() == 'pds') active @endif" style="font-size: 14px">Provincial Distributor</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('/ads') }}" class="nav-link @if(Route::currentRouteName() == 'ads') active @endif" style="font-size: 14px">Area Distributor</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    @endif
                    @if($sidebarIsAdmin || $sedpCanAccess('can_access_dealers'))
                    <div class="nav-item">
                        <a href="javascript:void(0)" class="nav-link" data-bs-toggle="collapse" data-bs-target="#dealersMenu" aria-expanded="{{ in_array(Route::currentRouteName(), ['ads','pds','mds']) ? 'true' : 'false' }}">
                            <div class="nav-icon">
                                <i class="ti ti-building-store"></i>
                            </div>
                            <span class="nav-text">Dealers</span>
                            <i class="ti ti-chevron-down ms-auto"></i>
                        </a>

                        <div class="collapse @if(in_array(Route::currentRouteName(), ['mds','dealers'])) show @endif"
                            id="dealersMenu">
                            <ul class="nav flex-column ms-3">
                                {{-- <li class="nav-item">
                                    <a href="{{ url('/mds') }}" class="nav-link @if(Route::currentRouteName() == 'mds') active @endif" style="font-size: 14px">Mega Dealer</a>
                                </li> --}}

                                <li class="nav-item">
                                    <a href="{{ url('/dealers') }}" class="nav-link @if(Route::currentRouteName() == 'dealers') active @endif" style="font-size: 14px">Dealers</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('/mds') }}" class="nav-link @if(Route::currentRouteName() == 'mds') active @endif" style="font-size: 14px">Mega Dealers</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    @endif
                    {{-- <div class="nav-item">
                        <a href="{{url('/dealers')}}" class="nav-link @if(Route::currentRouteName() == 'dealers')active @endif">
                            <div class="nav-icon">
                                <i class="ti ti-building-store"></i>
                            </div>
                            <span class="nav-text">Dealers</span>
                        </a>
                    </div> --}}
                    @if($sidebarIsAdmin || $sedpCanAccess('can_access_customers'))
                    <div class="nav-item">
                        <a href="{{url('/customers')}}" class="nav-link @if(Route::currentRouteName() == 'customers')active @endif">
                            <div class="nav-icon">
                                <i class="ti ti-users"></i>
                            </div>
                            <span class="nav-text">Customers</span>
                        </a>
                    </div>
                    @endif
                    @if($canAccessUsersModule)
                    <div class="nav-item">
                        <a href="{{url('/users')}}" class="nav-link @if(Route::currentRouteName() == 'users')active @endif">
                            <div class="nav-icon">
                                <i class="ti ti-id-badge"></i>
                            </div>
                            <span class="nav-text">Users</span>
                        </a>
                    </div>
                    @endif
                    {{-- <div class="nav-item">
                        <a href="{{url('/rewards')}}" class="nav-link @if(Route::currentRouteName() == 'rewards')active @endif">
                            <div class="nav-icon">
                                <i class="ti ti-gift"></i>
                            </div>
                            <span class="nav-text">Rewards</span>
                        </a>
                    </div> --}}
                    @if($sidebarIsAdmin || $sedpCanAccess('can_access_settings'))
                    <div class="nav-item">
                        <a href="javascript:void(0)" class="nav-link" data-bs-toggle="collapse" data-bs-target="#settingsMenu" aria-expanded="{{ in_array(Route::currentRouteName(), ['vouchers', 'rewards', 'items', 'raffles']) ? 'true' : 'false' }}">
                            <div class="nav-icon">
                                <i class="ti ti-settings"></i>
                            </div>
                            <span class="nav-text">Settings</span>
                            <i class="ti ti-chevron-down ms-auto"></i>
                        </a>

                        <div class="collapse @if(in_array(Route::currentRouteName(), ['vouchers','rewards', 'items', 'raffles', 'areas'])) show @endif"
                            id="settingsMenu">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item">
                                    <a href="{{ url('/items') }}" class="nav-link @if(Route::currentRouteName() == 'items') active @endif" style="font-size: 14px">Items</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('/vouchers') }}" class="nav-link @if(Route::currentRouteName() == 'vouchers') active @endif" style="font-size: 14px">Vouchers</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{url('/rewards')}}" class="nav-link @if(Route::currentRouteName() == 'rewards') active @endif" style="font-size: 14px">Rewards</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{url('/raffles')}}" class="nav-link @if(Route::currentRouteName() == 'raffles') active @endif" style="font-size: 14px">Raffles</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{url('/areas')}}" class="nav-link @if(Route::currentRouteName() == 'areas') active @endif" style="font-size: 14px">Areas</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    @endif
                    @if($sidebarIsAdmin || $sedpCanAccess('can_access_stock_requests'))
                        <div class="nav-item">
                            <a href="{{url('/stock-requests')}}" class="nav-link @if(Route::currentRouteName() == 'admin.stock.requests')active @endif">
                                <div class="nav-icon position-relative">
                                    <i class="ti ti-checkbox"></i>
                                    @if(($pendingStockRequestsCount ?? 0) > 0)
                                        <span class="badge rounded-pill bg-danger nav-badge">
                                            {{ $pendingStockRequestsCount > 99 ? '99+' : $pendingStockRequestsCount }}
                                        </span>
                                    @endif
                                </div>
                                <span class="nav-text">Stock Request Approvals</span>
                            </a>
                        </div>
                    @endif
                    @if($canAccessAnyReports)
                    <div class="nav-item">
                        <a href="javascript:void(0)" class="nav-link" data-bs-toggle="collapse" data-bs-target="#reportsMenu" aria-expanded="{{ in_array(Route::currentRouteName(), $visibleReportRoutes) ? 'true' : 'false' }}">
                            <div class="nav-icon">
                                <i class="ti ti-clipboard-data"></i>
                            </div>
                            <span class="nav-text">Reports</span>
                            <i class="ti ti-chevron-down ms-auto"></i>
                        </a>

                        <div class="collapse @if(in_array(Route::currentRouteName(), $visibleReportRoutes)) show @endif"
                            id="reportsMenu">
                            <ul class="nav flex-column ms-3">
                                {{-- <li class="nav-item">
                                    <a href="{{ url('/reports/daily-sales') }}" class="nav-link @if(Route::currentRouteName() == 'dsr') active @endif" style="font-size: 14px">Daily Sales & Remittance Report</a>
                                </li> --}}
                                @if($canAccessStandardReports)
                                <li class="nav-item">
                                    <a href="{{ url('/reports/dpo-report') }}" class="nav-link @if(Route::currentRouteName() == 'dpo') active @endif" style="font-size: 14px">Distributor Purchase Order Report</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('/reports/monthly-sales') }}" class="nav-link @if(Route::currentRouteName() == 'monthly-sales') active @endif" style="font-size: 14px">Monthly Sales Report</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('/reports/isl-report') }}" class="nav-link @if(Route::currentRouteName() == 'isl') active @endif" style="font-size: 14px">Inventory Stock Level Report</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('/reports/aging') }}" class="nav-link @if(Route::currentRouteName() == 'aging') active @endif" style="font-size: 14px">Aging Report</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('/reports/voucher-history') }}" class="nav-link @if(Route::currentRouteName() == 'voucher-history') active @endif" style="font-size: 14px">Voucher History Report</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('/reports/distributor-other-charges') }}" class="nav-link @if(Route::currentRouteName() == 'distributor-other-charges') active @endif" style="font-size: 14px">Distributor Other Charges Report</a>
                                </li>
                                @endif
                                @if($canAccessSedpReports)
                                    <li class="nav-item">
                                        <a href="{{ url('/reports/signup-incentives') }}" class="nav-link @if(Route::currentRouteName() == 'signup-incentives') active @endif" style="font-size: 14px">Sign Up Incentives Report</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('/reports/repeat-purchase-incentives') }}" class="nav-link @if(Route::currentRouteName() == 'repeat-purchase-incentives') active @endif" style="font-size: 14px">Repeat Purchase Incentives Report</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('/reports/aging-report-dealer') }}" class="nav-link @if(Route::currentRouteName() == 'aging-report-dealer') active @endif" style="font-size: 14px">Aging Report - Dealer</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('/reports/aging-report-customer') }}" class="nav-link @if(Route::currentRouteName() == 'aging-report-customer') active @endif" style="font-size: 14px">Aging Report - Customer</a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>  
                    @endif
                    {{-- <div class="nav-item">
                        <a href="javascript:void(0)" class="nav-link" data-bs-toggle="collapse" data-bs-target="#suppliesMenu" aria-expanded="{{ in_array(Route::currentRouteName(), ['areas','centers']) ? 'true' : 'false' }}">
                            <div class="nav-icon">
                                <i class="ti ti-settings"></i>
                            </div>
                            <span class="nav-text">Settings</span>
                            <i class="ti ti-chevron-down ms-auto"></i>
                        </a>

                        <div class="collapse @if(in_array(Route::currentRouteName(), ['areas','centers'])) show @endif"
                            id="suppliesMenu">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item">
                                    <a href="{{ url('/areas') }}" class="nav-link @if(Route::currentRouteName() == 'areas') active @endif" style="font-size: 14px">Area</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('/products/create') }}" class="nav-link @if(Route::currentRouteName() == 'products') active @endif" style="font-size: 14px">Products</a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ url('/inventories') }}" class="nav-link @if(Route::currentRouteName() == 'inventories') active @endif" style="font-size: 14px">Inventory</a>
                                </li>
                            </ul>
                        </div>
                    </div> --}}
                @endif
            </div>
        </div>

        <div class="sidebar-footer">
            <div class="user-profile">
                <img src="{{auth()->user()->avatar}}" onerror="this.src='{{url('design/assets/images/profile/user-1.png')}}';" alt="User" class="user-avatar">
                <div class="user-info">
                    <p class="user-name">{{current(explode(' ',auth()->user()->name))}}</p>
                    <p class="user-role">{{auth()->user()->role}}</p>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-left">
                <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle navigation" aria-controls="sidebar" aria-expanded="true">
                    <i class="ti ti-menu-2" style="font-size: 20px;"></i>
                </button>
                <div class="welcome-message">
                    <h6>Hello {{current(explode(' ',auth()->user()->name))}}!</h6>
                    <small>Welcome back to dashboard</small>
                </div>
            </div>
            <div class="topbar-right">
                @if(auth()->user()->role == "Area Distributor")
                    <a href="{{ route('guest-order') }}" class="guest-order-link topbar-action" target="_blank" rel="noopener" aria-label="Open guest order" title="Guest Order">
                        <i class="ti ti-shopping-cart-plus"></i>
                        <span>Guest Order</span>
                    </a>
                    <button type="button" class="loyalty-scan-link topbar-action border-0" data-bs-toggle="modal" data-bs-target="#loyaltyScanModal" aria-label="Scan loyalty card" title="Scan Loyalty">
                        <i class="ti ti-qrcode"></i>
                        <span>Scan Loyalty</span>
                    </button>
                @endif
                <button type="button" class="mobile-search-toggle" id="mobileSearchToggle" aria-label="Open search" aria-controls="searchInput" aria-expanded="false">
                    <i class="ti ti-search"></i>
                </button>
                <div class="search-container">
                    <form action="{{ url('/search') }}" method="GET" class="position-relative">
                        <input 
                        type="search" 
                        name="q"
                        id="searchInput"
                        class="search-input" 
                        style="width: 300px; height: 40px;" 
                        placeholder="Search by name..." 
                        value="{{ request('q') }}"
                        autocomplete="off"
                        />
                        <iconify-icon icon="solar:magnifer-linear" 
                                    class="search-icon position-absolute top-50 translate-middle-y ms-3 fs-6 text-muted"></iconify-icon>
                        
                        <div id="searchSuggestions" 
                            class="position-absolute bg-white border rounded-3 shadow-lg" 
                            style="display: none; z-index: 1000; max-height: 350px; overflow-y: auto; top: 100%; left: 0; right: 0; margin-top: 5px;">
                        </div>
                        
                        <button type="submit" style="display: none;"></button>
                    </form>
                </div>
                
                <div class="dropdown notification-dropdown">
                    @php
                        $notificationData = app('App\Http\Controllers\NotificationController')->getNotificationData();
                        extract($notificationData); // This gives you all the variables like $notifications, $totalUnreadCount, etc.
                    @endphp
                    
                    <a class="" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="rounded-circle danger notification-bell-btn" id="notification-bell"
                            data-current-count="{{ $totalUnreadCount }}"
                            data-latest-notification-id="{{ $latestNotificationId }}">
                            {{-- <iconify-icon icon="solar:bell-linear" class="fs-7" style="color: #DFDFEC"></iconify-icon> --}}
                            <i class="ti ti-bell fs-7" style="color: #ffffff"></i>
                            <span class="position-absolute bottom-45 start-100 translate-middle badges rounded-pill notif-badge" 
                                id="notification-badge" style="{{ $totalUnreadCount > 0 ? '' : 'display: none;' }}">
                                {{$totalUnreadCount ?? ''}}
                            </span>
                        </div>
                    </a>
                    
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up p-0 notification-menu" 
                        aria-labelledby="notificationDropdown">
                        <div class="px-3 py-3 notification-head d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 fw-bold">Notifications</h6>
                                <small class="text-muted">{{ $totalUnreadCount }} unread updates</small>
                            </div>
                            @if($totalUnreadCount > 0)
                                <form action="{{ route('notifications.markAllAsRead') }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-link p-0 text-primary fw-semibold">
                                        Mark all as read
                                    </button>
                                </form>
                            @endif
                        </div>
                        
                        <div class="notification-list">
                            @if($notifications->count() > 0)
                                <div class="px-3 py-3">
                                    @foreach($notifications as $notification)
                                        @if($notification['type'] === 'client')
                                            @php 
                                                $client = $notification['data'];
                                                $isUnread = !in_array('client_' . $client->id, $readNotifications);
                                                $isSaved = in_array('customer_' . $client->id, $savedNotifications);
                                                $redirectUrl = url('/customers'); // Dynamic URL for clients
                                            @endphp
                                            <div class="notification-item notification-row {{ $isUnread ? 'is-unread' : '' }}">
                                                <div class="d-flex align-items-center" 
                                                    style="cursor: pointer;" 
                                                    onclick="markAsReadAndRedirect('client_{{ $client->id }}', '{{ $redirectUrl }}')"
                                                    onmouseover="this.style.backgroundColor='#f8f9fa'" 
                                                    onmouseout="this.style.backgroundColor=''">
                                                    <div class="flex-shrink-0">
                                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                                            style="width: 35px; height: 35px;">
                                                            <iconify-icon icon="solar:user-plus-broken" class="text-white fs-5"></iconify-icon>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <p class="mb-0 fs-3">
                                                            <strong>{{ $client->name }}</strong> registered
                                                            @if($isUnread)
                                                                <span class="badges bg-primary ms-2" style="font-size: 0.6rem;">NEW</span>
                                                            @endif
                                                        </p>
                                                        <small class="text-muted">{{ $client->created_at->diffForHumans() }}</small>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-end mt-2">
                                                    @if(!$isSaved)
                                                        <form action="{{ route('notification.save') }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            <input type="hidden" name="type" value="client">
                                                            <input type="hidden" name="record_id" value="{{ $client->id }}">
                                                            <button type="submit" class="btn btn-sm btn-success" style="font-size: 0.75rem;">
                                                                View
                                                            </button>
                                                        </form>
                                                    @else
                                                        <a href="{{ $redirectUrl }}" class="btn btn-sm btn-success" style="font-size: 0.75rem;">
                                                            View
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                            @elseif($notification['type'] === 'transaction')
                                            @php 
                                                $transaction = $notification['data'];
                                                $isUnread = !in_array('transaction_' . $transaction->id, $readNotifications);
                                                $isSaved = in_array('transaction_' . $transaction->id, $savedNotifications);
                                                $redirectUrl = url('/transactions'); // Dynamic URL for transactions
                                            @endphp
                                            <div class="notification-item notification-row {{ $isUnread ? 'is-unread' : '' }}">
                                                <div class="d-flex align-items-center" 
                                                    style="cursor: pointer;" 
                                                    onclick="markAsReadAndRedirect('transaction_{{ $transaction->id }}', '{{ $redirectUrl }}')"
                                                    onmouseover="this.style.backgroundColor='#f8f9fa'" 
                                                    onmouseout="this.style.backgroundColor=''">
                                                    <div class="flex-shrink-0">
                                                        <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" 
                                                            style="width: 35px; height: 35px;">
                                                            <iconify-icon icon="solar:dollar-minimalistic-broken" class="text-white fs-5"></iconify-icon>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <p class="mb-0 fs-3">
                                                            @if($transaction->customer)
                                                                <strong>{{ $transaction->customer->name }}</strong> 
                                                            @else
                                                                Customer
                                                            @endif
                                                            made transaction
                                                            @if($isUnread)
                                                                <span class="badges bg-primary ms-2" style="font-size: 0.6rem;">NEW</span>
                                                            @endif
                                                            @if($transaction->product)
                                                                <br><small class="text-muted">{{ $transaction->product->name }} - ₱{{ number_format($transaction->price, 2) }}</small>
                                                            @endif
                                                        </p>
                                                        <small class="text-muted">{{ $transaction->created_at->diffForHumans() }}</small>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-end mt-2">
                                                    @if(!$isSaved)
                                                        <form action="{{ route('notification.save') }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            <input type="hidden" name="type" value="transaction">
                                                            <input type="hidden" name="record_id" value="{{ $transaction->id }}">
                                                            <button type="submit" class="btn btn-sm btn-success" style="font-size: 0.75rem;">
                                                                View
                                                            </button>
                                                        </form>
                                                    @else
                                                        <a href="{{ $redirectUrl }}" class="btn btn-sm btn-success" style="font-size: 0.75rem;">
                                                            View
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        @elseif($notification['type'] === 'order')
                                            @php
                                                $order = $notification['data'];
                                                $dealerName = ($order->is_guest ?? false) || $order->guest_name
                                                    ? ($order->guest_name ?: 'Guest Customer')
                                                    : (optional($order->dealer)->name ?: optional($order->adDealer)->name ?: 'Dealer');
                                                $isUnread = !in_array('order_' . $order->id, $readNotifications);
                                                $isSaved = in_array('order_' . $order->id, $savedNotifications);
                                                $redirectUrl = url('/orders');
                                                $orderTotal = ($order->price * $order->qty) + ($order->delivery_fee ?? 0);
                                            @endphp
                                            <div class="notification-item notification-row {{ $isUnread ? 'is-unread' : '' }}">
                                                <div class="d-flex align-items-center"
                                                    style="cursor: pointer;"
                                                    onclick="markAsReadAndRedirect('order_{{ $order->id }}', '{{ $redirectUrl }}')"
                                                    onmouseover="this.style.backgroundColor='#f8f9fa'"
                                                    onmouseout="this.style.backgroundColor=''">
                                                    <div class="flex-shrink-0">
                                                        <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center"
                                                            style="width: 35px; height: 35px;">
                                                            <iconify-icon icon="solar:cart-large-minimalistic-broken" class="text-white fs-5"></iconify-icon>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <p class="mb-0 fs-3">
                                                            <strong>{{ $dealerName }}</strong> placed an order
                                                            @if($isUnread)
                                                                <span class="badges bg-primary ms-2" style="font-size: 0.6rem;">NEW</span>
                                                            @endif
                                                            <br><small class="text-muted">{{ $order->item }} x {{ $order->qty }} - PHP {{ number_format($orderTotal, 2) }}</small>
                                                        </p>
                                                        <small class="text-muted">{{ $order->created_at->diffForHumans() }}</small>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-end mt-2">
                                                    @if(!$isSaved)
                                                        <form action="{{ route('notification.save') }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            <input type="hidden" name="type" value="order">
                                                            <input type="hidden" name="record_id" value="{{ $order->id }}">
                                                            <button type="submit" class="btn btn-sm btn-success" style="font-size: 0.75rem;">
                                                                View
                                                            </button>
                                                        </form>
                                                    @else
                                                        <a href="{{ $redirectUrl }}" class="btn btn-sm btn-success" style="font-size: 0.75rem;">
                                                            View
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <div class="notification-empty text-center">
                                    <iconify-icon icon="solar:bell-off-broken" class="fs-1 text-muted"></iconify-icon>
                                    <p class="text-muted mb-0 mt-2">No recent notifications</p>
                                </div>
                            @endif
                        </div>
                        
                        <div class="px-3 py-3 border-top d-flex gap-2 justify-content-center">
                            <a href="{{ url('/transactions') }}" class="btn btn-sm btn-outline-secondary">View All Transactions</a>
                            {{-- <a href="{{ url('/orders') }}" class="btn btn-sm btn-danger">AD Orders</a> --}}
                        </div>
                    </div>
                    <div id="notification-live-toast" class="notification-live-toast">
                        <div class="fw-semibold">New notification received</div>
                        <small class="opacity-75">Open notifications to see the latest update.</small>
                    </div>
                </div>
                <div class="dropdown">
                    <button class="profile-dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{auth()->user()->avatar}}" onerror="this.src='{{url('design/assets/images/profile/user-1.png')}}';" alt="Profile" class="profile-img">
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="profile-info">
                            <div class="d-flex align-items-center">
                                <img src="{{auth()->user()->avatar}}" onerror="this.src='{{url('design/assets/images/profile/user-1.png')}}';" alt="user" width="40" class="rounded-circle me-3" />
                                <div>
                                    <h6 class="mb-0">{{auth()->user()->name}}</h6>
                                    <small class="text-muted">{{auth()->user()->email}}</small>
                                </div>
                            </div>
                        </li>
                        @if(auth()->user()->role != "Admin" && auth()->user()->role != "Area Distributor")
                        {{-- <li><a class="dropdown-item" href="{{url('user-profile')}}">
                            <i class="ti ti-user"></i>
                            <span>My Profile</span>
                        </a></li> --}}
                        @endif
                        {{-- <li><hr class="dropdown-divider"></li> --}}
                        <li><a class="dropdown-item" href="#" onclick="logout(); show();">
                            <i class="ti ti-logout text-danger"></i>
                            <span>Log Out</span>
                        </a></li>
                    </ul>
                </div>
            </div>
        </header>
        <div class="modal fade loyalty-scan-modal" id="loyaltyScanModal" tabindex="-1" aria-labelledby="loyaltyScanModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="loyalty-scan-head">
                        <div>
                            <h5 class="modal-title fw-bold mb-1" id="loyaltyScanModalLabel">Scan Loyalty Card</h5>
                            <small class="text-muted">Verify if the client is included in the project.</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-3">
                        <div id="loyaltyQrReader" class="loyalty-scan-reader"></div>
                        <div class="loyalty-scan-status mt-3" id="loyaltyScanStatus">
                            Open camera access, then scan the client loyalty card QR code.
                        </div>
                        <div class="input-group mt-3">
                            <input type="text" class="form-control" id="loyaltyManualCode" placeholder="Enter loyalty code manually">
                            <button type="button" class="btn btn-danger" id="loyaltyManualScanBtn">
                                <i class="ti ti-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <a href="#" class="btn btn-success d-none" id="loyaltyViewClientBtn">
                            <i class="ti ti-shopping-cart-plus"></i> <span id="loyaltyOrderLabel">Guest Order</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        {{-- @endif --}} {{-- Mark Ian 04/23/2026 --}}

        <!-- Redeem Area -->
        @yield('contents')

        <!-- Content Area -->
        <div class="content-area @if(Request::is('rewards')) rewards-page @endif">
            @yield('content')
            
            <footer class="footer">
                <div class="container-fluid">
                    <div class="footer-content">
                        <!-- Left-aligned Logo -->
                        <div class="company-info">
                            <div class="company-logo">
                                <img src="{{ asset('images/footer.png') }}" alt="Company Logo" />
                            </div>
                        </div>

                        <!-- Center Nav + Social -->
                        <div class="nav-links-container">
                            <nav>
                                <ul class="nav-links">
                                    <li><a href="{{ url('/') }}">Home</a></li>
                                    <li><a href="{{ route('products') }}">Product</a></li>
                                    <li><a href="{{ route('storelocation') }}">Store Location</a></li>
                                    <li><a href="{{ route('about') }}">About</a></li>
                                </ul>
                            </nav>

                            <div class="divider"></div>

                            <div class="social-links">
                                <a href="https://www.tiktok.com/@gazliteofficial" aria-label="Tiktok">
                                    <iconify-icon icon="simple-icons:tiktok"></iconify-icon>
                                </a>
                                <a href="https://www.instagram.com/gazliteph/#" aria-label="Instagram">
                                    <iconify-icon icon="mdi:instagram"></iconify-icon>
                                </a>
                                <a href="https://www.facebook.com/GazLitePH/" aria-label="Facebook">
                                    <iconify-icon icon="mdi:facebook"></iconify-icon>
                                </a>
                            </div>
                            <div class="footer-right-image">
                                <img src="{{ asset('images/footer1.png') }}" alt="Right Footer Image" />
                            </div>
                        </div>
                    </div>
                </div>
            </footer> 
        </div>
    </div>

    <!-- Hidden logout form -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        {{ csrf_field() }}
    </form>

    <!-- Bootstrap JS -->
    
    <!-- Original scripts -->
    <script src="{{asset('design/assets/js/vendor.min.js')}}"></script>
    <script src="{{asset('design/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('design/assets/libs/simplebar/dist/simplebar.min.js')}}"></script>
    <script src="{{asset('design/assets/js/theme/app.init.js')}}"></script>
    <script src="{{asset('design/assets/js/theme/theme.js')}}"></script>
    <script src="{{asset('design/assets/js/theme/app.min.js')}}"></script>
    <script src="{{asset('design/assets/js/theme/sidebarmenu.js')}}"></script>
    <script src="{{asset('design/assets/js/theme/feather.min.js')}}"></script>
    {{-- <script src="{{ asset('design/vendors/select2/select2.min.js') }}"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- solar icons -->
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="{{ asset('vendor/sweetalert/sweetalert.all.js') }}"></script>

    @php
        config(['sweetalert.neverLoadJS' => true]);
    @endphp
    @include('sweetalert::alert')

    <script>
        function initSelect2(parent = document) {
            if (!window.jQuery || !$.fn || !$.fn.select2) {
                return;
            }

            const $parent = $(parent);
            const $selects = $parent.is('select.select2')
                ? $parent
                : $parent.find('select.select2');

            $selects.each(function () {
                const $this = $(this);
                const $modal = $this.closest('.modal');
                const isArea = $this.hasClass('select2-area');
                const isMultiple = $this.prop('multiple');
                const hasAreaFormatter = isArea && typeof window.formatArea === 'function';

                // ✅ Destroy ONLY if already initialized (prevents duplication bug)
                if ($this.hasClass('select2-hidden-accessible')) {
                    return;
                }

                $this.select2({
                    width: '100%',
                    dropdownParent: $modal.length ? $modal : $(document.body),
                    placeholder: $this.data('placeholder') || 'Select Option',
                    allowClear: !isMultiple,
                    closeOnSelect: !isMultiple,
                    theme: $this.data('select2-theme') || 'bootstrap-5',
                    selectionCssClass: $this.data('selection-css-class') || ':all:',
                    dropdownCssClass: $this.data('dropdown-css-class') || '',
                    minimumResultsForSearch: $this.data('minimum-results-for-search') !== undefined ? Number($this.data('minimum-results-for-search')) : 0,

                    templateResult: hasAreaFormatter ? window.formatArea : undefined,
                    templateSelection: hasAreaFormatter ? window.formatArea : undefined,

                    escapeMarkup: markup => markup
                });

                // ✅ Fix selected value rendering
                if ($this.val()) {
                    $this.trigger('change.select2');
                }

                // ✅ Autofocus search field
                $this.off('select2:open.codexFocus').on('select2:open.codexFocus', function () {
                    setTimeout(() => {
                        const search = document.querySelector('.select2-container--open .select2-search__field');
                        if (search) search.focus();
                    }, 0);
                });
            });
        }
        $(document).ready(function () {
            initSelect2();
        });
        // Re-init ONLY inside modal when opened
        $(document).on('shown.bs.modal', '.modal', function () {
            const modal = this;

            setTimeout(function () {
                initSelect2(modal);
            }, 0);
        });
    </script>

    @yield('javascript')

    <script>
        function logout() {
            event.preventDefault();
            document.getElementById('logout-form').submit();
        }
        
        function initSelect2Legacy(parent = document) {
            if (!$.fn.select2) return;

            const $parent = $(parent);
            const $selects = $parent.is('select.select2') ? $parent : $parent.find('select.select2');

            $selects.each(function () {
                const $this = $(this);
                const $modal = $this.closest('.modal');
                const isArea = $this.hasClass('select2-area');
                const isMultiple = $this.prop('multiple');

                // ✅ Destroy ONLY if already initialized (prevents duplication bug)
                if ($this.hasClass('select2-hidden-accessible')) {
                    return;
                }

                $this.select2({
                    width: '100%',
                    dropdownParent: $modal.length ? $modal : $(document.body),
                    placeholder: $this.data('placeholder') || 'Select Option',
                    allowClear: !isMultiple,
                    closeOnSelect: !isMultiple,
                    theme: $this.data('select2-theme') || 'bootstrap-5',
                    selectionCssClass: $this.data('selection-css-class') || ':all:',
                    dropdownCssClass: $this.data('dropdown-css-class') || '',
                    minimumResultsForSearch: $this.data('minimum-results-for-search') !== undefined ? Number($this.data('minimum-results-for-search')) : 0,

                    templateResult: isArea && typeof formatArea === 'function' ? formatArea : undefined,
                    templateSelection: isArea && typeof formatArea === 'function' ? formatArea : undefined,

                    escapeMarkup: markup => markup
                });

                // ✅ Fix selected value rendering
                if ($this.val()) {
                    $this.trigger('change.select2');
                }

                // ✅ Autofocus search field
                $this.off('select2:open.codexFocus').on('select2:open.codexFocus', function () {
                    setTimeout(() => {
                        const search = document.querySelector('.select2-container--open .select2-search__field');
                        if (search) search.focus();
                    }, 0);
                });
            });
        }
        $(document).ready(function () {
            initSelect2();
        });
        // Re-init ONLY inside modal when opened
        $(document).on('shown.bs.modal', '.modal', function () {
            const modal = this;

            setTimeout(function () {
                initSelect2(modal);
            }, 0);
        });

        document.addEventListener('focusin', function (e) {
            if (e.target.closest(".select2-container")) {
                e.stopPropagation();
            }
        });

        $(document).on('input', 'input[data-uppercase], textarea[data-uppercase]', function () {

            let start = this.selectionStart;
            let end = this.selectionEnd;

            this.value = this.value.toUpperCase();

            // Keep cursor position
            this.setSelectionRange(start, end);

        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const suggestionsDiv = document.getElementById('searchSuggestions');
            let debounceTimer;

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const query = this.value.trim();
                    
                    clearTimeout(debounceTimer);
                    
                    if (query.length < 2) {
                        suggestionsDiv.style.display = 'none';
                        return;
                    }

                    debounceTimer = setTimeout(() => {
                        fetch(`{{ url('/search/suggestions') }}?q=${encodeURIComponent(query)}`)
                            .then(response => response.json())
                            .then(data => {
                                suggestionsDiv.innerHTML = '';
                                
                                if (data.length > 0) {
                                    data.forEach(item => {
                                        const div = document.createElement('div');
                                        div.className = 'p-3 search-suggestion-item border-bottom';
                                        div.style.cursor = 'pointer';
                                        div.innerHTML = `
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                                        style="width: 35px; height: 35px; background-color: ${item.type === 'client' ? '#e3f2fd' : '#f3e5f5'};">
                                                        <iconify-icon icon="${item.type === 'client' ? 'solar:user-linear' : 'solar:shop-linear'}" 
                                                                    class="fs-5" style="color: ${item.type === 'client' ? '#1976d2' : '#7b1fa2'};"></iconify-icon>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="fw-semibold text-dark">${item.name}</div>
                                                    <small class="text-muted">${item.type === 'client' ? 'Customer' : 'Dealer'}</small>
                                                </div>
                                                <div>
                                                    <iconify-icon icon="solar:arrow-right-linear" class="text-muted"></iconify-icon>
                                                </div>
                                            </div>
                                        `;
                                        
                                        div.addEventListener('click', function() {
                                            searchInput.value = item.name;
                                            suggestionsDiv.style.display = 'none';
                                            window.location.href = `{{ url('/profile') }}/${item.id}/${item.type}`;
                                        });

                                        div.addEventListener('mouseenter', function() {
                                            this.style.backgroundColor = '#f8f9fa';
                                        });

                                        div.addEventListener('mouseleave', function() {
                                            this.style.backgroundColor = 'transparent';
                                        });
                                        
                                        suggestionsDiv.appendChild(div);
                                    });
                                    
                                    if (data.length > 0) {
                                        const viewAllDiv = document.createElement('div');
                                        viewAllDiv.className = 'p-3 text-center border-top';
                                        viewAllDiv.innerHTML = `
                                            <small class="text-primary fw-semibold" style="cursor: pointer;">
                                                Press Enter to search for "${query}"
                                            </small>
                                        `;
                                        suggestionsDiv.appendChild(viewAllDiv);
                                    }
                                    
                                    suggestionsDiv.style.display = 'block';
                                } else {
                                    suggestionsDiv.innerHTML = `
                                        <div class="p-3 text-center text-muted">
                                            <iconify-icon icon="solar:magnifer-linear" class="fs-3"></iconify-icon>
                                            <div class="mt-2">No users found for "${query}"</div>
                                            <small>Try a different search term</small>
                                        </div>
                                    `;
                                    suggestionsDiv.style.display = 'block';
                                }
                            })
                            .catch(error => {
                                console.error('Search error:', error);
                                suggestionsDiv.style.display = 'none';
                            });
                    }, 300);
                });

                document.addEventListener('click', function(event) {
                    if (!searchInput.closest('.position-relative').contains(event.target)) {
                        suggestionsDiv.style.display = 'none';
                    }
                });

                searchInput.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape') {
                        suggestionsDiv.style.display = 'none';
                    }
                });

                searchInput.addEventListener('focus', function() {
                    if (this.value.length >= 2) {
                        this.dispatchEvent(new Event('input'));
                    }
                });
            }
        });
    </script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const topbar = document.querySelector('.topbar');
            const mobileSearchToggle = document.getElementById('mobileSearchToggle');
            const searchInput = document.getElementById('searchInput');

            if (!sidebar || !sidebarToggle || !sidebarOverlay) {
                return;
            }

            function syncToggleState() {
                const isMobile = window.innerWidth <= 768;
                const isOpen = isMobile
                    ? sidebar.classList.contains('mobile-open')
                    : !sidebar.classList.contains('collapsed');

                sidebarToggle.setAttribute('aria-expanded', String(isOpen));
            }

            function initializeSidebar() {
                if (window.innerWidth > 768) {
                    const savedState = localStorage.getItem('sidebarCollapsed');
                    sidebar.classList.toggle('collapsed', savedState === 'true');
                    sidebar.classList.remove('mobile-open');
                    sidebarOverlay.classList.remove('active');
                } else {
                    sidebar.classList.remove('collapsed');
                    sidebar.classList.remove('mobile-open');
                    sidebarOverlay.classList.remove('active');
                }

                if (window.innerWidth > 768 && topbar) {
                    topbar.classList.remove('search-active');
                    if (mobileSearchToggle) {
                        mobileSearchToggle.setAttribute('aria-expanded', 'false');
                    }
                }

                syncToggleState();
            }

            initializeSidebar();

            function toggleSidebar() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.toggle('mobile-open');
                    sidebarOverlay.classList.toggle('active', sidebar.classList.contains('mobile-open'));
                } else {
                    sidebar.classList.toggle('collapsed');
                    localStorage.setItem('sidebarCollapsed', String(sidebar.classList.contains('collapsed')));
                }

                syncToggleState();
            }

            sidebarToggle.addEventListener('click', toggleSidebar);

            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('mobile-open');
                sidebarOverlay.classList.remove('active');
                syncToggleState();
            });

            if (mobileSearchToggle && topbar && searchInput) {
                mobileSearchToggle.addEventListener('click', function() {
                    const searchIsOpen = topbar.classList.toggle('search-active');
                    mobileSearchToggle.setAttribute('aria-expanded', String(searchIsOpen));

                    if (searchIsOpen) {
                        window.setTimeout(function() {
                            searchInput.focus();
                        }, 0);
                    }
                });
            }

            let resizeTimer;
            window.addEventListener('resize', function() {
                window.clearTimeout(resizeTimer);
                resizeTimer = window.setTimeout(initializeSidebar, 120);
            });

            if (searchInput) {
                searchInput.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                searchInput.addEventListener('blur', function() {
                    this.parentElement.classList.remove('focused');
                });
            }

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && topbar && topbar.classList.contains('search-active')) {
                    topbar.classList.remove('search-active');
                    if (mobileSearchToggle) {
                        mobileSearchToggle.setAttribute('aria-expanded', 'false');
                        mobileSearchToggle.focus();
                    }
                    return;
                }

                if (event.key === 'Escape' && sidebar.classList.contains('mobile-open')) {
                    sidebar.classList.remove('mobile-open');
                    sidebarOverlay.classList.remove('active');
                    syncToggleState();
                    sidebarToggle.focus();
                }
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalElement = document.getElementById('loyaltyScanModal');
            const statusBox = document.getElementById('loyaltyScanStatus');
            const manualCodeInput = document.getElementById('loyaltyManualCode');
            const manualScanBtn = document.getElementById('loyaltyManualScanBtn');
            const viewClientBtn = document.getElementById('loyaltyViewClientBtn');
            const loyaltyOrderLabel = document.getElementById('loyaltyOrderLabel');
            let qrScanner = null;
            let isScanning = false;

            if (!modalElement || !statusBox) {
                return;
            }

            function setScanStatus(message, type) {
                statusBox.textContent = message;
                statusBox.classList.remove('is-success', 'is-error');

                if (type) {
                    statusBox.classList.add(type === 'success' ? 'is-success' : 'is-error');
                }
            }

            function setClientButton(url, label) {
                if (!viewClientBtn) {
                    return;
                }

                if (loyaltyOrderLabel) {
                    loyaltyOrderLabel.textContent = label || 'Guest Order';
                }

                if (url) {
                    viewClientBtn.href = url;
                    viewClientBtn.classList.remove('d-none');
                } else {
                    viewClientBtn.href = '#';
                    viewClientBtn.classList.add('d-none');
                }
            }

            function lookupLoyaltyCode(code) {
                const value = String(code || '').trim();

                if (!value) {
                    setScanStatus('No QR code was detected.', 'error');
                    return;
                }

                setScanStatus('Checking loyalty card...', null);
                setClientButton(null);

                fetch('{{ route('loyalty-card.scan') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ code: value })
                })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        if (data.included) {
                            const clientName = data.client && data.client.name ? data.client.name : 'Client';
                            setScanStatus(clientName + ' is included in the project. Continue to order.', 'success');
                            setClientButton(data.order_url || data.redirect_url, 'Order');
                            stopScanner();
                            return;
                        }

                        setScanStatus(data.message || 'This loyalty card is not included in the project.', 'error');
                    })
                    .catch(function() {
                        setScanStatus('Unable to verify loyalty card right now.', 'error');
                    });
            }

            function startScanner() {
                if (isScanning || typeof Html5Qrcode === 'undefined') {
                    if (typeof Html5Qrcode === 'undefined') {
                        setScanStatus('QR scanner library is not available. You can enter the code manually.', 'error');
                    }
                    return;
                }

                qrScanner = new Html5Qrcode('loyaltyQrReader');
                qrScanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 230, height: 230 } },
                    function(decodedText) {
                        lookupLoyaltyCode(decodedText);
                    },
                    function() {}
                )
                    .then(function() {
                        isScanning = true;
                        setScanStatus('Camera ready. Scan the loyalty card QR code.', null);
                    })
                    .catch(function() {
                        setScanStatus('Camera permission denied or unavailable. Enter the code manually.', 'error');
                    });
            }

            function stopScanner() {
                if (!qrScanner || !isScanning) {
                    return;
                }

                qrScanner.stop()
                    .then(function() {
                        return qrScanner.clear();
                    })
                    .finally(function() {
                        isScanning = false;
                        qrScanner = null;
                    });
            }

            modalElement.addEventListener('shown.bs.modal', function() {
                setClientButton(null);
                if (manualCodeInput) {
                    manualCodeInput.value = '';
                }
                setScanStatus('Opening camera...', null);
                startScanner();
            });

            modalElement.addEventListener('hidden.bs.modal', function() {
                stopScanner();
            });

            if (manualScanBtn) {
                manualScanBtn.addEventListener('click', function() {
                    lookupLoyaltyCode(manualCodeInput ? manualCodeInput.value : '');
                });
            }

            if (manualCodeInput) {
                manualCodeInput.addEventListener('keydown', function(event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        lookupLoyaltyCode(manualCodeInput.value);
                    }
                });
            }
        });
    </script>

    <script>
        window.markAsReadAndRedirect = function(notificationId, redirectUrl) {
            fetch('{{ route('notification.markRead') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ notification_id: notificationId })
            }).finally(function() {
                window.location.href = redirectUrl;
            });
        };

        document.addEventListener('DOMContentLoaded', function() {
            const bell = document.getElementById('notification-bell');
            const badge = document.getElementById('notification-badge');
            const liveToast = document.getElementById('notification-live-toast');

            if (!bell || !badge) {
                return;
            }

            let currentCount = parseInt(bell.dataset.currentCount || '0', 10);
            let latestNotificationId = bell.dataset.latestNotificationId || '';
            let audioContext = null;

            function unlockNotificationSound() {
                if (!audioContext) {
                    const AudioContext = window.AudioContext || window.webkitAudioContext;
                    if (AudioContext) {
                        audioContext = new AudioContext();
                    }
                }

                if (audioContext && audioContext.state === 'suspended') {
                    audioContext.resume();
                }
            }

            function playNotificationSound() {
                unlockNotificationSound();

                if (!audioContext || audioContext.state !== 'running') {
                    return;
                }

                const oscillator = audioContext.createOscillator();
                const gain = audioContext.createGain();

                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(880, audioContext.currentTime);
                oscillator.frequency.setValueAtTime(660, audioContext.currentTime + 0.12);

                gain.gain.setValueAtTime(0.001, audioContext.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.18, audioContext.currentTime + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.001, audioContext.currentTime + 0.35);

                oscillator.connect(gain);
                gain.connect(audioContext.destination);
                oscillator.start();
                oscillator.stop(audioContext.currentTime + 0.38);
            }

            function updateNotificationBadge(count) {
                badge.textContent = count > 0 ? count : '';
                badge.style.display = count > 0 ? '' : 'none';
                bell.dataset.currentCount = count;
            }

            function showNotificationToast() {
                if (!liveToast) {
                    return;
                }

                liveToast.classList.add('show');
                window.clearTimeout(liveToast.dataset.hideTimer);
                liveToast.dataset.hideTimer = window.setTimeout(function() {
                    liveToast.classList.remove('show');
                }, 4200);
            }

            document.addEventListener('click', unlockNotificationSound, { once: true });
            document.addEventListener('keydown', unlockNotificationSound, { once: true });

            setInterval(function() {
                fetch('{{ route('notifications.unreadCount') }}', {
                    headers: { 'Accept': 'application/json' }
                })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        const newCount = parseInt(data.count || '0', 10);
                        const newLatestNotificationId = data.latest_notification_id || '';
                        const hasNewNotification = newCount > currentCount && newLatestNotificationId !== latestNotificationId;

                        updateNotificationBadge(newCount);

                        if (hasNewNotification) {
                            playNotificationSound();
                            showNotificationToast();
                        }

                        currentCount = newCount;
                        latestNotificationId = newLatestNotificationId;
                    })
                    .catch(function(error) {
                        console.error('Notification polling error:', error);
                    });
            }, 5000);
        });
    </script>

    @yield('js')
</body>
</html>
