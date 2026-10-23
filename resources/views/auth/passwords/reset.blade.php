@extends('layouts.app')

@section('title', 'Create new password | Gaz Lite DMS')

@section('content')
<main class="reset-page">
    <span class="reset-ambient reset-ambient-one" aria-hidden="true"></span>
    <span class="reset-ambient reset-ambient-two" aria-hidden="true"></span>

    <div class="reset-layout">
        <aside class="reset-showcase" aria-label="Gaz Lite password recovery">
            <div class="reset-grid" aria-hidden="true"></div>

            <div class="reset-brand">
                <span class="reset-brand-mark">
                    <img src="{{ asset('images/gazlite.png') }}" alt="Gaz Lite">
                </span>
                <span class="reset-brand-product">Distributor Management System</span>
            </div>

            <div class="reset-showcase-copy">
                <span class="reset-eyebrow">
                    <span class="reset-eyebrow-dot"></span>
                    Secure account recovery
                </span>
                <h1>Fresh password.<br><span>Protected access.</span></h1>
                <p>Create a strong new password to complete recovery and securely return to your Gaz Lite workspace.</p>

                <ol class="reset-steps" aria-label="Password recovery progress">
                    <li class="reset-step is-complete">
                        <span class="reset-step-number">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 12 4 4 8-8"/></svg>
                        </span>
                        <span><strong>Identify your account</strong><small>Your account was located.</small></span>
                    </li>
                    <li class="reset-step is-complete">
                        <span class="reset-step-number">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 12 4 4 8-8"/></svg>
                        </span>
                        <span><strong>Verify your identity</strong><small>Your one-time code was accepted.</small></span>
                    </li>
                    <li class="reset-step is-active" aria-current="step">
                        <span class="reset-step-number">3</span>
                        <span><strong>Create a new password</strong><small>Choose a strong, unique password.</small></span>
                    </li>
                </ol>
            </div>

            <div class="reset-showcase-footer">
                <span class="reset-status-pulse" aria-hidden="true"></span>
                Protected recovery for authorized Gaz Lite users
            </div>
        </aside>

        <section class="reset-workspace">
            <div class="reset-mobile-brand">
                <img src="{{ asset('images/gazlite.png') }}" alt="Gaz Lite">
                <span>DMS</span>
            </div>

            <div class="reset-card">
                <a class="reset-back" href="{{ route('login', ['direct' => 'true']) }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5m6 6-6-6 6-6"/></svg>
                    Back to sign in
                </a>

                <div class="reset-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/><path d="M12 14v3"/></svg>
                </div>

                <div class="reset-heading">
                    <span class="reset-kicker">Step 3 of 3</span>
                    <h2>Create a new password</h2>
                    <p>Your identity is verified. Choose a secure password for:</p>
                    <div class="reset-email" title="{{ $email }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                        <strong>{{ $email }}</strong>
                        <span>Verified</span>
                    </div>
                </div>

                @if (session('success'))
                    <div class="reset-notice reset-notice-success" role="status">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="reset-notice reset-notice-error" role="alert">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 9v4m0 4h.01"/><path d="M10.3 3.6 2.4 17.2A2 2 0 0 0 4.1 20h15.8a2 2 0 0 0 1.7-2.8L13.7 3.6a2 2 0 0 0-3.4 0Z"/></svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" id="resetPasswordForm" novalidate>
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">

                    <div class="reset-field{{ $errors->has('password') ? ' has-error' : '' }}" id="passwordField">
                        <label for="password">New password</label>
                        <div class="reset-input-shell">
                            <svg class="reset-input-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Enter a strong new password"
                                autocomplete="new-password"
                                aria-describedby="passwordError passwordStrength passwordRequirements"
                                aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                                minlength="8"
                                required
                                autofocus
                            >
                            <button class="reset-password-toggle" type="button" data-toggle-password="password" aria-label="Show new password" aria-pressed="false">
                                <svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.9 5.2A10.8 10.8 0 0 1 12 5c6.5 0 10 7 10 7a16.5 16.5 0 0 1-2.1 3M6.2 6.2C3.5 8.1 2 12 2 12s3.5 7 10 7a9.7 9.7 0 0 0 4.1-.9"/></svg>
                            </button>
                        </div>
                        <small class="reset-field-error" id="passwordError">{{ $errors->first('password') }}</small>
                    </div>

                    <div class="password-status" id="passwordStrength" aria-live="polite">
                        <div class="password-status-row">
                            <span>Password strength</span>
                            <strong id="strengthLabel">Not set</strong>
                        </div>
                        <div class="strength-track" aria-hidden="true"><span id="strengthBar"></span></div>
                    </div>

                    <ul class="password-requirements" id="passwordRequirements" aria-label="Password requirements">
                        <li id="req-length"><span></span>8+ characters</li>
                        <li id="req-uppercase"><span></span>Uppercase letter</li>
                        <li id="req-lowercase"><span></span>Lowercase letter</li>
                        <li id="req-number"><span></span>Number</li>
                        <li id="req-special"><span></span>Special: @$!%*?&amp;</li>
                    </ul>

                    <div class="reset-field" id="confirmField">
                        <label for="password-confirm">Confirm new password</label>
                        <div class="reset-input-shell">
                            <svg class="reset-input-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                            <input
                                id="password-confirm"
                                type="password"
                                name="password_confirmation"
                                placeholder="Re-enter your new password"
                                autocomplete="new-password"
                                aria-describedby="confirmError"
                                required
                            >
                            <button class="reset-password-toggle" type="button" data-toggle-password="password-confirm" aria-label="Show confirmed password" aria-pressed="false">
                                <svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.9 5.2A10.8 10.8 0 0 1 12 5c6.5 0 10 7 10 7a16.5 16.5 0 0 1-2.1 3M6.2 6.2C3.5 8.1 2 12 2 12s3.5 7 10 7a9.7 9.7 0 0 0 4.1-.9"/></svg>
                            </button>
                        </div>
                        <small class="reset-field-error" id="confirmError" aria-live="polite"></small>
                    </div>

                    <button class="reset-submit" type="submit" id="resetButton" disabled>
                        <span>Update password securely</span>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                    </button>

                    <p class="reset-secure-note">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                        Your new password is encrypted before it is stored.
                    </p>
                </form>
            </div>

            <footer class="reset-footer">&copy; {{ date('Y') }} Pascal Resources Energy, Inc.</footer>
        </section>
    </div>
</main>

<style>
    :root {
        --reset-navy: #07345f;
        --reset-blue: #0877ca;
        --reset-sky: #28b8ec;
        --reset-red: #ee5058;
        --reset-green: #1f9d6a;
        --reset-ink: #102a43;
        --reset-muted: #64778a;
        --reset-line: #dbe6ee;
        --reset-soft: #f5f9fc;
    }

    *, *::before, *::after { box-sizing: border-box; }
    html, body { min-height: 100%; }
    body { margin: 0; overflow-x: hidden; color: var(--reset-ink); background: #edf6fb; font-family: Inter, Nunito, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
    button, input { font: inherit; }

    .reset-page { position: relative; min-height: 100vh; min-height: 100svh; display: grid; place-items: center; padding: clamp(18px, 3.2vw, 52px); isolation: isolate; overflow: hidden; background: radial-gradient(circle at 8% 14%, rgba(40,184,236,.18), transparent 27%), radial-gradient(circle at 92% 88%, rgba(8,119,202,.14), transparent 25%), linear-gradient(145deg, #f8fcff 0%, #eaf5fb 52%, #f4fbff 100%); }
    .reset-ambient { position: absolute; z-index: -1; border-radius: 999px; filter: blur(2px); pointer-events: none; }
    .reset-ambient-one { width: 280px; height: 280px; top: -130px; right: 10%; background: rgba(238,80,88,.08); }
    .reset-ambient-two { width: 360px; height: 360px; bottom: -210px; left: 4%; background: rgba(8,119,202,.1); }

    .reset-layout { 
        /* width: min(1240px, 100%);  */
        min-height: min(780px, calc(100svh - clamp(36px, 6.4vw, 104px))); 
        display: grid; 
        grid-template-columns: minmax(390px, 1.03fr) minmax(440px, .97fr); 
        overflow: hidden; background: #fff; 
        border: 1px solid rgba(179,205,221,.64); 
        border-radius: 30px; 
        box-shadow: 0 35px 90px rgba(26,75,110,.18), 0 8px 28px rgba(26,75,110,.08); 
    }

    .reset-showcase { position: relative; min-width: 0; display: flex; flex-direction: column; justify-content: space-between; gap: 44px; padding: clamp(34px, 4.6vw, 70px); overflow: hidden; color: #fff; background: radial-gradient(circle at 88% 8%, rgba(56,195,238,.35), transparent 29%), radial-gradient(circle at 8% 90%, rgba(10,154,218,.3), transparent 30%), linear-gradient(145deg, #052f59 0%, #075798 54%, #0780c9 100%); }
    .reset-showcase::after { content: ""; position: absolute; width: 360px; height: 360px; right: -200px; bottom: -180px; border: 46px solid rgba(255,255,255,.07); border-radius: 50%; }
    .reset-grid { position: absolute; inset: 0; opacity: .13; background-image: linear-gradient(rgba(255,255,255,.18) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.18) 1px, transparent 1px); background-size: 44px 44px; mask-image: linear-gradient(to bottom right, #000, transparent 72%); }
    .reset-brand, .reset-showcase-copy, .reset-showcase-footer { position: relative; z-index: 1; }
    .reset-brand { display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
    .reset-brand-mark { width: 174px; min-height: 58px; display: grid; place-items: center; padding: 9px 14px; background: #fff; border-radius: 14px; box-shadow: 0 14px 34px rgba(0,25,55,.2); }
    .reset-brand-mark img { display: block; width: 100%; height: auto; }
    .reset-brand-product { max-width: 150px; color: rgba(255,255,255,.8); font-size: 11px; font-weight: 800; line-height: 1.45; letter-spacing: .08em; text-transform: uppercase; }
    .reset-showcase-copy { max-width: 590px; }
    .reset-eyebrow { display: inline-flex; align-items: center; gap: 9px; margin-bottom: 20px; color: #c8f2ff; font-size: 11px; font-weight: 800; letter-spacing: .13em; text-transform: uppercase; }
    .reset-eyebrow-dot { width: 8px; height: 8px; background: #55d7ff; border-radius: 50%; box-shadow: 0 0 0 6px rgba(85,215,255,.13); }
    .reset-showcase-copy h1 { margin: 0; color: #fff; font-size: clamp(42px, 4.5vw, 64px); font-weight: 850; line-height: .98; letter-spacing: -.055em; }
    .reset-showcase-copy h1 span { color: #75dcff; }
    .reset-showcase-copy > p { max-width: 560px; margin: 24px 0 28px; color: rgba(255,255,255,.78); font-size: 15px; line-height: 1.75; }

    .reset-steps { display: grid; gap: 10px; margin: 0; padding: 0; list-style: none; }
    .reset-step { display: grid; grid-template-columns: 38px 1fr; align-items: center; gap: 12px; max-width: 390px; padding: 11px 13px; border: 1px solid rgba(255,255,255,.12); border-radius: 14px; background: rgba(255,255,255,.07); backdrop-filter: blur(10px); }
    .reset-step.is-active { border-color: rgba(117,220,255,.46); background: rgba(117,220,255,.13); }
    .reset-step-number { width: 36px; height: 36px; display: grid; place-items: center; color: #bdefff; font-size: 12px; font-weight: 900; border: 1px solid rgba(156,231,255,.35); border-radius: 11px; background: rgba(255,255,255,.08); }
    .reset-step-number svg { width: 18px; fill: none; stroke: currentColor; stroke-width: 2.4; stroke-linecap: round; stroke-linejoin: round; }
    .reset-step.is-complete .reset-step-number { color: #146848; border-color: transparent; background: #75e5b3; }
    .reset-step.is-active .reset-step-number { color: #075798; border-color: transparent; background: #8ce4ff; }
    .reset-step strong, .reset-step small { display: block; }
    .reset-step strong { color: #fff; font-size: 13px; font-weight: 800; }
    .reset-step small { margin-top: 2px; color: rgba(255,255,255,.65); font-size: 11px; }
    .reset-showcase-footer { display: flex; align-items: center; gap: 10px; color: rgba(255,255,255,.72); font-size: 11px; font-weight: 700; letter-spacing: .02em; }
    .reset-status-pulse { width: 8px; height: 8px; flex: 0 0 auto; background: #63e9ae; border-radius: 50%; box-shadow: 0 0 0 6px rgba(99,233,174,.13); }

    .reset-workspace { min-width: 0; display: flex; flex-direction: column; justify-content: center; padding: clamp(26px, 3.6vw, 52px); background: linear-gradient(180deg, #fff 0%, #fbfdff 100%); }
    .reset-mobile-brand { display: none; }
    .reset-card { width: min(500px, 100%); margin: auto; }
    .reset-back { display: inline-flex; align-items: center; gap: 7px; margin-bottom: 17px; color: #5f7486; font-size: 11px; font-weight: 800; text-decoration: none; }
    .reset-back:hover { color: var(--reset-blue); }
    .reset-back:focus-visible { outline: 3px solid rgba(8,119,202,.18); outline-offset: 4px; border-radius: 5px; }
    .reset-back svg { width: 17px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .reset-icon { width: 48px; height: 48px; display: grid; place-items: center; margin-bottom: 13px; color: var(--reset-blue); border: 1px solid #c9e8f8; border-radius: 15px; background: linear-gradient(145deg, #eef9ff, #e1f4fe); box-shadow: 0 10px 24px rgba(8,119,202,.12); }
    .reset-icon svg { width: 25px; fill: none; stroke: currentColor; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; }
    .reset-kicker { display: block; margin-bottom: 7px; color: var(--reset-blue); font-size: 11px; font-weight: 850; letter-spacing: .14em; text-transform: uppercase; }
    .reset-heading h2 { margin: 0; color: var(--reset-ink); font-size: clamp(28px, 2.7vw, 36px); font-weight: 850; line-height: 1.12; letter-spacing: -.035em; }
    .reset-heading > p { margin: 8px 0 7px; color: var(--reset-muted); font-size: 13px; line-height: 1.5; }
    .reset-email { max-width: 100%; display: inline-flex; align-items: center; gap: 7px; padding: 6px 9px; color: #1d684d; border: 1px solid #c9eadc; border-radius: 9px; background: #f0fbf6; font-size: 10px; }
    .reset-email svg { width: 14px; flex: 0 0 auto; fill: none; stroke: currentColor; stroke-width: 2.2; stroke-linecap: round; stroke-linejoin: round; }
    .reset-email strong { max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .reset-email span { padding-left: 7px; border-left: 1px solid #b6ddcd; font-size: 8px; font-weight: 900; letter-spacing: .07em; text-transform: uppercase; }

    .reset-notice { display: grid; grid-template-columns: 21px 1fr; align-items: start; gap: 10px; margin-top: 14px; padding: 10px 12px; border: 1px solid; border-radius: 11px; font-size: 11px; font-weight: 700; line-height: 1.45; }
    .reset-notice svg { width: 19px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .reset-notice-success { color: #176b47; border-color: #bcebd6; background: #effbf5; }
    .reset-notice-error { color: #a52c35; border-color: #ffd0d4; background: #fff5f6; }

    #resetPasswordForm { margin-top: 16px; }
    .reset-field { margin-bottom: 12px; }
    .reset-field > label { display: block; margin: 0 0 7px; color: #314b61; font-size: 11px; font-weight: 800; }
    .reset-input-shell { position: relative; }
    .reset-input-icon { position: absolute; top: 50%; left: 14px; width: 18px; transform: translateY(-50%); color: #7890a3; fill: none; stroke: currentColor; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; pointer-events: none; }
    .reset-input-shell input { width: 100%; height: 49px; padding: 0 48px 0 43px; color: var(--reset-ink); font-size: 13px; font-weight: 650; border: 1.5px solid var(--reset-line); border-radius: 12px; outline: none; background: #fff; transition: border-color .2s ease, box-shadow .2s ease, background .2s ease; }
    .reset-input-shell input::placeholder { color: #a1b0bc; font-weight: 500; }
    .reset-input-shell input:hover { border-color: #bad1df; }
    .reset-input-shell input:focus { border-color: var(--reset-blue); box-shadow: 0 0 0 4px rgba(8,119,202,.1); }
    .reset-field.has-error .reset-input-shell input { border-color: var(--reset-red); background: #fffafa; }
    .reset-field.is-valid .reset-input-shell input { border-color: #65c59f; background: #fbfffd; }
    .reset-password-toggle { position: absolute; top: 50%; right: 7px; width: 36px; height: 36px; display: grid; place-items: center; padding: 0; transform: translateY(-50%); color: #72899c; border: 0; border-radius: 9px; background: transparent; cursor: pointer; }
    .reset-password-toggle:hover { color: var(--reset-blue); background: #edf7fc; }
    .reset-password-toggle:focus-visible { outline: 3px solid rgba(8,119,202,.18); }
    .reset-password-toggle svg { width: 19px; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .reset-password-toggle .eye-closed { display: none; }
    .reset-password-toggle.is-visible .eye-open { display: none; }
    .reset-password-toggle.is-visible .eye-closed { display: block; }
    .reset-field-error { display: block; margin-top: 5px; color: #b52d37; font-size: 10px; font-weight: 700; line-height: 1.3; }
    .reset-field-error:empty { display: none; }

    .password-status { margin: 0 0 8px; }
    .password-status-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; color: #748899; font-size: 9px; font-weight: 750; }
    .password-status-row strong { color: #8193a1; font-size: 9px; }
    .strength-track { height: 4px; margin-top: 5px; overflow: hidden; border-radius: 99px; background: #e7eef3; }
    .strength-track span { display: block; width: 0; height: 100%; border-radius: inherit; background: #c4cdd4; transition: width .25s ease, background .25s ease; }
    .password-status.is-weak .strength-track span { width: 35%; background: #e95a64; }
    .password-status.is-medium .strength-track span { width: 68%; background: #e7a52f; }
    .password-status.is-strong .strength-track span { width: 100%; background: #2baa78; }
    .password-status.is-weak .password-status-row strong { color: #c9444f; }
    .password-status.is-medium .password-status-row strong { color: #a96d0a; }
    .password-status.is-strong .password-status-row strong { color: #20875e; }

    .password-requirements { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 5px 12px; margin: 0 0 13px; padding: 10px 12px; color: #768998; border: 1px solid #e3edf3; border-radius: 11px; background: var(--reset-soft); list-style: none; font-size: 9px; font-weight: 700; }
    .password-requirements li { display: flex; align-items: center; gap: 6px; min-width: 0; }
    .password-requirements li span { width: 7px; height: 7px; flex: 0 0 auto; border: 1.5px solid #a9bac6; border-radius: 50%; background: #fff; }
    .password-requirements li.is-met { color: #237a59; }
    .password-requirements li.is-met span { border-color: #2baa78; background: #2baa78; box-shadow: 0 0 0 3px rgba(43,170,120,.1); }

    .reset-submit { width: 100%; min-height: 51px; display: flex; align-items: center; justify-content: center; gap: 11px; margin-top: 3px; padding: 11px 20px; color: #fff; font-size: 12px; font-weight: 850; border: 0; border-radius: 12px; background: linear-gradient(135deg, #0877ca, #075da7); box-shadow: 0 13px 25px rgba(8,119,202,.24); cursor: pointer; transition: transform .2s ease, box-shadow .2s ease, filter .2s ease, opacity .2s ease; }
    .reset-submit:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 16px 30px rgba(8,119,202,.3); filter: saturate(1.08); }
    .reset-submit:focus-visible { outline: 3px solid rgba(8,119,202,.22); outline-offset: 3px; }
    .reset-submit:disabled { cursor: not-allowed; opacity: .52; box-shadow: none; }
    .reset-submit svg { width: 18px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .reset-spinner { width: 17px; height: 17px; border: 2px solid rgba(255,255,255,.42); border-top-color: #fff; border-radius: 50%; animation: reset-spin .7s linear infinite; }
    @keyframes reset-spin { to { transform: rotate(360deg); } }
    .reset-secure-note { display: flex; align-items: center; justify-content: center; gap: 7px; margin: 11px 0 0; color: #8494a1; font-size: 9px; font-weight: 650; text-align: center; }
    .reset-secure-note svg { width: 13px; flex: 0 0 auto; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .reset-footer { width: min(500px, 100%); margin: 15px auto 0; color: #8a9ba8; font-size: 9px; font-weight: 650; text-align: center; }

    @media (max-width: 1050px) {
        .reset-layout { grid-template-columns: minmax(340px, .86fr) minmax(420px, 1.14fr); }
        .reset-showcase { padding: 42px 36px; }
        .reset-showcase-copy h1 { font-size: clamp(38px, 5vw, 52px); }
        .reset-brand-product { display: none; }
    }

    @media (max-width: 820px) {
        .reset-page { place-items: start center; padding: 18px; overflow: visible; }
        .reset-layout { min-height: calc(100svh - 36px); grid-template-columns: 1fr; }
        .reset-showcase { min-height: auto; display: block; padding: 24px 28px; }
        .reset-brand { justify-content: center; }
        .reset-brand-mark { width: 150px; min-height: 48px; padding: 7px 12px; }
        .reset-showcase-copy { margin: 18px auto 0; text-align: center; }
        .reset-eyebrow, .reset-showcase-copy > p, .reset-steps, .reset-showcase-footer { display: none; }
        .reset-showcase-copy h1 { font-size: 28px; line-height: 1.08; letter-spacing: -.035em; }
        .reset-showcase-copy h1 br { display: none; }
        .reset-workspace { padding: 30px clamp(26px, 7vw, 64px) 22px; }
        .reset-card, .reset-footer { width: min(520px, 100%); }
    }

    @media (max-width: 540px) {
        .reset-page { padding: 0; background: #fff; }
        .reset-ambient { display: none; }
        .reset-layout { min-height: 100svh; border: 0; border-radius: 0; box-shadow: none; }
        .reset-showcase { display: none; }
        .reset-workspace { justify-content: flex-start; padding: max(22px, env(safe-area-inset-top)) 20px max(18px, env(safe-area-inset-bottom)); }
        .reset-mobile-brand { display: flex; align-items: center; justify-content: space-between; width: 100%; margin-bottom: clamp(22px, 4.5vh, 38px); }
        .reset-mobile-brand img { width: 142px; height: auto; }
        .reset-mobile-brand span { padding: 6px 9px; color: var(--reset-blue); font-size: 10px; font-weight: 900; letter-spacing: .12em; border: 1px solid #cbe6f5; border-radius: 8px; background: #f1faff; }
        .reset-card { margin: 0 auto; }
        .reset-back { margin-bottom: 14px; }
        .reset-icon { width: 44px; height: 44px; margin-bottom: 11px; border-radius: 13px; }
        .reset-icon svg { width: 23px; }
        .reset-heading h2 { font-size: 28px; }
        .reset-input-shell input { height: 48px; font-size: 16px; }
        .reset-footer { margin-top: auto; padding-top: 18px; }
    }

    @media (max-width: 370px) {
        .reset-workspace { padding-left: 15px; padding-right: 15px; }
        .reset-mobile-brand { margin-bottom: 20px; }
        .password-requirements { gap: 5px 8px; padding-left: 9px; padding-right: 9px; font-size: 8px; }
        .reset-email span { display: none; }
    }

    @media (min-width: 821px) and (max-height: 760px) {
        .reset-page { padding: 12px; }
        .reset-layout { min-height: calc(100svh - 24px); }
        .reset-showcase { gap: 18px; padding: 28px 38px; }
        .reset-showcase-copy h1 { font-size: 40px; }
        .reset-showcase-copy > p { margin: 14px 0 16px; line-height: 1.5; }
        .reset-steps { grid-template-columns: repeat(3, minmax(0, 1fr)); max-width: 560px; }
        .reset-step { grid-template-columns: 30px 1fr; padding: 7px; }
        .reset-step-number { width: 29px; height: 29px; }
        .reset-step small { display: none; }
        .reset-workspace { padding-top: 16px; padding-bottom: 13px; overflow-y: auto; }
        .reset-back { margin-bottom: 10px; }
        .reset-icon { width: 40px; height: 40px; margin-bottom: 8px; }
        .reset-heading h2 { font-size: 30px; }
        #resetPasswordForm { margin-top: 12px; }
        .reset-field { margin-bottom: 9px; }
        .reset-input-shell input { height: 45px; }
        .password-requirements { margin-bottom: 9px; padding-top: 8px; padding-bottom: 8px; }
        .reset-submit { min-height: 46px; }
        .reset-footer { margin-top: 9px; }
    }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after { scroll-behavior: auto !important; animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('resetPasswordForm');
    const password = document.getElementById('password');
    const confirmation = document.getElementById('password-confirm');
    const passwordField = document.getElementById('passwordField');
    const confirmField = document.getElementById('confirmField');
    const passwordError = document.getElementById('passwordError');
    const confirmError = document.getElementById('confirmError');
    const strength = document.getElementById('passwordStrength');
    const strengthBar = document.getElementById('strengthBar');
    const strengthLabel = document.getElementById('strengthLabel');
    const submitButton = document.getElementById('resetButton');
    let isSubmitting = false;

    const requirementTests = {
        length: function (value) { return value.length >= 8; },
        uppercase: function (value) { return /[A-Z]/.test(value); },
        lowercase: function (value) { return /[a-z]/.test(value); },
        number: function (value) { return /[0-9]/.test(value); },
        special: function (value) { return /[@$!%*?&]/.test(value); }
    };

    function evaluatePassword(value) {
        const results = {};
        let score = 0;

        Object.keys(requirementTests).forEach(function (key) {
            results[key] = requirementTests[key](value);
            if (results[key]) score += 1;
            const item = document.getElementById('req-' + key);
            if (item) item.classList.toggle('is-met', results[key]);
        });

        strength.classList.remove('is-weak', 'is-medium', 'is-strong');
        if (!value) {
            strengthLabel.textContent = 'Not set';
            strengthBar.style.width = '0';
        } else if (score < 3) {
            strength.classList.add('is-weak');
            strengthLabel.textContent = 'Weak';
            strengthBar.style.width = '';
        } else if (score < 5) {
            strength.classList.add('is-medium');
            strengthLabel.textContent = 'Almost there';
            strengthBar.style.width = '';
        } else {
            strength.classList.add('is-strong');
            strengthLabel.textContent = 'Strong';
            strengthBar.style.width = '';
        }

        return score === 5;
    }

    function updateFormState() {
        const isStrong = evaluatePassword(password.value);
        const hasConfirmation = confirmation.value.length > 0;
        const matches = hasConfirmation && password.value === confirmation.value;

        passwordField.classList.toggle('is-valid', isStrong);
        if (password.value && !isStrong) {
            passwordField.classList.add('has-error');
            password.setAttribute('aria-invalid', 'true');
            passwordError.textContent = 'Meet all password requirements to continue.';
        } else {
            passwordField.classList.remove('has-error');
            password.setAttribute('aria-invalid', 'false');
            passwordError.textContent = '';
        }

        confirmField.classList.toggle('is-valid', matches);
        confirmField.classList.toggle('has-error', hasConfirmation && !matches);
        confirmation.setAttribute('aria-invalid', hasConfirmation && !matches ? 'true' : 'false');
        confirmError.textContent = hasConfirmation && !matches ? 'Passwords do not match.' : '';

        submitButton.disabled = !isStrong || !matches || isSubmitting;
        return isStrong && matches;
    }

    document.querySelectorAll('[data-toggle-password]').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            const input = document.getElementById(toggle.dataset.togglePassword);
            const willShow = input.type === 'password';
            input.type = willShow ? 'text' : 'password';
            toggle.classList.toggle('is-visible', willShow);
            toggle.setAttribute('aria-pressed', willShow ? 'true' : 'false');
            toggle.setAttribute('aria-label', willShow ? 'Hide password' : 'Show password');
        });
    });

    password.addEventListener('input', updateFormState);
    confirmation.addEventListener('input', updateFormState);

    form.addEventListener('submit', function (event) {
        if (isSubmitting) {
            event.preventDefault();
            return;
        }

        if (!updateFormState() || !form.checkValidity()) {
            event.preventDefault();
            if (!password.value) {
                passwordField.classList.add('has-error');
                passwordError.textContent = 'Enter a new password.';
                password.focus();
            } else if (!confirmation.value) {
                confirmField.classList.add('has-error');
                confirmError.textContent = 'Confirm your new password.';
                confirmation.focus();
            }
            return;
        }

        isSubmitting = true;
        submitButton.disabled = true;
        submitButton.setAttribute('aria-busy', 'true');
        submitButton.innerHTML = '<span class="reset-spinner" aria-hidden="true"></span><span>Updating password...</span>';
    });

    updateFormState();
});
</script>
@endsection
