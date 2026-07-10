@extends('layouts.app')
@section('content')
<?php
$hasErrors = $errors->any();
$hasSelectedRole = old('selected_role');
$showLoginDirectly = $hasErrors && $hasSelectedRole;

$isDirect = request('direct') === 'true';
$showRoleSelection = $isDirect && !$showLoginDirectly;
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<main class="auth-shell">
    <section class="brand-panel">
        <div>
            <div class="logo-card">
                <img src="{{ asset('images/gazlite.png') }}" alt="GazLite">
            </div>
            <div class="brand-name">Distributor Management System</div>
        </div>

        <div class="brand-heading">
            <p class="eyebrow">Gaz Lite DMS</p>
            <h3 class="text-white">Manage your distributor network with one clean workspace.</h3>
            <p>Track dealers, inventory, orders, purchases, pricing, sales, alerts, and reports using role-based access for each partner level.</p>
        </div>

        <div class="brand-roles" aria-label="Supported partner levels">
            <div class="role-pill">SEDP</div>
            <div class="role-pill">Provincial Distributor</div>
            <div class="role-pill">Area Distributor</div>
            {{-- <div class="role-pill">Mega Dealer</div>
            <div class="role-pill">Dealer</div> --}}
        </div>
    </section>

    <section class="auth-panel">
        <div class="panel-top">
            <img src="{{ asset('images/logo_nya.png') }}" alt="GazLite Logo" class="compact-logo">
        </div>

        <div class="auth-step" id="landingPage" style="display: {{ $showLoginDirectly || $showRoleSelection ? 'none' : 'flex' }};">
            <div class="step-body">
                <div class="mobile-logo">
                    <img src="{{ asset('images/logo_sa_labas.png') }}" alt="GazLite Logo">
                </div>

                <img src="{{ asset('images/human.png') }}" alt="Easy management illustration" class="step-image landing-image">

                <div class="step-copy">
                    <p class="step-kicker">Welcome back</p>
                    <h2>Distributor Management System</h2>
                    <p>Manage your distributor network with one clean workspace.</p>
                </div>

                <div class="progress-dots" aria-label="Step 1 of 3">
                    <span class="dot active"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                </div>

                <button class="primary-button" type="button" onclick="showRoleSelection()">
                    Sign in
                </button>
            </div>
        </div>

        <div class="auth-step" id="roleSelectionPage" style="display: {{ $showRoleSelection ? 'flex' : 'none' }};">
            <div class="step-body">
                <button type="button" class="ghost-back" onclick="showLandingPage()" aria-label="Back to welcome">
                    <span aria-hidden="true">&lsaquo;</span>
                </button>

                <img src="{{ asset('images/context.png') }}" alt="Role selection" class="step-image role-image">

                <div class="step-copy">
                    <p class="step-kicker">Choose access</p>
                    <h2>How are you signing in?</h2>
                    <p>Select the account type connected to your credentials.</p>
                </div>

                <div class="role-buttons">
                    <button class="role-button" type="button" onclick="selectRole('admin', this)">
                        <span class="role-icon" aria-hidden="true">
                            <svg width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0m-9 8c0 1 1 1 1 1h5.256A4.5 4.5 0 0 1 8 12.5a4.5 4.5 0 0 1 1.544-3.393Q8.844 9.002 8 9c-5 0-6 3-6 4m9.886-3.54c.18-.613 1.048-.613 1.229 0l.043.148a.64.64 0 0 0 .921.382l.136-.074c.561-.306 1.175.308.87.869l-.075.136a.64.64 0 0 0 .382.92l.149.045c.612.18.612 1.048 0 1.229l-.15.043a.64.64 0 0 0-.38.921l.074.136c.305.561-.309 1.175-.87.87l-.136-.075a.64.64 0 0 0-.92.382l-.045.149c-.18.612-1.048.612-1.229 0l-.043-.15a.64.64 0 0 0-.921-.38l-.136.074c-.561.305-1.175-.309-.87-.87l.075-.136a.64.64 0 0 0-.382-.92l-.148-.045c-.613-.18-.613-1.048 0-1.229l.148-.043a.64.64 0 0 0 .382-.921l-.074-.136c-.306-.561.308-1.175.869-.87l.136.075a.64.64 0 0 0 .92-.382zM14 12.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0"/>
                            </svg>
                        </span>
                        <span>
                            <strong>Admin</strong>
                            <small>Manage users, reports, and system settings</small>
                        </span>
                        <span class="selected-indicator" aria-hidden="true">&#10003;</span>
                    </button>

                    <button class="role-button" type="button" onclick="selectRole('sedp', this)">
                        <span class="role-icon" aria-hidden="true">
                            <svg width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 1.5 1.75 4.25 8 7l6.25-2.75z"/>
                                <path d="M1.75 5.75 8 8.5l6.25-2.75v4.5L8 13 1.75 10.25z"/>
                            </svg>
                        </span>
                        <span>
                            <strong>SEDP</strong>
                            <small>Access assigned centers and admin tools</small>
                        </span>
                        <span class="selected-indicator" aria-hidden="true">&#10003;</span>
                    </button>

                    <button class="role-button" type="button" onclick="selectRole('area distributor', this)">
                        <span class="role-icon" aria-hidden="true">
                            <svg width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>
                            </svg>
                        </span>
                        <span>
                            <strong>Partner</strong>
                            <small>Access orders, inventory, and partner tools</small>
                        </span>
                        <span class="selected-indicator" aria-hidden="true">&#10003;</span>
                    </button>
                </div>

                <div class="progress-dots" aria-label="Step 2 of 3">
                    <span class="dot"></span>
                    <span class="dot active"></span>
                    <span class="dot"></span>
                </div>
            </div>
        </div>

        <div class="auth-step" id="loginFormPage" style="display: {{ $showLoginDirectly ? 'flex' : 'none' }};">
            <div class="step-body login-body">
                <button type="button" class="ghost-back" onclick="showRoleSelection()" aria-label="Back to role selection">
                    <span aria-hidden="true">&lsaquo;</span>
                </button>

                <div class="step-copy login-heading">
                    <p class="step-kicker">Secure sign in</p>
                    <h2>Enter your account details.</h2>
                    <p>Use the email or phone number registered to your account.</p>
                </div>

                <div class="role-indicator" id="roleIndicator" style="display: {{ $showLoginDirectly ? 'flex' : 'none' }};">
                    <span class="role-label">Signing in as</span>
                    <strong class="role-name" id="selectedRoleName">
                        @if(strtolower(old('selected_role')) === 'sedp')
                            SEDP
                        @else
                            {{ $hasSelectedRole ? ucwords(old('selected_role')) : 'User' }}
                        @endif
                    </strong>
                </div>

                <form id="loginForm" aria-label="{{ __('Login') }}">
                    @csrf
                    <input type="hidden" name="selected_role" id="selectedRoleInput" value="{{ old('selected_role') }}">

                    <div class="input-group">
                        <label for="email" class="input-label">Email or Phone Number</label>
                        <input
                            id="email"
                            type="text"
                            class="form-input"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="name@example.com"
                            required
                            autofocus
                        >
                    </div>

                    <div class="input-group">
                        <label class="input-label" for="password">Password</label>
                        <input
                            id="password"
                            type="password"
                            class="form-input"
                            placeholder="Enter password"
                            name="password"
                            required
                        >
                    </div>

                    <button class="primary-button signin-button" type="submit" id="signinButton">
                        Sign in
                    </button>

                    <div class="forgot-section">
                        <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                    </div>

                    <div class="progress-dots" aria-label="Step 3 of 3">
                        <span class="dot"></span>
                        <span class="dot"></span>
                        <span class="dot active"></span>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<style>
    :root {
        --auth-primary: #2f9bd7;
        --auth-primary-dark: #1678b4;
        --auth-ink: #132f45;
        --auth-muted: #6b7f8e;
        --auth-line: #d9e7ef;
        --auth-soft: #edf8fd;
        --auth-bg: #f6fbfd;
    }

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        min-height: 100vh;
        font-family: Inter, Nunito, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        background: var(--auth-bg);
        color: var(--auth-ink);
    }

    .auth-shell {
        min-height: 100vh;
        display: grid;
        grid-template-columns: minmax(460px, 1.08fr) minmax(380px, .92fr);
        background:
            radial-gradient(circle at 15% 12%, rgba(47, 155, 215, .16), transparent 28%),
            linear-gradient(135deg, #f8fdff 0%, #e8f6fb 100%);
    }

    .brand-panel {
        position: relative;
        min-height: 100vh;
        padding: clamp(28px, 4vw, 58px);
        display: flex;
        flex-direction: column;
        /* justify-content: space-between; */
        /* gap: 48px; */
        gap: 7em;
        overflow: hidden;
        background: linear-gradient(135deg, rgba(3, 58, 128, .94), rgba(7, 95, 195, .9)),
                linear-gradient(135deg, #063f8b, #0a74d7);
        color: #fff;
    }

    .brand-panel::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(8, 34, 51, .82), rgba(8, 34, 51, .32));
        pointer-events: none;
    }

    .brand-panel > * {
        position: relative;
        z-index: 1;
    }

    .logo-card {
        width: min(245px, 48vw);
        padding: 14px 18px;
        border-radius: 8px;
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 24px 70px rgba(0, 0, 0, .18);
    }

    .logo-card img,
    .mobile-logo img {
        display: block;
        width: 100%;
        height: auto;
    }

    .brand-name {
        margin-top: 18px;
        color: rgba(255, 255, 255, .86);
        font-size: 13px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .eyebrow,
    .step-kicker {
        margin: 0 0 10px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .eyebrow {
        color: #aee4ff;
    }

    .brand-heading {
        max-width: 720px;
    }

    .brand-heading h3 {
        margin: 0;
        max-width: 21ch;
        font-size: clamp(48px, 6vw, 52px);
        line-height: .98;
        font-weight: 800;
    }

    .brand-heading p:last-child {
        max-width: 610px;
        margin: 24px 0 0;
        color: rgba(255, 255, 255, .82);
        font-size: 17px;
        line-height: 1.7;
    }

    .brand-roles {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .role-pill
    {
        min-height: 54px;
        border: 1px solid rgba(255, 255, 255, .22);
        border-radius: .75rem;
        padding: .75rem .85rem;
        font-weight: 700;
        background: rgba(255, 255, 255, .14);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .12);
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        gap: .6rem;
    }

    .role-pill::before {
        content: "";
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #4bd3ff;
        box-shadow: 0 0 0 4px rgba(75, 211, 255, .16);
    }

    .auth-panel {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        padding: clamp(22px, 4vw, 48px);
        background: rgba(255, 255, 255, .82);
        backdrop-filter: blur(18px);
    }

    .panel-top {
        display: flex;
        justify-content: flex-end;
        min-height: 54px;
    }

    .compact-logo {
        width: 54px;
        height: 54px;
        object-fit: contain;
    }

    .auth-step {
        flex: 1;
        align-items: center;
        justify-content: center;
    }

    .step-body {
        position: relative;
        /* width: min(100%, 430px); */
        width: min(100%, 500px);
        margin: 0 auto;
    }

    .mobile-logo {
        display: none;
        width: 218px;
        margin: 0 auto 24px;
    }

    .step-image {
        display: block;
        height: auto;
        margin: 0 auto 28px;
    }

    .landing-image {
        width: min(58vw, 238px);
    }

    .role-image {
        width: min(46vw, 196px);
    }

    .step-copy {
        text-align: center;
        margin-bottom: 28px;
    }

    .step-kicker {
        color: var(--auth-primary);
    }

    .step-copy h2 {
        margin: 0;
        color: var(--auth-ink);
        font-size: clamp(28px, 3.6vw, 40px);
        line-height: 1.08;
        font-weight: 800;
    }

    .step-copy p:last-child {
        width: min(100%, 350px);
        margin: 14px auto 0;
        color: var(--auth-muted);
        font-size: 15px;
        line-height: 1.65;
    }

    .progress-dots {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin: 0 0 28px;
    }

    .dot {
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #c9d8e0;
        transition: width .18s ease, background-color .18s ease;
    }

    .dot.active {
        width: 28px;
        background: var(--auth-primary);
    }

    .primary-button {
        width: 100%;
        min-height: 56px;
        border: 0;
        border-radius: 8px;
        background: var(--auth-primary);
        color: #fff;
        font-size: 16px;
        font-weight: 800;
        cursor: pointer;
        box-shadow: 0 16px 34px rgba(47, 155, 215, .25);
        transition: transform .18s ease, background-color .18s ease, box-shadow .18s ease;
    }

    .primary-button:hover {
        background: var(--auth-primary-dark);
        box-shadow: 0 20px 40px rgba(22, 120, 180, .28);
        transform: translateY(-1px);
    }

    .primary-button:disabled {
        cursor: wait;
        opacity: .72;
        transform: none;
    }

    .ghost-back {
        position: absolute;
        top: -66px;
        left: 0;
        width: 42px;
        height: 42px;
        border: 1px solid var(--auth-line);
        border-radius: 999px;
        background: #fff;
        color: var(--auth-primary-dark);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 12px 28px rgba(19, 47, 69, .08);
        transition: border-color .18s ease, transform .18s ease;
    }

    .ghost-back span {
        font-size: 34px;
        line-height: 1;
        margin-top: -4px;
    }

    .ghost-back:hover {
        border-color: var(--auth-primary);
        transform: translateX(-2px);
    }

    .role-buttons {
        display: grid;
        gap: 12px;
        margin-bottom: 26px;
    }

    .role-button {
        width: 100%;
        min-height: 76px;
        padding: 14px 16px;
        border: 1px solid var(--auth-line);
        border-radius: 8px;
        background: #fff;
        color: var(--auth-ink);
        display: grid;
        grid-template-columns: 44px 1fr 24px;
        gap: 14px;
        align-items: center;
        text-align: left;
        cursor: pointer;
        box-shadow: 0 16px 36px rgba(19, 47, 69, .06);
        transition: border-color .18s ease, transform .18s ease, box-shadow .18s ease;
    }

    .role-button:hover,
    .role-button.selected {
        border-color: var(--auth-primary);
        box-shadow: 0 18px 42px rgba(47, 155, 215, .16);
        transform: translateY(-1px);
    }

    .role-icon {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        background: var(--auth-soft);
        color: var(--auth-primary-dark);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .role-button strong,
    .role-button small {
        display: block;
    }

    .role-button strong {
        font-size: 15px;
        color: var(--auth-ink);
    }

    .role-button small {
        margin-top: 3px;
        color: var(--auth-muted);
        font-size: 12px;
        line-height: 1.35;
    }

    .selected-indicator {
        opacity: 0;
        width: 24px;
        height: 24px;
        border-radius: 999px;
        background: var(--auth-primary);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 900;
    }

    .role-button.selected .selected-indicator {
        opacity: 1;
    }

    .login-body {
        width: min(100%, 420px);
    }

    .login-heading {
        text-align: left;
    }

    .login-heading p:last-child {
        margin-left: 0;
    }

    .role-indicator {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 22px;
        padding: 12px 14px;
        border: 1px solid #cbe7f6;
        border-radius: 8px;
        background: var(--auth-soft);
    }

    .role-label {
        color: var(--auth-muted);
        font-size: 13px;
        font-weight: 700;
    }

    .role-name {
        color: var(--auth-primary-dark);
        font-size: 14px;
    }

    .input-group {
        margin-bottom: 18px;
    }

    .input-label {
        display: block;
        margin-bottom: 8px;
        color: var(--auth-ink);
        font-size: 13px;
        font-weight: 800;
    }

    .form-input {
        width: 100%;
        height: 54px;
        padding: 0 16px;
        border: 1px solid var(--auth-line);
        border-radius: 8px !important;
        background: #fff;
        color: var(--auth-ink);
        font-size: 15px;
        outline: none;
        box-shadow: 0 14px 30px rgba(19, 47, 69, .05);
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    .form-input::placeholder {
        color: #9cafba;
    }

    .form-input:focus {
        border-color: var(--auth-primary);
        box-shadow: 0 0 0 4px rgba(47, 155, 215, .14);
    }

    .signin-button {
        margin-top: 6px;
    }

    .forgot-section {
        margin: 18px 0 24px;
        text-align: center;
    }

    .forgot-link {
        color: var(--auth-primary-dark);
        font-size: 14px;
        font-weight: 800;
        text-decoration: none;
    }

    .forgot-link:hover {
        text-decoration: underline;
    }

    @media (max-width: 900px) {
        .auth-shell {
            display: grid;
            grid-template-columns: 1fr;
            min-height: 100vh;
            background:
                linear-gradient(rgba(246, 251, 253, .88), rgba(246, 251, 253, .94)),
                url("{{ asset('images/gazlite.png') }}") center / cover no-repeat;
        }

        .brand-panel {
            min-height: auto;
            padding: 28px 20px 24px;
            gap: 24px;
            background:
                linear-gradient(135deg, rgba(10, 50, 74, .9), rgba(21, 111, 158, .78)),
                url("{{ asset('images/gazlite.png') }}") center / cover no-repeat;
        }

        .brand-panel::after {
            background: linear-gradient(180deg, rgba(8, 34, 51, .58), rgba(8, 34, 51, .32));
        }

        .logo-card {
            width: 190px;
        }

        .brand-name {
            margin-top: 12px;
            font-size: 11px;
        }

        .brand-heading {
            max-width: 620px;
        }

        .brand-heading h1 {
            max-width: 18ch;
            font-size: clamp(34px, 8vw, 48px);
            line-height: 1.04;
        }

        .brand-heading p:last-child {
            max-width: 560px;
            margin-top: 16px;
            font-size: 14px;
        }

        .brand-roles {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            max-width: 560px;
        }

        .auth-panel {
            min-height: auto;
            padding: 24px 18px;
            background: rgba(255, 255, 255, .9);
        }

        .panel-top {
            display: none;
        }

        .auth-step {
            min-height: calc(100vh - 48px);
        }

        .step-body {
            padding: 24px;
            border: 1px solid rgba(217, 231, 239, .9);
            border-radius: 8px;
            background: rgba(255, 255, 255, .94);
            box-shadow: 0 22px 70px rgba(19, 47, 69, .12);
        }

        .mobile-logo {
            display: block;
        }

        .ghost-back {
            top: 18px;
            left: 18px;
            z-index: 1;
        }

        .step-copy h2 {
            font-size: 30px;
        }

        .login-heading {
            padding-top: 48px;
        }
    }

    @media (max-width: 480px) {
        .brand-panel {
            padding: 22px 16px;
        }

        .logo-card {
            width: 168px;
        }

        .brand-heading h1 {
            font-size: 31px;
        }

        .brand-roles {
            grid-template-columns: 1fr;
            gap: 8px;
        }

        .role-pill {
            min-height: 40px;
        }

        .auth-panel {
            padding: 14px;
        }

        .auth-step {
            min-height: calc(100vh - 28px);
        }

        .step-body {
            padding: 22px 18px;
        }

        .landing-image {
            width: 172px;
        }

        .role-image {
            width: 168px;
        }

        .step-copy h2 {
            font-size: 27px;
        }

        .role-button {
            grid-template-columns: 40px 1fr 22px;
            min-height: 74px;
            padding: 12px;
        }

        .role-icon {
            width: 40px;
            height: 40px;
        }
    }
</style>

<script>
    let currentRole = "{{ old('selected_role') }}";

    window.showLandingPage = function () {
        document.getElementById('landingPage').style.display = 'flex';
        document.getElementById('roleSelectionPage').style.display = 'none';
        document.getElementById('loginFormPage').style.display = 'none';
    };

    window.showRoleSelection = function () {
        document.getElementById('landingPage').style.display = 'none';
        document.getElementById('roleSelectionPage').style.display = 'flex';
        document.getElementById('loginFormPage').style.display = 'none';
    };

    window.showLoginForm = function () {
        document.getElementById('landingPage').style.display = 'none';
        document.getElementById('roleSelectionPage').style.display = 'none';
        document.getElementById('loginFormPage').style.display = 'flex';

        updateLoginFormRole();
    };

    window.selectRole = function (role, button) {
        currentRole = role;

        document.querySelectorAll('.role-button').forEach(btn => {
            btn.classList.remove('selected');
        });

        if (button) {
            button.classList.add('selected');
        }

        document.getElementById('selectedRoleInput').value = role;

        setTimeout(() => {
            showLoginForm();
        }, 220);
    };

    function updateLoginFormRole() {
        if (!currentRole) {
            return;
        }

        document.getElementById('roleIndicator').style.display = 'flex';
        document.getElementById('selectedRoleName').innerText = formatRoleName(currentRole);
    }

    function formatRoleName(role) {
        if ((role || '').toLowerCase() === 'sedp') {
            return 'SEDP';
        }

        return role.replace(/\b\w/g, letter => letter.toUpperCase());
    }

    document.getElementById('loginForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const btn = document.getElementById('signinButton');
        const formData = new FormData(this);

        btn.disabled = true;
        btn.innerHTML = "Signing in...";

        try {
            const res = await fetch("{{ route('login') }}", {
                method: "POST",
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await res.json();

            if (data.success) {
                window.location.href = data.redirect;
            } else {
                Swal.fire("Error", data.message, "error");
            }
        } catch (err) {
            Swal.fire("Error", "Server error", "error");
        }

        btn.disabled = false;
        btn.innerHTML = "Sign in";
    });
</script>

@endsection
