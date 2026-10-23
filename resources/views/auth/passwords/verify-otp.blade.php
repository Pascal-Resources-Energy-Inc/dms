@extends('layouts.app')

@section('title', 'Verify code | Gaz Lite DMS')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<main class="otp-page">
    <span class="otp-ambient otp-ambient-one" aria-hidden="true"></span>
    <span class="otp-ambient otp-ambient-two" aria-hidden="true"></span>

    <div class="otp-layout">
        <aside class="otp-showcase" aria-label="Gaz Lite password recovery">
            <div class="otp-grid" aria-hidden="true"></div>

            <div class="otp-brand">
                <span class="otp-brand-mark">
                    <img src="{{ asset('images/gazlite.png') }}" alt="Gaz Lite">
                </span>
                <span class="otp-brand-product">Distributor Management System</span>
            </div>

            <div class="otp-showcase-copy">
                <span class="otp-eyebrow">
                    <span class="otp-eyebrow-dot"></span>
                    Secure account recovery
                </span>
                <h1>One quick check.<br><span>Then you’re back.</span></h1>
                <p>Confirm the verification code sent to your email to securely continue resetting your password.</p>

                <ol class="otp-steps" aria-label="Password recovery progress">
                    <li class="otp-step is-complete">
                        <span class="otp-step-number">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 12 4 4 8-8"/></svg>
                        </span>
                        <span><strong>Identify your account</strong><small>Your email has been confirmed.</small></span>
                    </li>
                    <li class="otp-step is-active" aria-current="step">
                        <span class="otp-step-number">2</span>
                        <span><strong>Verify your identity</strong><small>Enter the six-digit code.</small></span>
                    </li>
                    <li class="otp-step">
                        <span class="otp-step-number">3</span>
                        <span><strong>Create a new password</strong><small>Secure your account and sign in.</small></span>
                    </li>
                </ol>
            </div>

            <div class="otp-showcase-footer">
                <span class="otp-status-pulse" aria-hidden="true"></span>
                Protected recovery for authorized Gaz Lite users
            </div>
        </aside>

        <section class="otp-workspace">
            <div class="otp-mobile-brand">
                <img src="{{ asset('images/gazlite.png') }}" alt="Gaz Lite">
                <span>DMS</span>
            </div>

            <div class="otp-card">
                <a class="otp-back" href="{{ route('password.request') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5m6 6-6-6 6-6"/></svg>
                    Change email address
                </a>

                <div class="otp-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="3"/><path d="m4 7 8 6 8-6"/><path d="M16.5 3.5 18 2l1.5 1.5L18 5l-1.5-1.5Z"/></svg>
                </div>

                <div class="otp-heading">
                    <span class="otp-kicker">Step 2 of 3</span>
                    <h2>Check your email</h2>
                    <p>We sent a six-digit verification code to:</p>
                    <div class="otp-email" title="{{ $email }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16v16H4z"/><path d="m4 6 8 7 8-7"/></svg>
                        <strong>{{ $email }}</strong>
                    </div>
                </div>

                @if (session('success'))
                    <div class="otp-notice otp-notice-success" role="status">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="otp-notice otp-notice-error" role="alert">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 9v4m0 4h.01"/><path d="M10.3 3.6 2.4 17.2A2 2 0 0 0 4.1 20h15.8a2 2 0 0 0 1.7-2.8L13.7 3.6a2 2 0 0 0-3.4 0Z"/></svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.verify-otp') }}" id="otpForm">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <input type="hidden" name="otp" id="otpValue" required>

                    <fieldset class="otp-fieldset">
                        <legend>Enter verification code</legend>
                        <div class="otp-inputs" id="otpInputs" role="group" aria-label="Six-digit verification code">
                            @for ($index = 1; $index <= 6; $index++)
                                <input
                                    type="text"
                                    class="otp-digit"
                                    maxlength="1"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    autocomplete="{{ $index === 1 ? 'one-time-code' : 'off' }}"
                                    aria-label="Digit {{ $index }} of 6"
                                    data-index="{{ $index - 1 }}"
                                >
                            @endfor
                        </div>
                        <small class="otp-field-error" id="otpError" aria-live="polite"></small>
                        <small class="otp-field-help">The code expires shortly. Never share it with anyone.</small>
                    </fieldset>

                    <button class="otp-submit" type="submit" id="verifyButton" disabled>
                        <span class="otp-button-label">Verify and continue</span>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                    </button>
                </form>

                <div class="otp-resend">
                    <div>
                        <strong>Didn't receive the code?</strong>
                        <small>Check your spam folder or request a new code.</small>
                    </div>

                    <span class="otp-timer" id="timerDisplay" aria-live="polite">
                        Resend in <strong id="countdown">60</strong>s
                    </span>

                    <form method="POST" action="{{ route('password.email') }}" id="resendForm" hidden>
                        @csrf
                        <input type="hidden" name="email" value="{{ $email }}">
                        <button type="submit" class="otp-resend-button" id="resendButton">Resend code</button>
                    </form>
                </div>

                <p class="otp-secure-note">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                    Your verification is encrypted and securely transmitted.
                </p>
            </div>

            <footer class="otp-footer">&copy; {{ date('Y') }} Pascal Resources Energy, Inc.</footer>
        </section>
    </div>
