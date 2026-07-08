@extends('layouts.app')

@section('content')
<style>
    :root {
        --reset-primary: #2f80ed;
        --reset-primary-dark: #1d62c6;
        --reset-ink: #172033;
        --reset-muted: #667085;
        --reset-line: #e4e7ec;
        --reset-soft: #f5fbff;
        --reset-danger: #dc2626;
        --reset-success: #027a48;
    }

    .forgot-page {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background:
            radial-gradient(circle at 12% 12%, rgba(47, 128, 237, .12), transparent 28%),
            linear-gradient(135deg, #f8fbff 0%, #eef7ff 100%);
    }

    .forgot-shell {
        width: min(1080px, 100%);
        min-height: 620px;
        display: grid;
        grid-template-columns: 1.05fr .95fr;
        overflow: hidden;
        background: #fff;
        border: 1px solid rgba(47, 128, 237, .12);
        border-radius: 8px;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .12);
    }

    .forgot-visual {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 24px;
        padding: 56px;
        background: linear-gradient(135deg, #eaf6ff 0%, #ffffff 82%);
        border-right: 1px solid var(--reset-line);
    }

    .forgot-brand {
        position: absolute;
        top: 28px;
        left: 32px;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: var(--reset-ink);
        font-size: 14px;
        font-weight: 800;
    }

    .forgot-brand img {
        width: 36px;
        height: 36px;
        object-fit: contain;
    }

    .forgot-visual img.forgot-image {
        width: min(420px, 100%);
        max-height: 360px;
        object-fit: contain;
        align-self: center;
        filter: drop-shadow(0 18px 28px rgba(47, 128, 237, .16));
    }

    .forgot-steps {
        display: grid;
        gap: 10px;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .forgot-step {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #344054;
        font-size: 13px;
        font-weight: 700;
    }

    .forgot-step span {
        width: 24px;
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--reset-primary);
        background: #fff;
        border: 1px solid rgba(47, 128, 237, .18);
        border-radius: 999px;
        box-shadow: 0 8px 20px rgba(47, 128, 237, .08);
    }

    .forgot-panel {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 56px 46px;
    }

    .forgot-card {
        width: min(430px, 100%);
    }

    .forgot-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 14px;
        padding: 7px 11px;
        color: var(--reset-primary-dark);
        background: var(--reset-soft);
        border: 1px solid rgba(47, 128, 237, .16);
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    .forgot-title {
        margin: 0;
        color: var(--reset-ink);
        font-size: clamp(28px, 4vw, 38px);
        font-weight: 900;
        line-height: 1.08;
    }

    .forgot-copy {
        margin: 14px 0 28px;
        color: var(--reset-muted);
        font-size: 15px;
        line-height: 1.65;
    }

    .forgot-alert {
        display: flex;
        gap: 10px;
        margin-bottom: 18px;
        padding: 12px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.45;
    }

    .forgot-alert.is-error {
        color: #991b1b;
        background: #fef2f2;
        border: 1px solid #fecaca;
    }

    .forgot-alert.is-success {
        color: var(--reset-success);
        background: #ecfdf3;
        border: 1px solid #abefc6;
    }

    .forgot-field {
        margin-bottom: 18px;
    }

    .forgot-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
        color: #344054;
        font-size: 13px;
        font-weight: 800;
    }

    .forgot-input-wrap {
        position: relative;
    }

    .forgot-input-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #98a2b3;
        font-size: 18px;
        pointer-events: none;
    }

    .forgot-input {
        width: 100%;
        height: 52px;
        padding: 0 16px 0 44px;
        color: var(--reset-ink);
        background: #fff;
        border: 1px solid var(--reset-line);
        border-radius: 8px;
        font-size: 15px;
        font-weight: 700;
        outline: none;
        transition: border-color .16s ease, box-shadow .16s ease;
    }

    .forgot-input::placeholder {
        color: #98a2b3;
        font-weight: 600;
    }

    .forgot-input:focus {
        border-color: var(--reset-primary);
        box-shadow: 0 0 0 4px rgba(47, 128, 237, .12);
    }

    .forgot-input.is-invalid {
        border-color: var(--reset-danger);
        box-shadow: 0 0 0 4px rgba(220, 38, 38, .08);
    }

    .forgot-error {
        display: block;
        margin-top: 8px;
        color: var(--reset-danger);
        font-size: 12px;
        font-weight: 800;
    }

    .forgot-submit {
        width: 100%;
        height: 52px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: #fff;
        background: linear-gradient(135deg, var(--reset-primary), #56b4f2);
        border: 0;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 900;
        box-shadow: 0 14px 28px rgba(47, 128, 237, .24);
        transition: transform .16s ease, box-shadow .16s ease, background .16s ease;
    }

    .forgot-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 18px 34px rgba(47, 128, 237, .3);
    }

    .forgot-submit:disabled {
        cursor: wait;
        opacity: .78;
        transform: none;
    }

    .forgot-foot {
        margin-top: 24px;
        text-align: center;
        color: var(--reset-muted);
        font-size: 14px;
        font-weight: 650;
    }

    .forgot-foot a {
        color: var(--reset-primary-dark);
        font-weight: 900;
        text-decoration: none;
    }

    .forgot-foot a:hover {
        text-decoration: underline;
    }

    .forgot-mobile-image {
        display: none;
        width: min(190px, 70%);
        margin: 0 auto 22px;
        object-fit: contain;
    }

    @media (max-width: 900px) {
        .forgot-page {
            align-items: flex-start;
            padding: 16px;
        }

        .forgot-shell {
            min-height: auto;
            grid-template-columns: 1fr;
        }

        .forgot-visual {
            display: none;
        }

        .forgot-panel {
            padding: 34px 22px;
        }

        .forgot-mobile-image {
            display: block;
        }
    }

    @media (max-width: 420px) {
        .forgot-page {
            padding: 10px;
        }

        .forgot-panel {
            padding: 28px 16px;
        }

        .forgot-title {
            font-size: 26px;
        }
    }
