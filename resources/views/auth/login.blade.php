@extends('layouts.frontend.layouts_login')
@section('page-content')
<div class="row min-vh-100 align-items-center justify-content-center ">
    <div class="col-md-8 col-lg-8">
        @if (session('success'))
            <div class="alert alert-success shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="card border-0 shadow-lg rounded-lg">
            <div class="row no-gutters">
                <div class="col-md-6 d-flex flex-column justify-content-center align-items-center bg-primary text-white rounded-left p-4">
                    <img src="{{ asset('assets/images/liquor_icon_.png') }}"
                                alt="Print Invoice Icon" style="height: 185px">
                    <h2 class="font-weight-bold mb-2" style="letter-spacing: 2px;">LiquorHub</h2>
                    <p class="text-center mb-0" style="max-width: 220px;">
                        Welcome! Login to access your account and explore our exclusive collection.
                    </p>
                </div>
                <div class="col-md-6 p-4">
                    <h3 class="mb-3 text-center font-weight-bold">Sign In</h3>
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="form-group">
                            <label for="username" class="font-weight-bold">Username</label>
                            <input id="username" class="form-control" name="username" type="text" value="{{ old('username') }}" placeholder="Enter your username" autofocus>
                            <x-input-error :messages="$errors->get('username')" class="mt-2" />
                        </div>
                        <div class="form-group position-relative">
                            <label for="password" class="font-weight-bold">Password</label>
                            <input id="password" class="form-control pr-5" name="password" type="password" placeholder="Enter your password">
                            <span class="position-absolute" style="top: 38px; right: 15px; cursor: pointer;" onclick="togglePasswordVisibility()">
                                <i id="togglePasswordIcon" class="fa fa-eye"></i>
                            </span>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="customCheck1" name="remember">
                                <label class="custom-control-label" for="customCheck1">Remember Me</label>
                            </div>
                            <a href="{{ route('password.request') }}" class="text-primary small">Forgot Password?</a>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block font-weight-bold">Login</button>
                        {{-- <p class="mt-3 text-center">Create an Account <a href="auth-sign-up.html" class="text-primary">Sign Up</a></p> --}}
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    setTimeout(function() {
        let alert = document.querySelector('.alert');
        if (alert) {
            alert.style.transition = 'opacity 0.5s ease-out';
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
