@extends('layouts.frontend.layouts_login')
@section('page-content')

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

<style>
    :root {
        --lh-gold: #c9922e;
        --lh-gold-dark: #a8741c;
        --lh-gold-light: #e6b755;
        --lh-dark: #0d0a07;
        --lh-cream: #fbf7ef;
        --lh-text: #1c1712;
        --lh-muted: #8a8378;
        --lh-gap: 20px 30px;
    }

    /* FULL PAGE BACKGROUND */
    .lh-page {
        position: fixed;
        inset: 0;
        z-index: 1000;
        display: flex;
        align-items: stretch;
        padding: var(--lh-gap);
        font-family: 'Montserrat', sans-serif;
        background-color: var(--lh-dark);
        background-image:
            linear-gradient(90deg, rgba(8, 6, 4, .65) 0%, rgba(8, 6, 4, .25) 45%, rgba(8, 6, 4, 0) 70%),
            linear-gradient(0deg, rgba(8, 6, 4, .55) 0%, rgba(8, 6, 4, 0) 40%),
            url('{{ asset('assets/images/login-bg.jpg') }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        overflow-y: auto;
    }

    /* ---------- LEFT (branding & new design) ---------- */
    .lh-left {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 25px 40px 35px;
        color: #fff;
        min-width: 0;
    }

    .lh-brand {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .lh-brand img {
        height: 85px;
        width: 85px;
        object-fit: contain;
    }

    .lh-brand-name {
        font-family: 'Playfair Display', serif;
        font-size: 40px;
        font-weight: 700;
        line-height: 1;
        color: #fff;
        margin: 0;
    }

    .lh-brand-name span {
        color: var(--lh-gold-light);
    }

    .lh-brand-sub {
        letter-spacing: 5px;
        font-size: 11px;
        color: #d8d2c6;
        margin-top: 6px;
    }

    .lh-welcome {
        max-width: 580px;
    }

    .lh-main-heading {
        font-family: 'Montserrat', sans-serif;
        font-size: 36px;
        font-weight: 700;
        line-height: 1.15;
        margin: 0 0 16px;
    }

    .lh-main-heading .white-text {
        color: #ffffff;
        display: block;
    }

    .lh-main-heading .gold-text {
        color: var(--lh-gold-light);
        display: block;
    }

    .lh-welcome-desc {
        font-size: 18px;
        line-height: 1.5;
        color: #e2ded5;
        margin-bottom: 24px;
        font-weight: 400;
    }

    .lh-welcome-divider {
        width: 65px;
        height: 3px;
        background: var(--lh-gold);
        margin-bottom: 35px;
    }

    .lh-features-grid {
        display: flex;
        align-items: flex-start;
        gap: 15px;
    }

    .lh-feature-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        width: 95px;
        position: relative;
    }

    .lh-feature-item:not(:last-child)::after {
        content: '';
        position: absolute;
        right: -10px;
        top: 15%;
        height: 70%;
        width: 1px;
        background: rgba(255, 255, 255, 0.15);
    }

    .lh-feature-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: rgba(201, 146, 46, 0.12);
        border: 1px solid rgba(201, 146, 46, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        color: var(--lh-gold-light);
        font-size: 20px;
    }

    .lh-feature-label {
        font-size: 12.5px;
        font-weight: 500;
        color: #f1ede5;
        line-height: 1.35;
    }

    /* ---------- RIGHT (floating login card) ---------- */
    .lh-right {
        width: 44%;
        min-width: 440px;
        max-width: 620px;
        background: linear-gradient(160deg, rgba(255, 253, 249, .94) 0%, rgba(251, 247, 239, .94) 60%, rgba(243, 234, 216, .94) 100%);
        -webkit-backdrop-filter: blur(6px);
        backdrop-filter: blur(6px);
        border: 1px solid rgba(201, 146, 46, .55);
        border-radius: 18px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 40px 60px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, .55);
    }

    .lh-tag {
        display: flex;
        align-items: center;
        gap: 18px;
        font-size: 20px;
        font-weight: 600;
        letter-spacing: 2px;
        color: var(--lh-gold-dark);
        margin-bottom: 14px;
        font-family: fangsong;
    }

    .lh-tag::after {
        content: '';
        width: 70px;
        height: 2px;
        background: var(--lh-gold);
    }

    .lh-title {
        font-size: 38px;
        font-weight: 700;
        color: var(--lh-text);
        margin: 0 0 8px;
    }

    .lh-subtitle {
        color: var(--lh-muted);
        font-size: 17px;
        margin-bottom: 34px;
    }

    .lh-alert {
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 14px;
        margin-bottom: 18px;
        transition: opacity .5s ease-out;
    }

    .lh-alert.success {
        background: #e7f6ec;
        color: #1d6b3a;
        border: 1px solid #b9e2c6;
    }

    .lh-alert.error {
        background: #fdeaea;
        color: #a12626;
        border: 1px solid #f3bcbc;
    }

    .lh-field {
        margin-bottom: 22px;
    }

    .lh-input-wrap {
        position: relative;
    }

    .lh-input-wrap .lh-icon-left {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: #6b645a;
        font-size: 17px;
        line-height: 1;
        pointer-events: none;
    }

    .lh-input-wrap .lh-eye {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: #6b645a;
        cursor: pointer;
        font-size: 16px;
        line-height: 1;
    }

    .lh-input {
        width: 100%;
        height: 50px;
        border: 1px solid #dcc79a;
        border-radius: 8px;
        background: #fffdf8;
        padding: 0 50px 0 56px;
        font-size: 15px;
        font-family: inherit;
        color: var(--lh-text);
        transition: all .2s;
        display: block;
    }

    .lh-input::placeholder {
        color: #8f887c;
    }

    .lh-input:focus {
        outline: none;
        border-color: var(--lh-gold);
        box-shadow: 0 0 0 3px rgba(201, 146, 46, .18);
        background: #fff;
    }

    .lh-field.has-error .lh-input {
        border-color: #d9534f;
    }

    .lh-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 4px 0 26px;
    }

    .lh-check {
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        font-size: 15px;
        color: var(--lh-text);
        margin: 0;
    }

    .lh-check input {
        display: none;
    }

    .lh-check .box {
        width: 26px;
        height: 26px;
        border-radius: 6px;
        border: 2px solid var(--lh-gold);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: transparent;
        font-size: 14px;
        transition: all .2s;
    }

    .lh-check input:checked+.box {
        background: var(--lh-gold);
        color: #fff;
    }

    .lh-forgot {
        color: var(--lh-gold-dark);
        font-weight: 500;
        font-size: 15px;
        text-decoration: none;
    }

    .lh-forgot:hover {
        color: var(--lh-gold);
        text-decoration: underline;
    }

    .lh-btn {
        width: 100%;
        height: 50px;
        border: 0;
        border-radius: 8px;
        background: linear-gradient(180deg, var(--lh-gold-light) 0%, var(--lh-gold) 45%, var(--lh-gold-dark) 100%);
        color: #fff;
        font-size: 18px;
        font-weight: 600;
        font-family: inherit;
        letter-spacing: .5px;
        box-shadow: 0 10px 22px rgba(168, 116, 28, .35);
        cursor: pointer;
        transition: transform .15s, box-shadow .15s;
    }

    .lh-btn i {
        margin-right: 10px;
    }

    .lh-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 26px rgba(168, 116, 28, .45);
    }

    .lh-footer {
        margin-top: 40px;
        font-size: 12.5px;
        color: black;
    }

    .lh-footer::before {
        content: '';
        display: block;
        width: 45px;
        height: 2px;
        background: var(--lh-gold);
        margin-bottom: 16px;
    }

    /* ---------- Responsive ---------- */
    @media (max-width: 991px) {
        .lh-page {
            flex-direction: column;
        }

        .lh-left {
            flex: none;
            padding: 15px 15px 25px;
        }

        .lh-left .lh-welcome {
            display: none;
        }

        .lh-brand img {
            height: 65px;
            width: 65px;
        }

        .lh-brand-name {
            font-size: 32px;
        }

        .lh-right {
            width: 100%;
            min-width: 0;
            max-width: none;
            flex: none;
            padding: 35px 25px;
        }
    }