</style>

<div class="forgot-page">
    <main class="forgot-shell" aria-labelledby="forgotPasswordTitle">
        <section class="forgot-visual" aria-label="Password recovery steps">
            <div class="forgot-brand">
                <img src="{{ asset('images/icon.png') }}" alt="">
                <span>{{ config('app.name', 'Gaz Lite') }}</span>
            </div>

            <img src="{{ asset('images/password.png') }}" alt="Password recovery illustration" class="forgot-image">

            <ul class="forgot-steps">
                <li class="forgot-step"><span>1</span> Enter the email linked to your account.</li>
                <li class="forgot-step"><span>2</span> Receive your 6-digit verification code.</li>
                <li class="forgot-step"><span>3</span> Verify the OTP and create a new password.</li>
            </ul>
        </section>

        <section class="forgot-panel">
            <div class="forgot-card">
                <img src="{{ asset('images/password.png') }}" alt="Password recovery illustration" class="forgot-mobile-image">

                <div class="forgot-kicker">
                    <i class="ti ti-shield-lock"></i>
                    Account Recovery
                </div>

                <h1 class="forgot-title" id="forgotPasswordTitle">Forgot your password?</h1>
                <p class="forgot-copy">
                    No stress. Enter your registered email and we will send a secure OTP code so you can reset your password.
                </p>

                @if (session('status'))
                    <div class="forgot-alert is-success" role="status">
                        <i class="ti ti-circle-check"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->has('email'))
                    <div class="forgot-alert is-error" role="alert">
                        <i class="ti ti-alert-circle"></i>
                        <span>{{ $errors->first('email') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" id="forgotPasswordForm" novalidate>
                    @csrf

                    <div class="forgot-field">
                        <label class="forgot-label" for="email">Email address</label>
                        <div class="forgot-input-wrap">
                            <i class="ti ti-mail forgot-input-icon"></i>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                class="forgot-input{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                placeholder="name@example.com"
                                autocomplete="email"
                                required
                                autofocus
                            >
                        </div>
                        @if ($errors->has('email'))
                            <span class="forgot-error">{{ $errors->first('email') }}</span>
                        @endif
                    </div>

                    <button class="forgot-submit" type="submit" id="forgotSubmitBtn">
                        <i class="ti ti-send"></i>
                        <span>Send OTP</span>
                    </button>
                </form>

                <div class="forgot-foot">
                    Remembered your password?
                    <a href="{{ route('login', ['direct' => 'true']) }}">Back to login</a>
                </div>
            </div>
        </section>
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('forgotPasswordForm');
        const button = document.getElementById('forgotSubmitBtn');

        if (!form || !button) {
            return;
        }

        form.addEventListener('submit', function () {
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span><span>Sending OTP...</span>';
        });
    });
</script>
@endsection
