@extends('layouts.app')

@section('title', 'Reset password | Gaz Lite DMS')

@section('content')
<main class="recovery-page">
    <span class="recovery-ambient recovery-ambient-one" aria-hidden="true"></span>
    <span class="recovery-ambient recovery-ambient-two" aria-hidden="true"></span>

    <div class="recovery-layout">
        <aside class="recovery-showcase" aria-label="Gaz Lite password recovery">
            <div class="recovery-grid" aria-hidden="true"></div>

            <div class="recovery-brand">
                <span class="recovery-brand-mark">
                    <img src="{{ asset('images/gazlite.png') }}" alt="Gaz Lite">
                </span>
                <span class="recovery-brand-product">Distributor Management System</span>
            </div>

            <div class="recovery-showcase-copy">
                <span class="recovery-eyebrow">
                    <span class="recovery-eyebrow-dot"></span>
                    Secure account recovery
                </span>
                <h1>Back to work.<br><span>Safely and quickly.</span></h1>
                <p>Recover access to your Gaz Lite workspace through a secure, guided verification process.</p>

                <ol class="recovery-steps" aria-label="Password recovery steps">
                    <li class="recovery-step is-active">
                        <span class="recovery-step-number">1</span>
                        <span><strong>Identify your account</strong><small>Enter your registered email address.</small></span>
                    </li>
                    <li class="recovery-step">
                        <span class="recovery-step-number">2</span>
                        <span><strong>Verify your identity</strong><small>Use the one-time code sent to you.</small></span>
                    </li>
                    <li class="recovery-step">
                        <span class="recovery-step-number">3</span>
                        <span><strong>Create a new password</strong><small>Choose a strong password and sign in.</small></span>
                    </li>
                </ol>
            </div>

            <div class="recovery-showcase-footer">
                <span class="recovery-status-pulse" aria-hidden="true"></span>
                Protected recovery for authorized Gaz Lite users
            </div>
        </aside>

        <section class="recovery-workspace">
            <div class="recovery-mobile-brand">
                <img src="{{ asset('images/gazlite.png') }}" alt="Gaz Lite">
                <span>DMS</span>
            </div>

            <div class="recovery-card">
                <a class="recovery-back" href="{{ route('login', ['direct' => 'true']) }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5m6 6-6-6 6-6"/></svg>
                    Back to sign in
                </a>

                <div class="recovery-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="M9 12a3 3 0 1 1 5.3 1.9L15 16H9l.7-2.1A3 3 0 0 1 9 12Z"/></svg>
                </div>

                <div class="recovery-heading">
                    <span class="recovery-kicker">Account recovery</span>
                    <h2>Forgot your password?</h2>
                    <p>Enter the email address linked to your account. We will send you a secure one-time code to continue.</p>
                </div>

                @if (session('status'))
                    <div class="recovery-notice recovery-notice-success" role="status">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->has('email'))
                    <div class="recovery-notice recovery-notice-error" role="alert">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 9v4m0 4h.01"/><path d="M10.3 3.6 2.4 17.2A2 2 0 0 0 4.1 20h15.8a2 2 0 0 0 1.7-2.8L13.7 3.6a2 2 0 0 0-3.4 0Z"/></svg>
                        <span>{{ $errors->first('email') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" id="recoveryForm" novalidate>
                    @csrf

                    <div class="recovery-field{{ $errors->has('email') ? ' has-error' : '' }}">
                        <label for="email">Email address</label>
                        <div class="recovery-input-shell">
                            <svg class="recovery-input-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16v16H4z"/><path d="m4 6 8 7 8-7"/></svg>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="name@example.com"
                                autocomplete="email"
                                inputmode="email"
                                aria-describedby="emailHelp emailError"
                                aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                                required
                                autofocus
                            >
                        </div>
                        <small class="recovery-field-error" id="emailError">{{ $errors->first('email') }}</small>
                        <small class="recovery-field-help" id="emailHelp">Use the same email address registered to your account.</small>
                    </div>

                    <button class="recovery-submit" type="submit" id="recoverySubmit">
                        <span class="recovery-button-label">Send verification code</span>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                    </button>

                    <p class="recovery-secure-note">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                        Your recovery request is encrypted and securely transmitted.
                    </p>
                </form>
            </div>

            <footer class="recovery-footer">&copy; {{ date('Y') }} Pascal Resources Energy, Inc.</footer>
        </section>
    </div>
</main>

<style>
    :root {
        --recovery-navy: #07345f;
        --recovery-blue: #0877ca;
        --recovery-sky: #28b8ec;
        --recovery-red: #ee5058;
        --recovery-ink: #102a43;
        --recovery-muted: #64778a;
        --recovery-line: #dbe6ee;
        --recovery-soft: #f5f9fc;
    }

    *, *::before, *::after { box-sizing: border-box; }
    html, body { min-height: 100%; }
    body { margin: 0; overflow-x: hidden; color: var(--recovery-ink); background: #edf6fb; font-family: Inter, Nunito, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
    button, input { font: inherit; }

    .recovery-page {
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

    .recovery-ambient { position: absolute; z-index: -1; border-radius: 999px; filter: blur(2px); pointer-events: none; }
    .recovery-ambient-one { width: 280px; height: 280px; top: -130px; right: 10%; background: rgba(238, 80, 88, .08); }
    .recovery-ambient-two { width: 360px; height: 360px; bottom: -210px; left: 4%; background: rgba(8, 119, 202, .1); }

    .recovery-layout {
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

    .recovery-showcase {
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

    .recovery-showcase::after { content: ""; position: absolute; width: 360px; height: 360px; right: -200px; bottom: -180px; border: 46px solid rgba(255, 255, 255, .07); border-radius: 50%; }
    .recovery-grid { position: absolute; inset: 0; opacity: .13; background-image: linear-gradient(rgba(255,255,255,.18) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.18) 1px, transparent 1px); background-size: 44px 44px; mask-image: linear-gradient(to bottom right, #000, transparent 72%); }
    .recovery-brand, .recovery-showcase-copy, .recovery-showcase-footer { position: relative; z-index: 1; }
    .recovery-brand { display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
    .recovery-brand-mark { width: 174px; min-height: 58px; display: grid; place-items: center; padding: 9px 14px; background: #fff; border-radius: 14px; box-shadow: 0 14px 34px rgba(0, 25, 55, .2); }
    .recovery-brand-mark img { display: block; width: 100%; height: auto; }
    .recovery-brand-product { max-width: 150px; color: rgba(255,255,255,.8); font-size: 11px; font-weight: 800; line-height: 1.45; letter-spacing: .08em; text-transform: uppercase; }
    .recovery-showcase-copy { max-width: 590px; }
    .recovery-eyebrow { display: inline-flex; align-items: center; gap: 9px; margin-bottom: 20px; color: #c8f2ff; font-size: 11px; font-weight: 800; letter-spacing: .13em; text-transform: uppercase; }
    .recovery-eyebrow-dot { width: 8px; height: 8px; background: #55d7ff; border-radius: 50%; box-shadow: 0 0 0 6px rgba(85,215,255,.13); }
    .recovery-showcase-copy h1 { margin: 0; color: #fff; font-size: clamp(42px, 4.5vw, 64px); font-weight: 850; line-height: .98; letter-spacing: -.055em; }
    .recovery-showcase-copy h1 span { color: #75dcff; }
    .recovery-showcase-copy > p { max-width: 560px; margin: 24px 0 28px; color: rgba(255,255,255,.78); font-size: 15px; line-height: 1.75; }

    .recovery-steps { display: grid; gap: 10px; margin: 0; padding: 0; list-style: none; }
    .recovery-step { display: grid; grid-template-columns: 38px 1fr; align-items: center; gap: 12px; max-width: 390px; padding: 11px 13px; border: 1px solid rgba(255,255,255,.12); border-radius: 14px; background: rgba(255,255,255,.07); backdrop-filter: blur(10px); }
    .recovery-step.is-active { border-color: rgba(117,220,255,.46); background: rgba(117,220,255,.13); }
    .recovery-step-number { width: 36px; height: 36px; display: grid; place-items: center; color: #bdefff; font-size: 12px; font-weight: 900; border: 1px solid rgba(156,231,255,.35); border-radius: 11px; background: rgba(255,255,255,.08); }
    .recovery-step.is-active .recovery-step-number { color: #075798; border-color: transparent; background: #8ce4ff; }
    .recovery-step strong, .recovery-step small { display: block; }
    .recovery-step strong { color: #fff; font-size: 13px; font-weight: 800; }
    .recovery-step small { margin-top: 2px; color: rgba(255,255,255,.65); font-size: 11px; }
    .recovery-showcase-footer { display: flex; align-items: center; gap: 10px; color: rgba(255,255,255,.72); font-size: 11px; font-weight: 700; letter-spacing: .02em; }
    .recovery-status-pulse { width: 8px; height: 8px; flex: 0 0 auto; background: #63e9ae; border-radius: 50%; box-shadow: 0 0 0 6px rgba(99,233,174,.13); }

    .recovery-workspace { min-width: 0; display: flex; flex-direction: column; justify-content: center; padding: clamp(34px, 5vw, 70px); background: linear-gradient(180deg, #fff 0%, #fbfdff 100%); }
    .recovery-mobile-brand { display: none; }
    .recovery-card { width: min(490px, 100%); margin: auto; }
    .recovery-back { display: inline-flex; align-items: center; gap: 7px; margin-bottom: 34px; color: #5f7486; font-size: 11px; font-weight: 800; text-decoration: none; }
    .recovery-back:hover { color: var(--recovery-blue); }
    .recovery-back:focus-visible { outline: 3px solid rgba(8,119,202,.18); outline-offset: 4px; border-radius: 5px; }
    .recovery-back svg { width: 17px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .recovery-icon { width: 54px; height: 54px; display: grid; place-items: center; margin-bottom: 20px; color: var(--recovery-blue); border: 1px solid #c9e8f8; border-radius: 16px; background: linear-gradient(145deg, #eef9ff, #e1f4fe); box-shadow: 0 10px 24px rgba(8,119,202,.12); }
    .recovery-icon svg { width: 28px; fill: none; stroke: currentColor; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; }
    .recovery-kicker { display: block; margin-bottom: 9px; color: var(--recovery-blue); font-size: 11px; font-weight: 850; letter-spacing: .14em; text-transform: uppercase; }
    .recovery-heading h2 { margin: 0; color: var(--recovery-ink); font-size: clamp(30px, 3vw, 40px); font-weight: 850; line-height: 1.12; letter-spacing: -.035em; }
    .recovery-heading p { max-width: 46ch; margin: 12px 0 0; color: var(--recovery-muted); font-size: 14px; line-height: 1.65; }

    .recovery-notice { display: grid; grid-template-columns: 21px 1fr; align-items: start; gap: 10px; margin-top: 20px; padding: 12px 14px; border: 1px solid; border-radius: 12px; font-size: 12px; font-weight: 700; line-height: 1.5; }
    .recovery-notice svg { width: 20px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .recovery-notice-success { color: #176b47; border-color: #bcebd6; background: #effbf5; }
    .recovery-notice-error { color: #a52c35; border-color: #ffd0d4; background: #fff5f6; }

    #recoveryForm { margin-top: 25px; }
    .recovery-field { margin-bottom: 20px; }
    .recovery-field > label { display: block; margin: 0 0 9px; color: #314b61; font-size: 12px; font-weight: 800; }
    .recovery-input-shell { position: relative; }
    .recovery-input-icon { position: absolute; top: 50%; left: 15px; width: 19px; transform: translateY(-50%); color: #7890a3; fill: none; stroke: currentColor; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; pointer-events: none; }
    .recovery-input-shell input { width: 100%; height: 52px; padding: 0 18px 0 45px; color: var(--recovery-ink); font-size: 14px; font-weight: 650; border: 1.5px solid var(--recovery-line); border-radius: 13px; outline: none; background: #fff; transition: border-color .2s ease, box-shadow .2s ease, background .2s ease; }
    .recovery-input-shell input::placeholder { color: #a1b0bc; font-weight: 500; }
    .recovery-input-shell input:hover { border-color: #bad1df; }
    .recovery-input-shell input:focus { border-color: var(--recovery-blue); box-shadow: 0 0 0 4px rgba(8,119,202,.1); }
    .recovery-field.has-error .recovery-input-shell input { border-color: var(--recovery-red); background: #fffafa; }
    .recovery-field-error { display: block; margin-top: 6px; color: #b52d37; font-size: 11px; font-weight: 700; line-height: 1.35; }
    .recovery-field-error:empty { display: none; }
    .recovery-field-help { display: block; margin-top: 7px; color: #8798a5; font-size: 10px; font-weight: 650; line-height: 1.45; }

    .recovery-submit { width: 100%; min-height: 53px; display: flex; align-items: center; justify-content: center; gap: 11px; padding: 12px 20px; color: #fff; font-size: 13px; font-weight: 850; border: 0; border-radius: 13px; background: linear-gradient(135deg, #0877ca, #075da7); box-shadow: 0 13px 25px rgba(8,119,202,.24); cursor: pointer; transition: transform .2s ease, box-shadow .2s ease, filter .2s ease; }
    .recovery-submit:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 16px 30px rgba(8,119,202,.3); filter: saturate(1.08); }
    .recovery-submit:focus-visible { outline: 3px solid rgba(8,119,202,.22); outline-offset: 3px; }
    .recovery-submit:disabled { cursor: wait; opacity: .72; box-shadow: none; }
    .recovery-submit svg { width: 19px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .recovery-spinner { width: 17px; height: 17px; border: 2px solid rgba(255,255,255,.42); border-top-color: #fff; border-radius: 50%; animation: recovery-spin .7s linear infinite; }
    @keyframes recovery-spin { to { transform: rotate(360deg); } }
    .recovery-secure-note { display: flex; align-items: center; justify-content: center; gap: 7px; margin: 15px 0 0; color: #8494a1; font-size: 10px; font-weight: 650; text-align: center; }
    .recovery-secure-note svg { width: 14px; flex: 0 0 auto; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .recovery-footer { width: min(490px, 100%); margin: 26px auto 0; color: #8a9ba8; font-size: 10px; font-weight: 650; text-align: center; }

    @media (max-width: 1050px) {
        .recovery-layout { grid-template-columns: minmax(340px, .86fr) minmax(420px, 1.14fr); }
        .recovery-showcase { padding: 42px 36px; }
        .recovery-showcase-copy h1 { font-size: clamp(38px, 5vw, 52px); }
        .recovery-brand-product { display: none; }
    }

    @media (max-width: 820px) {
        .recovery-page { place-items: start center; padding: 18px; overflow: visible; }
        .recovery-layout { min-height: calc(100svh - 36px); grid-template-columns: 1fr; }
        .recovery-showcase { min-height: auto; display: block; padding: 24px 28px; }
        .recovery-brand { justify-content: center; }
        .recovery-brand-mark { width: 150px; min-height: 48px; padding: 7px 12px; }
        .recovery-showcase-copy { margin: 18px auto 0; text-align: center; }
        .recovery-eyebrow, .recovery-showcase-copy > p, .recovery-steps, .recovery-showcase-footer { display: none; }
        .recovery-showcase-copy h1 { font-size: 28px; line-height: 1.08; letter-spacing: -.035em; }
        .recovery-showcase-copy h1 br { display: none; }
        .recovery-workspace { padding: 36px clamp(26px, 7vw, 64px) 24px; }
        .recovery-card, .recovery-footer { width: min(520px, 100%); }
        .recovery-back { margin-bottom: 27px; }
    }

    @media (max-width: 540px) {
        .recovery-page { padding: 0; background: #fff; }
        .recovery-ambient { display: none; }
        .recovery-layout { min-height: 100svh; border: 0; border-radius: 0; box-shadow: none; }
        .recovery-showcase { display: none; }
        .recovery-workspace { justify-content: flex-start; padding: max(24px, env(safe-area-inset-top)) 20px max(20px, env(safe-area-inset-bottom)); }
        .recovery-mobile-brand { display: flex; align-items: center; justify-content: space-between; width: 100%; margin-bottom: clamp(30px, 9vh, 62px); }
        .recovery-mobile-brand img { width: 142px; height: auto; }
        .recovery-mobile-brand span { padding: 6px 9px; color: var(--recovery-blue); font-size: 10px; font-weight: 900; letter-spacing: .12em; border: 1px solid #cbe6f5; border-radius: 8px; background: #f1faff; }
        .recovery-card { margin: 0 auto; }
        .recovery-back { margin-bottom: 25px; }
        .recovery-icon { width: 48px; height: 48px; margin-bottom: 17px; border-radius: 14px; }
        .recovery-icon svg { width: 25px; }
        .recovery-heading h2 { font-size: 30px; }
        .recovery-heading p { max-width: 36ch; }
        .recovery-input-shell input { height: 50px; font-size: 16px; }
        .recovery-footer { margin-top: auto; padding-top: 26px; }
    }

    @media (max-width: 370px) {
        .recovery-workspace { padding-left: 15px; padding-right: 15px; }
        .recovery-mobile-brand { margin-bottom: 28px; }
        .recovery-back { margin-bottom: 20px; }
    }

    @media (min-width: 821px) and (max-height: 720px) {
        .recovery-page { padding: 14px; }
        .recovery-layout { min-height: calc(100svh - 28px); }
        .recovery-showcase { gap: 20px; padding: 30px 38px; }
        .recovery-showcase-copy h1 { font-size: 40px; }
        .recovery-showcase-copy > p { margin: 16px 0 18px; line-height: 1.55; }
        .recovery-steps { grid-template-columns: repeat(3, minmax(0, 1fr)); max-width: 560px; }
        .recovery-step { grid-template-columns: 32px 1fr; padding: 8px; }
        .recovery-step-number { width: 31px; height: 31px; }
        .recovery-step small { display: none; }
        .recovery-workspace { padding-top: 25px; padding-bottom: 20px; overflow-y: auto; }
        .recovery-back { margin-bottom: 18px; }
        .recovery-icon { width: 46px; height: 46px; margin-bottom: 14px; }
        #recoveryForm { margin-top: 18px; }
        .recovery-footer { margin-top: 16px; }
    }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after { scroll-behavior: auto !important; animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; }
    }
</style>

<script>
    (function () {
        const form = document.getElementById('recoveryForm');
        const submitButton = document.getElementById('recoverySubmit');

        if (!form || !submitButton) return;

        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                form.classList.add('was-validated');

                const email = document.getElementById('email');
                const field = email ? email.closest('.recovery-field') : null;
                const error = document.getElementById('emailError');

                if (field) field.classList.add('has-error');
                if (email) {
                    email.setAttribute('aria-invalid', 'true');
                    email.focus();
                }
                if (error) error.textContent = 'Please enter a valid email address.';
                return;
            }

            submitButton.disabled = true;
            submitButton.setAttribute('aria-busy', 'true');
            submitButton.innerHTML = '<span class="recovery-spinner" aria-hidden="true"></span><span>Sending code...</span>';
        });

        const email = document.getElementById('email');
        if (email) {
            email.addEventListener('input', function () {
                if (!email.validity.valid) return;

                const field = email.closest('.recovery-field');
                const error = document.getElementById('emailError');
                if (field) field.classList.remove('has-error');
                email.setAttribute('aria-invalid', 'false');
                if (error) error.textContent = '';
            });
        }
    })();
</script>
@endsection
