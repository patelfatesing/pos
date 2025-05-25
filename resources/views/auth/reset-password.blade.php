@extends('layouts.frontend.layouts_login')

@section('page-content')
<div class="container-fluid vh-100 d-flex justify-content-center align-items-center">
    <div class="pos-card pos-card-login shadow-lg rounded-3 p-4" style="max-width: 400px; width: 100%;">
     

        <h3 class="pos-card-title mb-4 text-center fw-bold">Reset Password</h3>

        <form method="POST" action="{{ route('password.store') }}">
            @csrf

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <!-- Email Address -->
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Email</label>
                <input id="email" type="email" name="email" 
                       value="{{ old('email', $request->email) }}" 
                       required autofocus autocomplete="username"
                       class="form-control @error('email') is-invalid @enderror" />
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">Password</label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       class="form-control @error('password') is-invalid @enderror" />
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="mb-4">
                <label for="password_confirmation" class="form-label fw-semibold">Confirm Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="form-control @error('password_confirmation') is-invalid @enderror" />
                @error('password_confirmation')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-bold">Reset Password</button>
        </form>
    </div>
</div>
@endsection