</main>

<style>
    :root {
        --otp-navy: #07345f;
        --otp-blue: #0877ca;
        --otp-sky: #28b8ec;
        --otp-red: #ee5058;
        --otp-ink: #102a43;
        --otp-muted: #64778a;
        --otp-line: #dbe6ee;
        --otp-soft: #f5f9fc;
    }

    *, *::before, *::after { box-sizing: border-box; }
    html, body { min-height: 100%; }
    body { margin: 0; overflow-x: hidden; color: var(--otp-ink); background: #edf6fb; font-family: Inter, Nunito, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
    button, input { font: inherit; }

    .otp-page {
        position: relative;
        min-height: 100vh;
        min-height: 100svh;
        display: grid;
        place-items: center;
        padding: clamp(18px, 3.2vw, 52px);
        isolation: isolate;
        overflow: hidden;
        background: radial-gradient(circle at 8% 14%, rgba(40,184,236,.18), transparent 27%), radial-gradient(circle at 92% 88%, rgba(8,119,202,.14), transparent 25%), linear-gradient(145deg, #f8fcff 0%, #eaf5fb 52%, #f4fbff 100%);
    }

    .otp-ambient { position: absolute; z-index: -1; border-radius: 999px; filter: blur(2px); pointer-events: none; }
    .otp-ambient-one { width: 280px; height: 280px; top: -130px; right: 10%; background: rgba(238,80,88,.08); }
    .otp-ambient-two { width: 360px; height: 360px; bottom: -210px; left: 4%; background: rgba(8,119,202,.1); }

    .otp-layout {
        /* width: min(1240px, 100%); */
        min-height: min(780px, calc(100svh - clamp(36px, 6.4vw, 104px)));
        display: grid;
        grid-template-columns: minmax(390px, 1.03fr) minmax(440px, .97fr);
        overflow: hidden;
        background: #fff;
        border: 1px solid rgba(179,205,221,.64);
        border-radius: 30px;
        box-shadow: 0 35px 90px rgba(26,75,110,.18), 0 8px 28px rgba(26,75,110,.08);
    }

    .otp-showcase {
        position: relative;
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 44px;
        padding: clamp(34px, 4.6vw, 70px);
        overflow: hidden;
        color: #fff;
        background: radial-gradient(circle at 88% 8%, rgba(56,195,238,.35), transparent 29%), radial-gradient(circle at 8% 90%, rgba(10,154,218,.3), transparent 30%), linear-gradient(145deg, #052f59 0%, #075798 54%, #0780c9 100%);
    }

    .otp-showcase::after { content: ""; position: absolute; width: 360px; height: 360px; right: -200px; bottom: -180px; border: 46px solid rgba(255,255,255,.07); border-radius: 50%; }
    .otp-grid { position: absolute; inset: 0; opacity: .13; background-image: linear-gradient(rgba(255,255,255,.18) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.18) 1px, transparent 1px); background-size: 44px 44px; mask-image: linear-gradient(to bottom right, #000, transparent 72%); }
    .otp-brand, .otp-showcase-copy, .otp-showcase-footer { position: relative; z-index: 1; }
    .otp-brand { display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
    .otp-brand-mark { width: 174px; min-height: 58px; display: grid; place-items: center; padding: 9px 14px; background: #fff; border-radius: 14px; box-shadow: 0 14px 34px rgba(0,25,55,.2); }
    .otp-brand-mark img { display: block; width: 100%; height: auto; }
    .otp-brand-product { max-width: 150px; color: rgba(255,255,255,.8); font-size: 11px; font-weight: 800; line-height: 1.45; letter-spacing: .08em; text-transform: uppercase; }
    .otp-showcase-copy { max-width: 590px; }
    .otp-eyebrow { display: inline-flex; align-items: center; gap: 9px; margin-bottom: 20px; color: #c8f2ff; font-size: 11px; font-weight: 800; letter-spacing: .13em; text-transform: uppercase; }
    .otp-eyebrow-dot { width: 8px; height: 8px; background: #55d7ff; border-radius: 50%; box-shadow: 0 0 0 6px rgba(85,215,255,.13); }
    .otp-showcase-copy h1 { margin: 0; color: #fff; font-size: clamp(42px, 4.5vw, 64px); font-weight: 850; line-height: .98; letter-spacing: -.055em; }
    .otp-showcase-copy h1 span { color: #75dcff; }
    .otp-showcase-copy > p { max-width: 560px; margin: 24px 0 28px; color: rgba(255,255,255,.78); font-size: 15px; line-height: 1.75; }

    .otp-steps { display: grid; gap: 10px; margin: 0; padding: 0; list-style: none; }
    .otp-step { display: grid; grid-template-columns: 38px 1fr; align-items: center; gap: 12px; max-width: 390px; padding: 11px 13px; border: 1px solid rgba(255,255,255,.12); border-radius: 14px; background: rgba(255,255,255,.07); backdrop-filter: blur(10px); }
    .otp-step.is-active { border-color: rgba(117,220,255,.46); background: rgba(117,220,255,.13); }
    .otp-step-number { width: 36px; height: 36px; display: grid; place-items: center; color: #bdefff; font-size: 12px; font-weight: 900; border: 1px solid rgba(156,231,255,.35); border-radius: 11px; background: rgba(255,255,255,.08); }
    .otp-step-number svg { width: 18px; fill: none; stroke: currentColor; stroke-width: 2.4; stroke-linecap: round; stroke-linejoin: round; }
    .otp-step.is-complete .otp-step-number { color: #146848; border-color: transparent; background: #75e5b3; }
    .otp-step.is-active .otp-step-number { color: #075798; border-color: transparent; background: #8ce4ff; }
    .otp-step strong, .otp-step small { display: block; }
    .otp-step strong { color: #fff; font-size: 13px; font-weight: 800; }
    .otp-step small { margin-top: 2px; color: rgba(255,255,255,.65); font-size: 11px; }
    .otp-showcase-footer { display: flex; align-items: center; gap: 10px; color: rgba(255,255,255,.72); font-size: 11px; font-weight: 700; letter-spacing: .02em; }
    .otp-status-pulse { width: 8px; height: 8px; flex: 0 0 auto; background: #63e9ae; border-radius: 50%; box-shadow: 0 0 0 6px rgba(99,233,174,.13); }

    .otp-workspace { min-width: 0; display: flex; flex-direction: column; justify-content: center; padding: clamp(30px, 4vw, 58px); background: linear-gradient(180deg, #fff 0%, #fbfdff 100%); }
    .otp-mobile-brand { display: none; }
    .otp-card { width: min(490px, 100%); margin: auto; }
    .otp-back { display: inline-flex; align-items: center; gap: 7px; margin-bottom: 24px; color: #5f7486; font-size: 11px; font-weight: 800; text-decoration: none; }
    .otp-back:hover { color: var(--otp-blue); }
    .otp-back:focus-visible { outline: 3px solid rgba(8,119,202,.18); outline-offset: 4px; border-radius: 5px; }
    .otp-back svg { width: 17px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .otp-icon { width: 54px; height: 54px; display: grid; place-items: center; margin-bottom: 17px; color: var(--otp-blue); border: 1px solid #c9e8f8; border-radius: 16px; background: linear-gradient(145deg, #eef9ff, #e1f4fe); box-shadow: 0 10px 24px rgba(8,119,202,.12); }
    .otp-icon svg { width: 28px; fill: none; stroke: currentColor; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; }
    .otp-kicker { display: block; margin-bottom: 8px; color: var(--otp-blue); font-size: 11px; font-weight: 850; letter-spacing: .14em; text-transform: uppercase; }
    .otp-heading h2 { margin: 0; color: var(--otp-ink); font-size: clamp(30px, 3vw, 40px); font-weight: 850; line-height: 1.12; letter-spacing: -.035em; }
    .otp-heading > p { margin: 10px 0 8px; color: var(--otp-muted); font-size: 14px; line-height: 1.55; }
    .otp-email { max-width: 100%; display: inline-flex; align-items: center; gap: 7px; padding: 7px 10px; color: #27566f; border: 1px solid #d4eaf6; border-radius: 9px; background: #f2faff; font-size: 11px; }
    .otp-email svg { width: 15px; flex: 0 0 auto; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .otp-email strong { max-width: 340px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .otp-notice { display: grid; grid-template-columns: 21px 1fr; align-items: start; gap: 10px; margin-top: 17px; padding: 11px 13px; border: 1px solid; border-radius: 12px; font-size: 12px; font-weight: 700; line-height: 1.5; }
    .otp-notice svg { width: 20px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .otp-notice-success { color: #176b47; border-color: #bcebd6; background: #effbf5; }
    .otp-notice-error { color: #a52c35; border-color: #ffd0d4; background: #fff5f6; }

    #otpForm { margin-top: 21px; }
    .otp-fieldset { min-width: 0; margin: 0 0 18px; padding: 0; border: 0; }
    .otp-fieldset legend { margin: 0 0 10px; padding: 0; color: #314b61; font-size: 12px; font-weight: 800; }
    .otp-inputs { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 9px; }
    .otp-digit { width: 100%; min-width: 0; aspect-ratio: 1 / 1; max-height: 58px; color: var(--otp-ink); background: #fff; border: 1.5px solid var(--otp-line); border-radius: 13px; outline: none; font-size: 21px; font-weight: 850; text-align: center; caret-color: var(--otp-blue); transition: border-color .2s ease, box-shadow .2s ease, background .2s ease, transform .2s ease; }
    .otp-digit:hover { border-color: #bad1df; }
    .otp-digit:focus { border-color: var(--otp-blue); background: #fafdff; box-shadow: 0 0 0 4px rgba(8,119,202,.1); transform: translateY(-1px); }
    .otp-digit.is-filled { color: var(--otp-blue); border-color: #7fc5ec; background: #eef9ff; }
    .otp-digit.has-error { border-color: var(--otp-red); background: #fffafa; animation: otp-shake .25s ease; }
    @keyframes otp-shake { 0%, 100% { transform: translateX(0); } 35% { transform: translateX(-3px); } 70% { transform: translateX(3px); } }
    .otp-field-error { display: block; margin-top: 7px; color: #b52d37; font-size: 11px; font-weight: 700; line-height: 1.35; }
    .otp-field-error:empty { display: none; }
    .otp-field-help { display: block; margin-top: 7px; color: #8798a5; font-size: 10px; font-weight: 650; line-height: 1.45; }

    .otp-submit { width: 100%; min-height: 53px; display: flex; align-items: center; justify-content: center; gap: 11px; padding: 12px 20px; color: #fff; font-size: 13px; font-weight: 850; border: 0; border-radius: 13px; background: linear-gradient(135deg, #0877ca, #075da7); box-shadow: 0 13px 25px rgba(8,119,202,.24); cursor: pointer; transition: transform .2s ease, box-shadow .2s ease, filter .2s ease, opacity .2s ease; }
    .otp-submit:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 16px 30px rgba(8,119,202,.3); filter: saturate(1.08); }
    .otp-submit:focus-visible { outline: 3px solid rgba(8,119,202,.22); outline-offset: 3px; }
    .otp-submit:disabled { cursor: not-allowed; opacity: .52; box-shadow: none; }
    .otp-submit svg { width: 19px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .otp-spinner { width: 17px; height: 17px; border: 2px solid rgba(255,255,255,.42); border-top-color: #fff; border-radius: 50%; animation: otp-spin .7s linear infinite; }
    @keyframes otp-spin { to { transform: rotate(360deg); } }

    .otp-resend { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: 12px; margin-top: 16px; padding: 12px 13px; border: 1px solid #e3edf3; border-radius: 12px; background: var(--otp-soft); }
    .otp-resend strong, .otp-resend small { display: block; }
    .otp-resend > div > strong { color: #385369; font-size: 11px; font-weight: 850; }
    .otp-resend > div > small { margin-top: 2px; color: #8394a1; font-size: 9px; line-height: 1.4; }
    .otp-timer { color: #708698; font-size: 10px; font-weight: 750; white-space: nowrap; }
    .otp-timer strong { display: inline; color: var(--otp-blue); }
    #resendForm { margin: 0; }
    .otp-resend-button { padding: 7px 10px; color: var(--otp-blue); border: 1px solid #b9dff3; border-radius: 8px; background: #fff; font-size: 10px; font-weight: 850; cursor: pointer; }
    .otp-resend-button:hover { color: #fff; border-color: var(--otp-blue); background: var(--otp-blue); }
    .otp-secure-note { display: flex; align-items: center; justify-content: center; gap: 7px; margin: 13px 0 0; color: #8494a1; font-size: 10px; font-weight: 650; text-align: center; }
    .otp-secure-note svg { width: 14px; flex: 0 0 auto; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .otp-footer { width: min(490px, 100%); margin: 20px auto 0; color: #8a9ba8; font-size: 10px; font-weight: 650; text-align: center; }

    @media (max-width: 1050px) {
        .otp-layout { grid-template-columns: minmax(340px, .86fr) minmax(420px, 1.14fr); }
        .otp-showcase { padding: 42px 36px; }
        .otp-showcase-copy h1 { font-size: clamp(38px, 5vw, 52px); }
        .otp-brand-product { display: none; }
    }

    @media (max-width: 820px) {
        .otp-page { place-items: start center; padding: 18px; overflow: visible; }
        .otp-layout { min-height: calc(100svh - 36px); grid-template-columns: 1fr; }
        .otp-showcase { min-height: auto; display: block; padding: 24px 28px; }
        .otp-brand { justify-content: center; }
        .otp-brand-mark { width: 150px; min-height: 48px; padding: 7px 12px; }
        .otp-showcase-copy { margin: 18px auto 0; text-align: center; }
        .otp-eyebrow, .otp-showcase-copy > p, .otp-steps, .otp-showcase-footer { display: none; }
        .otp-showcase-copy h1 { font-size: 28px; line-height: 1.08; letter-spacing: -.035em; }
        .otp-showcase-copy h1 br { display: none; }
        .otp-workspace { padding: 34px clamp(26px, 7vw, 64px) 24px; }
        .otp-card, .otp-footer { width: min(520px, 100%); }
    }

    @media (max-width: 540px) {
        .otp-page { padding: 0; background: #fff; }
        .otp-ambient { display: none; }
        .otp-layout { min-height: 100svh; border: 0; border-radius: 0; box-shadow: none; }
        .otp-showcase { display: none; }
        .otp-workspace { justify-content: flex-start; padding: max(24px, env(safe-area-inset-top)) 20px max(20px, env(safe-area-inset-bottom)); }
        .otp-mobile-brand { display: flex; align-items: center; justify-content: space-between; width: 100%; margin-bottom: clamp(25px, 6vh, 48px); }
        .otp-mobile-brand img { width: 142px; height: auto; }
        .otp-mobile-brand span { padding: 6px 9px; color: var(--otp-blue); font-size: 10px; font-weight: 900; letter-spacing: .12em; border: 1px solid #cbe6f5; border-radius: 8px; background: #f1faff; }
        .otp-card { margin: 0 auto; }
        .otp-back { margin-bottom: 20px; }
        .otp-icon { width: 48px; height: 48px; margin-bottom: 15px; border-radius: 14px; }
        .otp-icon svg { width: 25px; }
        .otp-heading h2 { font-size: 30px; }
        .otp-inputs { gap: 6px; }
        .otp-digit { max-height: none; border-radius: 11px; font-size: 19px; }
        .otp-resend { grid-template-columns: minmax(0, 1fr) auto; }
        .otp-footer { margin-top: auto; padding-top: 22px; }
    }

    @media (max-width: 370px) {
        .otp-workspace { padding-left: 15px; padding-right: 15px; }
        .otp-mobile-brand { margin-bottom: 24px; }
        .otp-inputs { gap: 4px; }
        .otp-digit { border-radius: 9px; }
        .otp-resend > div > small { display: none; }
    }

    @media (min-width: 821px) and (max-height: 760px) {
        .otp-page { padding: 14px; }
        .otp-layout { min-height: calc(100svh - 28px); }
        .otp-showcase { gap: 20px; padding: 30px 38px; }
        .otp-showcase-copy h1 { font-size: 40px; }
        .otp-showcase-copy > p { margin: 16px 0 18px; line-height: 1.55; }
        .otp-steps { grid-template-columns: repeat(3, minmax(0, 1fr)); max-width: 560px; }
        .otp-step { grid-template-columns: 32px 1fr; padding: 8px; }
        .otp-step-number { width: 31px; height: 31px; }
        .otp-step small { display: none; }
        .otp-workspace { padding-top: 22px; padding-bottom: 18px; overflow-y: auto; }
        .otp-back { margin-bottom: 13px; }
        .otp-icon { width: 44px; height: 44px; margin-bottom: 11px; }
        .otp-heading h2 { font-size: 32px; }
        #otpForm { margin-top: 15px; }
        .otp-fieldset { margin-bottom: 13px; }
        .otp-resend { margin-top: 12px; padding-top: 9px; padding-bottom: 9px; }
        .otp-footer { margin-top: 12px; }
    }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after { scroll-behavior: auto !important; animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const otpInputs = Array.from(document.querySelectorAll('.otp-digit'));
    const otpValue = document.getElementById('otpValue');
    const otpError = document.getElementById('otpError');
    const otpForm = document.getElementById('otpForm');
    const verifyButton = document.getElementById('verifyButton');
    const resendForm = document.getElementById('resendForm');
    const timerDisplay = document.getElementById('timerDisplay');
    const countdown = document.getElementById('countdown');
    let isSubmitting = false;

    function updateOtp() {
        const value = otpInputs.map(function (input) { return input.value; }).join('');
        otpValue.value = value;
        verifyButton.disabled = value.length !== 6 || isSubmitting;

        otpInputs.forEach(function (input) {
            input.classList.toggle('is-filled', input.value !== '');
            if (input.value) input.classList.remove('has-error');
        });

        if (value.length === 6) otpError.textContent = '';
    }

    function distributeDigits(startIndex, value) {
        const digits = value.replace(/\D/g, '').slice(0, otpInputs.length - startIndex);
        if (!digits) return;

        digits.split('').forEach(function (digit, offset) {
            otpInputs[startIndex + offset].value = digit;
        });

        const focusIndex = Math.min(startIndex + digits.length, otpInputs.length - 1);
        otpInputs[focusIndex].focus();
        updateOtp();
    }

    otpInputs.forEach(function (input, index) {
        input.addEventListener('input', function (event) {
            if (isSubmitting) return;

            const digits = event.target.value.replace(/\D/g, '');
            if (!digits) {
                event.target.value = '';
                updateOtp();
                return;
            }

            if (digits.length > 1) {
                event.target.value = '';
                distributeDigits(index, digits);
                return;
            }

            event.target.value = digits;
            if (index < otpInputs.length - 1) otpInputs[index + 1].focus();
            updateOtp();
        });

        input.addEventListener('keydown', function (event) {
            if (isSubmitting) return;

            if (event.key === 'Backspace' && !input.value && index > 0) {
                event.preventDefault();
                otpInputs[index - 1].value = '';
                otpInputs[index - 1].focus();
                updateOtp();
            } else if (event.key === 'ArrowLeft' && index > 0) {
                event.preventDefault();
                otpInputs[index - 1].focus();
            } else if (event.key === 'ArrowRight' && index < otpInputs.length - 1) {
                event.preventDefault();
                otpInputs[index + 1].focus();
            }
        });

        input.addEventListener('paste', function (event) {
            if (isSubmitting) return;
            event.preventDefault();
            distributeDigits(index, event.clipboardData.getData('text'));
        });
    });

    otpForm.addEventListener('submit', function (event) {
        const finalOtp = otpValue.value.trim();

        if (isSubmitting) {
            event.preventDefault();
            return;
        }

        if (!/^\d{6}$/.test(finalOtp)) {
            event.preventDefault();
            otpError.textContent = 'Enter all six digits of your verification code.';
            otpInputs.forEach(function (input) { input.classList.add('has-error'); });
            const firstEmpty = otpInputs.find(function (input) { return !input.value; });
            (firstEmpty || otpInputs[0]).focus();
            return;
        }

        isSubmitting = true;
        verifyButton.disabled = true;
        verifyButton.setAttribute('aria-busy', 'true');
        verifyButton.innerHTML = '<span class="otp-spinner" aria-hidden="true"></span><span>Verifying code...</span>';
    });

    resendForm.addEventListener('submit', function (event) {
        if (typeof Swal === 'undefined') return;

        event.preventDefault();
        Swal.fire({
            title: 'Send a new code?',
            text: 'A new six-digit verification code will be sent to your email.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0877ca',
            cancelButtonColor: '#64778a',
            confirmButtonText: 'Yes, resend code'
        }).then(function (result) {
            if (result.isConfirmed) resendForm.submit();
        });
    });

    let timeLeft = 60;
    const countdownTimer = window.setInterval(function () {
        timeLeft -= 1;
        countdown.textContent = Math.max(timeLeft, 0);

        if (timeLeft <= 0) {
            window.clearInterval(countdownTimer);
            timerDisplay.hidden = true;
            resendForm.hidden = false;
        }
    }, 1000);

    otpInputs.forEach(function (input) { input.value = ''; });
    updateOtp();
    if (otpInputs[0]) otpInputs[0].focus();
});
</script>

@if (session('status'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Swal === 'undefined') return;

    Swal.fire({
        icon: 'success',
        title: 'Code sent',
        text: @json(session('status')),
        confirmButtonText: 'OK',
        confirmButtonColor: '#0877ca'
    });
});
</script>
@endif
@endsection
