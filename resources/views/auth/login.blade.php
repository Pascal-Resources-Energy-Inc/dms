@extends('layouts.app')

@section('title', 'Sign in | Gaz Lite DMS')

@section('content')
@php
    $selectedRole = strtolower((string) old('selected_role'));
    $loginError = $errors->first('email') ?: $errors->first('password') ?: $errors->first('selected_role');
@endphp

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<main class="login-page">
    <span class="ambient ambient-one" aria-hidden="true"></span>
    <span class="ambient ambient-two" aria-hidden="true"></span>

    <div class="login-layout">
        <aside class="login-showcase" aria-label="Gaz Lite Distributor Management System">
            <div class="showcase-grid" aria-hidden="true"></div>

            <div class="showcase-brand">
                <span class="brand-mark">
                    <img src="{{ asset('images/gazlite.png') }}" alt="Gaz Lite">
                </span>
                <span class="brand-product">Distributor Management System</span>
            </div>

            <div class="showcase-copy">
                <span class="showcase-eyebrow">
                    <span class="eyebrow-dot"></span>
                    Connected distribution, simplified
                </span>
                <h1>One Cylinder.<br><span>One System.</span></h1>
                <p>Manage inventory, orders, partners, and performance from one secure workspace built for the Gaz Lite distribution network.</p>

                <div class="feature-grid">
                    <div class="feature-item">
                        <span class="feature-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/></svg>
                        </span>
                        <span><strong>Live inventory</strong><small>Stay aligned across every level.</small></span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 20V10m7 10V4m7 16v-7"/><path d="M3 20h18"/></svg>
                        </span>
                        <span><strong>Clear reporting</strong><small>Turn activity into decisions.</small></span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>
                        </span>
                        <span><strong>Secure access</strong><small>Role-based tools for your team.</small></span>
                    </div>
                </div>
            </div>

            <div class="showcase-footer">
                <span class="status-pulse" aria-hidden="true"></span>
                Secure access for authorized Gaz Lite partners
            </div>
        </aside>

        <section class="login-workspace">
            <div class="mobile-brand">
                <img src="{{ asset('images/gazlite.png') }}" alt="Gaz Lite">
                <span>DMS</span>
            </div>

            <div class="login-card">
                <div class="login-heading">
                    <span class="heading-kicker">Welcome back</span>
                    <h2>Sign in to your account</h2>
                    <p>Enter your registered details to continue to your workspace.</p>
                </div>

                @if (session('status'))
                    <div class="notice notice-success" role="status">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <div id="serverError" class="notice notice-error" role="alert" aria-live="assertive" @if(!$loginError) hidden @endif>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 9v4m0 4h.01"/><path d="M10.3 3.6 2.4 17.2A2 2 0 0 0 4.1 20h15.8a2 2 0 0 0 1.7-2.8L13.7 3.6a2 2 0 0 0-3.4 0Z"/></svg>
                    <span id="serverErrorText">{{ $loginError }}</span>
                </div>

                <form id="loginForm" method="POST" action="{{ route('login') }}" novalidate>
                    @csrf

                    <fieldset class="role-fieldset">
                        <legend>Choose your workspace</legend>
                        <div class="role-options">
                            <label class="role-option">
                                <input type="radio" name="selected_role" value="admin" {{ $selectedRole === 'admin' ? 'checked' : '' }} required>
                                <span class="role-option-body">
                                    <span class="role-option-icon">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                                    </span>
                                    <span><strong>Admin</strong><small>Operations</small></span>
                                    <svg class="role-check" viewBox="0 0 24 24" aria-hidden="true"><path d="m7 12 3 3 7-7"/></svg>
                                </span>
                            </label>
                            <label class="role-option">
                                <input type="radio" name="selected_role" value="area distributor" {{ in_array($selectedRole, ['area distributor', 'provincial distributor'], true) ? 'checked' : '' }} required>
                                <span class="role-option-body">
                                    <span class="role-option-icon">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    </span>
                                    <span><strong>Partner</strong><small>PD / AD</small></span>
                                    <svg class="role-check" viewBox="0 0 24 24" aria-hidden="true"><path d="m7 12 3 3 7-7"/></svg>
                                </span>
                            </label>
                            <label class="role-option">
                                <input type="radio" name="selected_role" value="mfi" {{ $selectedRole === 'mfi' ? 'checked' : '' }} required>
                                <span class="role-option-body">
                                    <span class="role-option-icon">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 8 9-5 9 5-9 5-9-5Z"/><path d="m6 10v6l6 3 6-3v-6"/></svg>
                                    </span>
                                    <span><strong>MFI</strong><small>Centers</small></span>
                                    <svg class="role-check" viewBox="0 0 24 24" aria-hidden="true"><path d="m7 12 3 3 7-7"/></svg>
                                </span>
                            </label>
                        </div>
                        <small class="field-error" id="roleError"></small>
                    </fieldset>

                    <div class="form-field">
                        <label for="email">Email or phone number</label>
                        <div class="input-shell">
                            <svg class="input-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16v16H4z"/><path d="m4 6 8 7 8-7"/></svg>
                            <input id="email" type="text" name="email" value="{{ old('email') }}" placeholder="name@example.com" autocomplete="username" inputmode="email" required autofocus>
                        </div>
                        <small class="field-error" id="emailError"></small>
                    </div>

                    <div class="form-field">
                        <div class="field-label-row">
                            <label for="password">Password</label>
                            <a href="{{ route('password.request') }}">Forgot password?</a>
                        </div>
                        <div class="input-shell">
                            <svg class="input-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                            <input id="password" type="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                            <button class="password-toggle" type="button" id="passwordToggle" aria-label="Show password" aria-pressed="false">
                                <svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.9 5.2A10.8 10.8 0 0 1 12 5c6.5 0 10 7 10 7a16.5 16.5 0 0 1-2.1 3M6.2 6.2C3.5 8.1 2 12 2 12s3.5 7 10 7a9.7 9.7 0 0 0 4.1-.9"/></svg>
                            </button>
                        </div>
                        <small class="field-error" id="passwordError"></small>
                    </div>

                    <button class="signin-button" type="submit" id="signinButton">
                        <span class="button-label">Sign in securely</span>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                    </button>

                    <p class="secure-note">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                        Your credentials are encrypted and securely transmitted.
                    </p>
                </form>
            </div>

            <footer class="login-footer">&copy; {{ date('Y') }} Pascal Resources Energy, Inc.</footer>
        </section>
    </div>
</main>

<style>
    :root {
        --login-navy: #07345f;
        --login-blue: #0877ca;
        --login-sky: #28b8ec;
        --login-red: #ee5058;
        --login-ink: #102a43;
        --login-muted: #64778a;
        --login-line: #dbe6ee;
        --login-soft: #f5f9fc;
    }

    *, *::before, *::after { box-sizing: border-box; }
    html, body { min-height: 100%; }
    body { margin: 0; overflow-x: hidden; color: var(--login-ink); background: #edf6fb; font-family: Inter, Nunito, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
    button, input { font: inherit; }

    .login-page {
        position: relative;
        min-height: 100vh;
        min-height: 100svh;
        display: grid;
        place-items: center;
        padding: clamp(18px, 3.2vw, 52px);
        isolation: isolate;
        overflow: hidden;
        background: radial-gradient(circle at 8% 14%, rgba(40, 184, 236, .18), transparent 27%), radial-gradient(circle at 92% 88%, rgba(8, 119, 202, .14), transparent 25%), linear-gradient(145deg, #f8fcff 0%, #eaf5fb 52%, #f4fbff 100%);
    }

    .ambient { position: absolute; z-index: -1; border-radius: 999px; filter: blur(2px); pointer-events: none; }
    .ambient-one { width: 280px; height: 280px; top: -130px; right: 10%; background: rgba(238, 80, 88, .08); }
    .ambient-two { width: 360px; height: 360px; bottom: -210px; left: 4%; background: rgba(8, 119, 202, .1); }

    .login-layout {
        /* width: min(1240px, 100%); */
        min-height: min(780px, calc(100svh - clamp(36px, 6.4vw, 104px)));
        display: grid;
        grid-template-columns: minmax(390px, 1.03fr) minmax(440px, .97fr);
        overflow: hidden;
        background: #fff;
        border: 1px solid rgba(179, 205, 221, .64);
        border-radius: 30px;
        box-shadow: 0 35px 90px rgba(26, 75, 110, .18), 0 8px 28px rgba(26, 75, 110, .08);
    }

    .login-showcase {
        position: relative;
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 44px;
        padding: clamp(34px, 4.6vw, 70px);
        overflow: hidden;
        color: #fff;
        background: radial-gradient(circle at 88% 8%, rgba(56, 195, 238, .35), transparent 29%), radial-gradient(circle at 8% 90%, rgba(10, 154, 218, .3), transparent 30%), linear-gradient(145deg, #052f59 0%, #075798 54%, #0780c9 100%);
    }

    .login-showcase::after { content: ""; position: absolute; width: 360px; height: 360px; right: -200px; bottom: -180px; border: 46px solid rgba(255, 255, 255, .07); border-radius: 50%; }
    .showcase-grid { position: absolute; inset: 0; opacity: .13; background-image: linear-gradient(rgba(255,255,255,.18) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.18) 1px, transparent 1px); background-size: 44px 44px; mask-image: linear-gradient(to bottom right, #000, transparent 72%); }
    .showcase-brand, .showcase-copy, .showcase-footer { position: relative; z-index: 1; }
    .showcase-brand { display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
    .brand-mark { width: 174px; min-height: 58px; display: grid; place-items: center; padding: 9px 14px; background: #fff; border-radius: 14px; box-shadow: 0 14px 34px rgba(0, 25, 55, .2); }
    .brand-mark img { display: block; width: 100%; height: auto; }
    .brand-product { max-width: 150px; color: rgba(255,255,255,.8); font-size: 11px; font-weight: 800; line-height: 1.45; letter-spacing: .08em; text-transform: uppercase; }
    .showcase-copy { max-width: 590px; }
    .showcase-eyebrow { display: inline-flex; align-items: center; gap: 9px; margin-bottom: 20px; color: #c8f2ff; font-size: 11px; font-weight: 800; letter-spacing: .13em; text-transform: uppercase; }
    .eyebrow-dot { width: 8px; height: 8px; background: #55d7ff; border-radius: 50%; box-shadow: 0 0 0 6px rgba(85,215,255,.13); }
    .showcase-copy h1 { margin: 0; color: #fff; font-size: clamp(42px, 4.5vw, 64px); font-weight: 850; line-height: .98; letter-spacing: -.055em; }
    .showcase-copy h1 span { color: #75dcff; }
    .showcase-copy > p { max-width: 560px; margin: 24px 0 30px; color: rgba(255,255,255,.78); font-size: 15px; line-height: 1.75; }
    .feature-grid { display: grid; gap: 10px; }
    .feature-item { display: grid; grid-template-columns: 42px 1fr; align-items: center; gap: 12px; max-width: 370px; padding: 10px 12px; border: 1px solid rgba(255,255,255,.12); border-radius: 14px; background: rgba(255,255,255,.07); backdrop-filter: blur(10px); }
    .feature-icon { width: 42px; height: 42px; display: grid; place-items: center; color: #9ce7ff; background: rgba(255,255,255,.1); border-radius: 12px; }
    .feature-icon svg { width: 21px; fill: none; stroke: currentColor; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; }
    .feature-item strong, .feature-item small { display: block; }
    .feature-item strong { color: #fff; font-size: 13px; font-weight: 800; }
    .feature-item small { margin-top: 2px; color: rgba(255,255,255,.65); font-size: 11px; }
    .showcase-footer { display: flex; align-items: center; gap: 10px; color: rgba(255,255,255,.72); font-size: 11px; font-weight: 700; letter-spacing: .02em; }
    .status-pulse { width: 8px; height: 8px; flex: 0 0 auto; background: #63e9ae; border-radius: 50%; box-shadow: 0 0 0 6px rgba(99,233,174,.13); }

    .login-workspace { min-width: 0; display: flex; flex-direction: column; justify-content: center; padding: clamp(34px, 5vw, 70px); background: linear-gradient(180deg, #fff 0%, #fbfdff 100%); }
    .mobile-brand { display: none; }
    .login-card { width: min(490px, 100%); margin: auto; }
    .heading-kicker { display: block; margin-bottom: 9px; color: var(--login-blue); font-size: 11px; font-weight: 850; letter-spacing: .14em; text-transform: uppercase; }
    .login-heading h2 { margin: 0; color: var(--login-ink); font-size: clamp(30px, 3vw, 40px); font-weight: 850; line-height: 1.12; letter-spacing: -.035em; }
    .login-heading p { margin: 12px 0 0; color: var(--login-muted); font-size: 14px; line-height: 1.65; }

    .notice { display: grid; grid-template-columns: 21px 1fr; align-items: start; gap: 10px; margin-top: 20px; padding: 12px 14px; border: 1px solid; border-radius: 12px; font-size: 12px; font-weight: 700; line-height: 1.5; }
    .notice[hidden] { display: none; }
    .notice svg { width: 20px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .notice-success { color: #176b47; border-color: #bcebd6; background: #effbf5; }
    .notice-error { color: #a52c35; border-color: #ffd0d4; background: #fff5f6; }

    #loginForm { margin-top: 25px; }
    .role-fieldset { min-width: 0; margin: 0 0 20px; padding: 0; border: 0; }
    .role-fieldset legend, .form-field > label, .field-label-row label { margin: 0 0 9px; padding: 0; color: #314b61; font-size: 12px; font-weight: 800; }
    .role-options { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
    .role-option { position: relative; min-width: 0; margin: 0; cursor: pointer; }
    .role-option input { position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; }
    .role-option-body { position: relative; min-height: 78px; display: grid; grid-template-columns: 34px minmax(0, 1fr); align-items: center; gap: 8px; padding: 11px; overflow: hidden; border: 1.5px solid var(--login-line); border-radius: 14px; background: #fff; transition: border-color .2s ease, background .2s ease, box-shadow .2s ease, transform .2s ease; }
    .role-option:hover .role-option-body { border-color: #9fcfe9; background: #f8fcff; transform: translateY(-1px); }
    .role-option input:focus-visible + .role-option-body { outline: 3px solid rgba(8,119,202,.18); outline-offset: 2px; }
    .role-option input:checked + .role-option-body { color: var(--login-blue); border-color: var(--login-blue); background: #edf8ff; box-shadow: 0 7px 18px rgba(8,119,202,.12); }
    .role-option-icon { width: 34px; height: 34px; display: grid; place-items: center; color: #668196; background: var(--login-soft); border-radius: 10px; }
    .role-option input:checked + .role-option-body .role-option-icon { color: #fff; background: var(--login-blue); }
    .role-option-icon svg { width: 17px; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .role-option strong, .role-option small { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .role-option strong { color: #274157; font-size: 12px; font-weight: 850; }
    .role-option small { margin-top: 2px; color: #8393a1; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
    .role-check { position: absolute; top: 5px; right: 5px; width: 18px; color: var(--login-blue); fill: none; stroke: currentColor; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; opacity: 0; transform: scale(.7); transition: .2s ease; }
    .role-option input:checked + .role-option-body .role-check { opacity: 1; transform: scale(1); }

    .form-field { margin-bottom: 17px; }
    .field-label-row { display: flex; align-items: center; justify-content: space-between; gap: 15px; }
    .field-label-row a { margin-bottom: 9px; color: var(--login-blue); font-size: 11px; font-weight: 800; text-decoration: none; }
    .field-label-row a:hover { text-decoration: underline; }
    .input-shell { position: relative; }
    .input-icon { position: absolute; top: 50%; left: 15px; width: 19px; transform: translateY(-50%); color: #7890a3; fill: none; stroke: currentColor; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; pointer-events: none; }
    .input-shell input { width: 100%; height: 52px; padding: 0 48px 0 45px; color: var(--login-ink); font-size: 14px; font-weight: 650; border: 1.5px solid var(--login-line); border-radius: 13px; outline: none; background: #fff; transition: border-color .2s ease, box-shadow .2s ease, background .2s ease; }
    .input-shell input::placeholder { color: #a1b0bc; font-weight: 500; }
    .input-shell input:hover { border-color: #bad1df; }
    .input-shell input:focus { border-color: var(--login-blue); box-shadow: 0 0 0 4px rgba(8,119,202,.1); }
    .form-field.has-error .input-shell input { border-color: var(--login-red); background: #fffafa; }
    .password-toggle { position: absolute; top: 50%; right: 8px; width: 38px; height: 38px; display: grid; place-items: center; padding: 0; transform: translateY(-50%); color: #72899c; border: 0; border-radius: 9px; background: transparent; cursor: pointer; }
    .password-toggle:hover { color: var(--login-blue); background: #edf7fc; }
    .password-toggle:focus-visible { outline: 3px solid rgba(8,119,202,.18); }
    .password-toggle svg { width: 20px; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .password-toggle .eye-closed { display: none; }
    .password-toggle.is-visible .eye-open { display: none; }
    .password-toggle.is-visible .eye-closed { display: block; }
    .field-error { display: block; min-height: 0; margin-top: 6px; color: #b52d37; font-size: 11px; font-weight: 700; line-height: 1.35; }
    .field-error:empty { display: none; }

    .signin-button { width: 100%; min-height: 53px; display: flex; align-items: center; justify-content: center; gap: 11px; margin-top: 7px; padding: 12px 20px; color: #fff; font-size: 13px; font-weight: 850; border: 0; border-radius: 13px; background: linear-gradient(135deg, #0877ca, #075da7); box-shadow: 0 13px 25px rgba(8,119,202,.24); cursor: pointer; transition: transform .2s ease, box-shadow .2s ease, filter .2s ease; }
    .signin-button:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 16px 30px rgba(8,119,202,.3); filter: saturate(1.08); }
    .signin-button:focus-visible { outline: 3px solid rgba(8,119,202,.22); outline-offset: 3px; }
    .signin-button:disabled { cursor: wait; opacity: .72; box-shadow: none; }
    .signin-button svg { width: 19px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .button-spinner { width: 17px; height: 17px; border: 2px solid rgba(255,255,255,.42); border-top-color: #fff; border-radius: 50%; animation: login-spin .7s linear infinite; }
    @keyframes login-spin { to { transform: rotate(360deg); } }
    .secure-note { display: flex; align-items: center; justify-content: center; gap: 7px; margin: 15px 0 0; color: #8494a1; font-size: 10px; font-weight: 650; text-align: center; }
    .secure-note svg { width: 14px; flex: 0 0 auto; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .login-footer { width: min(490px, 100%); margin: 26px auto 0; color: #8a9ba8; font-size: 10px; font-weight: 650; text-align: center; }

    @media (max-width: 1050px) {
        .login-layout { grid-template-columns: minmax(340px, .86fr) minmax(420px, 1.14fr); }
        .login-showcase { padding: 42px 36px; }
        .showcase-copy h1 { font-size: clamp(38px, 5vw, 52px); }
        .brand-product { display: none; }
    }

    @media (max-width: 820px) {
        .login-page { place-items: start center; padding: 18px; overflow: visible; }
        .login-layout { min-height: calc(100svh - 36px); grid-template-columns: 1fr; }
        .login-showcase { min-height: auto; display: block; padding: 24px 28px; }
        .showcase-brand { justify-content: center; }
        .brand-mark { width: 150px; min-height: 48px; padding: 7px 12px; }
        .showcase-copy { margin: 18px auto 0; text-align: center; }
        .showcase-eyebrow, .showcase-copy > p, .feature-grid, .showcase-footer { display: none; }
        .showcase-copy h1 { font-size: 28px; line-height: 1.08; letter-spacing: -.035em; }
        .showcase-copy h1 br { display: none; }
        .login-workspace { padding: 36px clamp(26px, 7vw, 64px) 24px; }
        .login-card, .login-footer { width: min(520px, 100%); }
    }

    @media (max-width: 540px) {
        .login-page { padding: 0; background: #fff; }
        .ambient { display: none; }
        .login-layout { min-height: 100svh; border: 0; border-radius: 0; box-shadow: none; }
        .login-showcase { display: none; }
        .login-workspace { justify-content: flex-start; padding: max(24px, env(safe-area-inset-top)) 20px max(20px, env(safe-area-inset-bottom)); }
        .mobile-brand { display: flex; align-items: center; justify-content: space-between; width: 100%; margin-bottom: clamp(30px, 9vh, 62px); }
        .mobile-brand img { width: 142px; height: auto; }
        .mobile-brand span { padding: 6px 9px; color: var(--login-blue); font-size: 10px; font-weight: 900; letter-spacing: .12em; border: 1px solid #cbe6f5; border-radius: 8px; background: #f1faff; }
        .login-card { margin: 0 auto; }
        .login-heading h2 { font-size: 30px; }
        .login-heading p { max-width: 34ch; }
        .role-options { gap: 6px; }
        .role-option-body { min-height: 72px; grid-template-columns: 30px minmax(0, 1fr); gap: 6px; padding: 9px 8px; border-radius: 12px; }
        .role-option-icon { width: 30px; height: 30px; border-radius: 8px; }
        .role-option-icon svg { width: 15px; }
        .role-option strong { font-size: 11px; }
        .role-option small { font-size: 8px; }
        .input-shell input { height: 50px; font-size: 16px; }
        .login-footer { margin-top: auto; padding-top: 26px; }
    }

    @media (max-width: 370px) {
        .login-workspace { padding-left: 15px; padding-right: 15px; }
        .mobile-brand { margin-bottom: 28px; }
        .role-option-body { grid-template-columns: 1fr; justify-items: center; text-align: center; }
        .role-option-icon, .role-option small { display: none; }
    }

    @media (min-width: 821px) and (max-height: 720px) {
        .login-page { padding: 14px; }
        .login-layout { min-height: calc(100svh - 28px); }
        .login-showcase { gap: 20px; padding: 30px 38px; }
        .showcase-copy h1 { font-size: 40px; }
        .showcase-copy > p { margin: 16px 0 20px; line-height: 1.55; }
        .feature-grid { grid-template-columns: repeat(3, 1fr); max-width: 520px; }
        .feature-item { grid-template-columns: 34px 1fr; padding: 8px; }
        .feature-icon { width: 34px; height: 34px; }
        .feature-item small { display: none; }
        .login-workspace { padding-top: 25px; padding-bottom: 20px; overflow-y: auto; }
        #loginForm { margin-top: 18px; }
        .form-field { margin-bottom: 12px; }
        .login-footer { margin-top: 16px; }
    }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after { scroll-behavior: auto !important; animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; }
    }
</style>

<script>
    (function () {
        const form = document.getElementById('loginForm');
        const submitButton = document.getElementById('signinButton');
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('passwordToggle');
        const serverError = document.getElementById('serverError');
        const serverErrorText = document.getElementById('serverErrorText');

        function setError(field, message) {
            const error = document.getElementById(field + 'Error');
            const input = field === 'role' ? form.querySelector('input[name="selected_role"]') : document.getElementById(field);

            if (error) error.textContent = message || '';

            if (input) {
                const wrapper = field === 'role' ? input.closest('.role-fieldset') : input.closest('.form-field');
                if (wrapper) wrapper.classList.toggle('has-error', Boolean(message));
                input.setAttribute('aria-invalid', message ? 'true' : 'false');
            }
        }

        function showServerError(message) {
            serverErrorText.textContent = message;
            serverError.hidden = false;
        }

        function clearErrors() {
            ['role', 'email', 'password'].forEach(function (field) { setError(field, ''); });
            serverError.hidden = true;
        }

        function validateForm() {
            let firstInvalid = null;
            const role = form.querySelector('input[name="selected_role"]:checked');
            const email = document.getElementById('email');

            if (!role) {
                setError('role', 'Choose the workspace connected to your account.');
                firstInvalid = form.querySelector('input[name="selected_role"]');
            }

            if (!email.value.trim()) {
                setError('email', 'Enter your registered email or phone number.');
                firstInvalid = firstInvalid || email;
            }

            if (!passwordInput.value) {
                setError('password', 'Enter your password.');
                firstInvalid = firstInvalid || passwordInput;
            }

            if (firstInvalid) {
                firstInvalid.focus();
                return false;
            }

            return true;
        }

        function setLoading(isLoading) {
            submitButton.disabled = isLoading;
            submitButton.innerHTML = isLoading
                ? '<span class="button-spinner" aria-hidden="true"></span><span class="button-label">Signing in...</span>'
                : '<span class="button-label">Sign in securely</span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>';
        }

        passwordToggle.addEventListener('click', function () {
            const isVisible = passwordInput.type === 'text';
            passwordInput.type = isVisible ? 'password' : 'text';
            this.classList.toggle('is-visible', !isVisible);
            this.setAttribute('aria-pressed', String(!isVisible));
            this.setAttribute('aria-label', isVisible ? 'Show password' : 'Hide password');
            passwordInput.focus();
        });

        form.querySelectorAll('input').forEach(function (input) {
            input.addEventListener('input', function () {
                const field = input.name === 'selected_role' ? 'role' : input.id;
                setError(field, '');
                serverError.hidden = true;
            });
            input.addEventListener('change', function () {
                if (input.name === 'selected_role') setError('role', '');
            });
        });

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            clearErrors();

            if (!validateForm()) return;

            setLoading(true);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                const data = await response.json().catch(function () { return {}; });

                if (response.ok && data.success) {
                    window.location.assign(data.redirect);
                    return;
                }

                const errors = data.errors || {};
                const message = (errors.email && errors.email[0]) || (errors.password && errors.password[0]) || (errors.selected_role && errors.selected_role[0]) || data.message || 'Unable to sign in. Please check your details and try again.';

                showServerError(message);

                if (window.Swal) {
                    Swal.fire({ icon: 'error', title: 'Sign in failed', text: message, confirmButtonText: 'Try again', confirmButtonColor: '#0877ca' });
                }
            } catch (error) {
                const message = 'We could not reach the server. Check your connection and try again.';
                showServerError(message);

                if (window.Swal) {
                    Swal.fire({ icon: 'error', title: 'Connection problem', text: message, confirmButtonText: 'Try again', confirmButtonColor: '#0877ca' });
                }
            } finally {
                setLoading(false);
            }
        });

        @if (session('status'))
            document.addEventListener('DOMContentLoaded', function () {
                if (window.Swal) {
                    Swal.fire({ icon: 'success', title: 'Password reset successful', text: @json(session('status')), confirmButtonText: 'Continue to sign in', confirmButtonColor: '#0877ca' });
                }
            });
        @endif
    })();
</script>
@endsection