</style>

<div class="lh-page">

    <div class="lh-left">
        <div class="lh-brand">
            <img src="{{ asset('assets/images/liquorhub-logo.png') }}" alt="LiquorHub">
            <div>
                <h1 class="lh-brand-name">Liquor<span>Hub</span></h1>
                <div class="lh-brand-sub">RETAIL <b>•</b> ERP <b>•</b> POS</div>
            </div>
        </div>

        <div class="lh-welcome">
            <h2 class="lh-main-heading">
                <span class="white-text">Smarter</span>
                <span class="white-text">Business.</span>
                <span class="gold-text">Better Control.</span>
            </h2>

            <div class="lh-welcome-desc">
                Complete ERP solution for your<br>liquor retail business.
            </div>

            <div class="lh-welcome-divider"></div>

            <div class="lh-features-grid">
                <div class="lh-feature-item">
                    <div class="lh-feature-icon">
                        <i class="fa fa-store"></i>
                    </div>
                    <span class="lh-feature-label">Multi Store<br>Management</span>
                </div>

                <div class="lh-feature-item">
                    <div class="lh-feature-icon">
                        <i class="fa fa-box-open"></i>
                    </div>
                    <span class="lh-feature-label">Stock &<br>Inventory</span>
                </div>

                <div class="lh-feature-item">
                    <div class="lh-feature-icon">
                        <i class="fa fa-chart-line"></i>
                    </div>
                    <span class="lh-feature-label">Sales &<br>Reports</span>
                </div>

                <div class="lh-feature-item">
                    <div class="lh-feature-icon">
                        <i class="fa fa-shield-alt"></i>
                    </div>
                    <span class="lh-feature-label">Secure &<br>Reliable</span>
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT SIDE (LOGIN FORM) --}}
    <div class="lh-right">
        <div class="lh-tag">LIQUORHUB ERP</div>
        <h3 class="lh-title">Sign In</h3>
        <div class="lh-subtitle">Enter your credentials to continue.</div>

        @if (session('success'))
            <div class="lh-alert success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="lh-alert error">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="lh-field {{ $errors->has('username') ? 'has-error' : '' }}">
                <div class="lh-input-wrap">
                    <i class="far fa-user lh-icon-left"></i>
                    <input id="username" class="lh-input" name="username" type="text"
                        value="{{ old('username') }}" placeholder="Enter your username" autofocus>
                </div>
                <x-input-error :messages="$errors->get('username')" />
            </div>

            <div class="lh-field {{ $errors->has('password') ? 'has-error' : '' }}">
                <div class="lh-input-wrap">
                    <i class="fa fa-lock lh-icon-left"></i>
                    <input id="password" class="lh-input" name="password" type="password"
                        placeholder="Enter your password">
                    <span class="lh-eye" onclick="togglePasswordVisibility()">
                        <i id="togglePasswordIcon" class="far fa-eye"></i>
                    </span>
                </div>
                <x-input-error :messages="$errors->get('password')" />
            </div>

            <div class="lh-row">
                <label class="lh-check" for="customCheck1">
                    <input type="checkbox" id="customCheck1" name="remember" checked>
                    <span class="box"><i class="fa fa-check"></i></span>
                    Remember Me
                </label>
                <a href="{{ route('password.request') }}" class="lh-forgot">Forgot Password?</a>
            </div>

            <button type="submit" class="lh-btn">
                <i class="fa fa-sign-in-alt"></i> Login
            </button>
        </form>

        <div class="lh-footer">
            LiquorHub ERP v1.0 &nbsp;|&nbsp; © {{ date('Y') }} LiquorHub. All rights reserved.
        </div>
    </div>
</div>

<script>
    setTimeout(function() {
        let alert = document.querySelector('.lh-alert');
        if (alert) {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }
    }, 3000);

    function togglePasswordVisibility() {
        const input = document.getElementById('password');
        const icon = document.getElementById('togglePasswordIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection