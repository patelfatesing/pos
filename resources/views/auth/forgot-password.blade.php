@extends('layouts.frontend.layouts_login')

@section('page-content')

<div class="row justify-content-center align-items-center min-vh-100 ">
    <div class="col-md-6 col-lg-5">
        

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <img src="{{ asset('assets/images/login/01.png')}}" alt="Forgot Password" class="mb-3" style="max-height: 80px;">
                    <h4 class="fw-bold">Forgot Password?</h4>
                    <p class="text-muted small">
                        Enter your email address and we'll send you a link to reset your password.
                    </p>
                </div>

                <!-- Form Start -->
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="form-group mb-3">
                        <label for="email" class="form-label fw-semibold">Email Address</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control rounded-3 @error('email') is-invalid @enderror" required autofocus>
                        @error('email')
                            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary rounded-3 fw-semibold">
                            Send Reset Link
                        </button>
                    </div>

                </form>
                <!-- Form End -->
            </div>
        </div>
    </div>
</div>
@endsection
