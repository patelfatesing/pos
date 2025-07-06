@extends('layouts.frontend.layouts_login')
@section('page-content')
    <div class="row align-items-center justify-content-center height-self-center">
        <div class="col-lg-8">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif


            <div class="card auth-card">
                <div class="card-body p-0">
                    <div class="d-flex align-items-center auth-content">
                        <div class="col-lg-7 align-self-center">
                            <div class="p-3">
                                <h2 class="mb-2">Sign In</h2>
                                <p>Login to stay connected.</p>
                                <form method="POST" action="{{ route('login') }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input class="floating-input form-control" name="email" type="email"
                                                    placeholder=" " :value="old('email')">

                                            </div>
                                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="form-group position-relative">
                                                <label>Password</label>
                                                <input id="password" class="floating-input form-control pr-5"
                                                    name="password" type="password" placeholder=" "
                                                    :value="old('password')">
                                                <!-- Eye icon -->
                                                <span class="position-absolute"
                                                    style="top: 38px; right: 15px; cursor: pointer;"
                                                    onclick="togglePasswordVisibility()">
                                                    <i id="togglePasswordIcon" class="fa fa-eye"></i>
                                                </span>
                                            </div>
                                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                        </div>


                                        <div class="col-lg-6">
                                            <div class="custom-control custom-checkbox mb-3">
                                                <input type="checkbox" class="custom-control-input" id="customCheck1">
                                                <label class="custom-control-label control-label-1"
                                                    for="customCheck1">Remember Me</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <a href="{{ route('password.request') }}"
                                                class="text-primary float-right">Forgot Password?</a>
                                        </div>

                                    </div>
                                    <button type="submit" class="btn btn-primary">Login</button>
                                    <p class="mt-3">
                                        {{-- Create an Account <a href="auth-sign-up.html" class="text-primary">Sign Up</a> --}}
                                    </p>
                                </form>
                            </div>
                        </div>
                        <div class="col-lg-5 content-right">
                            <img src="{{ asset('assets/images/login/01.png') }}" class="img-fluid image-right"
                                alt="">
                        </div>
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
        }, 3000); // Hide after 3 seconds
    </script>

    <script>
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
