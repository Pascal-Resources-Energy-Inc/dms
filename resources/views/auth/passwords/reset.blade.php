@extends('layouts.app')

@section('content')
<style>
    .reset-password-container {
        min-height: 100vh;
        background: linear-gradient(135deg, #ffffffff 0%, #ffffffff 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: clamp(10px, 2vw, 20px);
    }

    .main-card {
        background: white;
        border-radius: clamp(15px, 2vw, 20px);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        max-width: 1200px;
        width: 100%;
        display: flex;
        min-height: clamp(400px, 60vh, 600px);
    }

    .illustration-section {
        flex: 1;
        background: linear-gradient(135deg, #fafdffff 0%, #ffffffff 100%);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: clamp(20px, 4vw, 40px);
        position: relative;
        min-width: 0;
    }

    .context-image {
        max-width: 100%;
        max-height: 70vh;
        width: auto;
        height: auto;
        object-fit: contain;
        border-radius: clamp(10px, 1.5vw, 20px);
    }

    .context-image-mobile {
        display: none;
        max-width: 200px;
        max-height: 250px;
        width: auto;
        height: auto;
        object-fit: contain;
        border-radius: 10px;
        margin: 0 auto;
    }

    .form-section {
        flex: 1;
        padding: clamp(20px, 4vw, 60px) clamp(15px, 3vw, 40px);
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-width: 0;
    }

    .card {
        border: none !important;
        box-shadow: none !important;
        background: transparent !important;
    }

    .card-body {
        padding: 0 !important;
    }

    .reset-title {
        color: #4facfe !important;
        font-size: 23px !important;
        font-weight: 600 !important;
        margin-bottom: 20px !important;
    }

    .text-muted {
        color: #666 !important;
        margin-bottom: clamp(20px, 3vw, 30px) !important;
        font-size: clamp(14px, 2vw, 16px);
    }

    .alert-success {
        background-color: rgba(40, 167, 69, 0.1) !important;
        border: 1px solid #28a745 !important;
        color: #28a745 !important;
        border-radius: clamp(8px, 1vw, 10px) !important;
        font-size: clamp(13px, 2vw, 14px);
        padding: clamp(10px, 2vw, 15px) !important;
    }

    .form-label {
        font-size: clamp(14px, 2vw, 16px);
        font-weight: 500;
        margin-bottom: clamp(6px, 1vw, 8px);
    }

    .form-control {
        padding: clamp(12px, 2.5vw, 15px) !important;
        border: 2px solid #e0e0e0 !important;
        border-radius: clamp(8px, 1vw, 10px) !important;
        font-size: clamp(14px, 2vw, 16px) !important;
        width: 100%;
        box-sizing: border-box;
    }

    .form-control:focus {
        border-color: #4facfe !important;
        box-shadow: 0 0 0 clamp(2px, 0.3vw, 3px) rgba(79, 172, 254, 0.1) !important;
    }

    .form-control.is-invalid {
        border-color: #dc3545 !important;
    }

    .password-field {
        position: relative;
    }

    .password-field .form-control {
        padding-right: 52px !important;
    }

    .password-toggle {
        position: absolute;
        top: 50%;
        right: 8px;
        transform: translateY(-50%);
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 8px;
        color: #6c757d;
        background: transparent;
        cursor: pointer;
        transition: color 0.2s ease, background-color 0.2s ease;
    }

    .password-toggle:hover,
    .password-toggle:focus-visible {
        color: #2589c4;
        background: rgba(79, 172, 254, 0.12);
        outline: none;
    }

    .password-toggle svg {
        width: 20px;
        height: 20px;
    }

    .btn-success {
        background-color: #5DADE2;
        border: none !important;
        border-radius: clamp(20px, 3vw, 25px) !important;
        padding: clamp(12px, 2.5vw, 15px) !important;
        font-size: clamp(14px, 2.5vw, 16px) !important;
        font-weight: bold !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease !important;
        width: 100%;
        min-height: clamp(45px, 6vw, 50px);
    }

    .btn-success:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 25px rgba(79, 172, 254, 0.3) !important;
        background-color: #4a8ab6ff;
    }

    .alert-danger {
        border-radius: clamp(8px, 1vw, 10px) !important;
        border: 1px solid #dc3545 !important;
        font-size: clamp(13px, 2vw, 14px);
        padding: clamp(10px, 2vw, 15px) !important;
    }

    .back-link {
        text-align: center;
        margin-top: clamp(15px, 2.5vw, 20px);
    }

    .back-link p {
        font-size: clamp(13px, 2vw, 14px);
        margin-bottom: 0;
    }

    .back-link a {
        color: #4facfe !important;
        text-decoration: none !important;
        font-weight: 500;
    }

    .back-link a:hover {
        text-decoration: underline !important;
    }

    .password-strength {
        margin-top: 10px;
        font-size: 12px;
    }

    .strength-weak { color: #dc3545; }
    .strength-medium { color: #ffc107; }
    .strength-strong { color: #28a745; }

    .password-requirements {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-top: 10px;
        font-size: 13px;
    }

    .password-requirements ul {
        margin-bottom: 0;
        padding-left: 20px;
    }

    .password-requirements li {
        margin-bottom: 5px;
    }

    .requirement-met {
        color: #28a745;
    }

    .requirement-not-met {
        color: #dc3545;
    }

    /* Mobile responsiveness */
    @media (max-width: 479px) {
        .main-card {
            flex-direction: column;
            margin: 5px;
            min-height: auto;
        }

        .illustration-section {
            display: none;
        }

        .form-section {
            padding: 20px 15px;
        }

        .context-image-mobile {
            display: block;
            margin: 15px auto 25px auto;
        }
    }

    @media (min-width: 480px) and (max-width: 767px) {
        .main-card {
            flex-direction: column;
            margin: 10px;
        }

        .illustration-section {
            display: none;
        }

        .form-section {
            padding: 25px 20px;
        }

        .context-image-mobile {
            display: block;
            margin: 15px auto 25px auto;
            max-height: 180px;
        }
    }

    @media (max-width: 767px) and (orientation: landscape) {
        .main-card {
            flex-direction: row;
            min-height: 400px;
        }

        .illustration-section {
            flex: 0.6;
            min-height: auto;
            padding: 15px;
        }

        .form-section {
            flex: 1.4;
            padding: 20px 15px;
        }

        .context-image {
            max-height: 50vh;
        }
        .context-image-mobile {
            display: none !important;
        }
    }
</style>

<div class="reset-password-container">
    <div class="main-card">
        <!-- Illustration Section -->
        <div class="illustration-section">
           <img src="{{asset('images/password.png')}}" alt="Reset Password" class="context-image">
        </div>

        <!-- Form Section -->
        <div class="form-section">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="card mt-4 card-bg-fill">
                        <div class="card-body p-4">
                            <div class="text-center mt-2">
                                <h5 class="reset-title">Reset Password</h5>
                            </div>

                            <!-- Image shows here only on mobile -->
                            <div class="text-center">
                                <img src="{{asset('images/password.png')}}" alt="Reset Password" class="context-image-mobile">
                            </div>

                            @if (session('success'))
                                <div class="alert border-0 alert-success text-center mb-3" role="alert">
                                    {{ session('success') }}
                                </div>
                            @endif

                            <div class="alert border-0 alert-success text-center mb-3" role="alert">
                                OTP verified! Please enter your new password.
                            </div>

                            <div class="p-2">
                                <form method="POST" action="{{ route('password.update') }}" id="resetPasswordForm">
                                    @csrf
                                    <input type="hidden" name="email" value="{{ $email }}">
                                    
                                    <div class="mb-4">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" value="{{ $email }}" readonly>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label">New Password</label>
                                        <div class="password-field">
                                            <input id="password" type="password" placeholder="New Password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>
                                            <button type="button" class="password-toggle" data-toggle-password="password" aria-label="Show new password" aria-pressed="false" title="Show password">
                                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </button>
                                        </div>
                                        
                                        <div class="password-strength" id="password-strength"></div>
                                        
                                        <div class="password-requirements">
                                            <strong>Password Requirements:</strong>
                                            <ul id="password-requirements">
                                                <li id="req-length" class="requirement-not-met">At least 8 characters</li>
                                                <li id="req-uppercase" class="requirement-not-met">One uppercase letter</li>
                                                <li id="req-lowercase" class="requirement-not-met">One lowercase letter</li>
                                                <li id="req-number" class="requirement-not-met">One number</li>
                                                <li id="req-special" class="requirement-not-met">One special character</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label">Confirm New Password</label>
                                        <div class="password-field">
                                            <input id="password-confirm" type="password" placeholder="Confirm New Password" class="form-control" name="password_confirmation" required>
                                            <button type="button" class="password-toggle" data-toggle-password="password-confirm" aria-label="Show confirmed password" aria-pressed="false" title="Show password">
                                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </button>
                                        </div>
                                        <div id="password-match" class="mt-2" style="font-size: 13px;"></div>
                                    </div>

                                    <div class="text-center mt-4">
                                        <button class="btn btn-success w-100" type="submit" id="reset-btn">Reset Password</button>
                                    </div>
                                    
                                    @if ($errors->has('password'))
                                        <div class="mt-3 form-group alert alert-danger alert-dismissable">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </div>
                                    @endif
                                </form>
                            </div>
                        </div>
                        <!-- end card body -->
                    </div>
                    <!-- end card -->

                    <div class="back-link">
                        <p class="mb-0">Back to <a href="{{ route('login') }}" class="fw-semibold text-primary text-decoration-underline">Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password-confirm');
    const strengthDiv = document.getElementById('password-strength');
    const matchDiv = document.getElementById('password-match');
    const resetBtn = document.getElementById('reset-btn');
    const showPasswordIcon = '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>';
    const hidePasswordIcon = '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 3 18 18"/><path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 4.2A10.7 10.7 0 0 1 12 4c6.5 0 10 8 10 8a18.5 18.5 0 0 1-3 4.3M6.6 6.6C3.7 8.5 2 12 2 12s3.5 8 10 8a9.8 9.8 0 0 0 4.1-.9"/></svg>';

    document.querySelectorAll('[data-toggle-password]').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = document.getElementById(this.dataset.togglePassword);
            const isHidden = input.type === 'password';

            input.type = isHidden ? 'text' : 'password';
            this.setAttribute('aria-pressed', String(isHidden));
            this.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
            this.setAttribute('title', isHidden ? 'Hide password' : 'Show password');
            this.innerHTML = isHidden ? hidePasswordIcon : showPasswordIcon;
        });
    });

    const resetPasswordForm = document.getElementById('resetPasswordForm');
    resetPasswordForm.addEventListener('submit', function () {
        if (!this.checkValidity()) {
            return;
        }

        resetBtn.disabled = true;
        resetBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span><span>Resetting Password...</span>';
    });
    
    // Password strength checker
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        const strength = checkPasswordStrength(password);
        
        strengthDiv.innerHTML = `Password strength: <span class="strength-${strength.level}">${strength.text}</span>`;
        
        updateRequirements(password);
        checkPasswordMatch();
    });
    
    confirmInput.addEventListener('input', checkPasswordMatch);
    
    function checkPasswordStrength(password) {
        let score = 0;
        let feedback = [];
        
        if (password.length >= 8) score++;
        if (password.match(/[a-z]/)) score++;
        if (password.match(/[A-Z]/)) score++;
        if (password.match(/[0-9]/)) score++;
        if (password.match(/[^a-zA-Z0-9]/)) score++;
        
        if (score < 3) return {level: 'weak', text: 'Weak'};
        if (score < 5) return {level: 'medium', text: 'Medium'};
        return {level: 'strong', text: 'Strong'};
    }
    
    function updateRequirements(password) {
        const requirements = [
            {id: 'req-length', test: password.length >= 8},
            {id: 'req-lowercase', test: /[a-z]/.test(password)},
            {id: 'req-uppercase', test: /[A-Z]/.test(password)},
            {id: 'req-number', test: /[0-9]/.test(password)},
            {id: 'req-special', test: /[^a-zA-Z0-9]/.test(password)}
        ];
        
        requirements.forEach(req => {
            const element = document.getElementById(req.id);
            if (req.test) {
                element.className = 'requirement-met';
            } else {
                element.className = 'requirement-not-met';
            }
        });
    }
    
    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmInput.value;
        
        if (confirmPassword === '') {
            matchDiv.innerHTML = '';
            return;
        }
        
        if (password === confirmPassword) {
            matchDiv.innerHTML = '<span style="color: #28a745;">✓ Passwords match</span>';
        } else {
            matchDiv.innerHTML = '<span style="color: #dc3545;">✗ Passwords do not match</span>';
        }
    }
});
</script>

@endsection
