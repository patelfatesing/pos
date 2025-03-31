@extends('layouts.frontend.layouts_login')
@section('page-content')
<div class="row align-items-center justify-content-center height-self-center">
    <div class="col-lg-8">
       <div class="card auth-card">
          <div class="card-body p-0">
             <div class="d-flex align-items-center auth-content">
                <div class="col-lg-7 align-self-center">
                   <div class="p-3">
                      <h2 class="mb-2">Sign In</h2>
                      <p>Login to stay connected.</p>
                      <form method="POST" action="{{ route('register') }}">
                        @csrf
                         <div class="row">
                            <div class="col-lg-12">
                               <div class="floating-label form-group">
                                  <input class="floating-input form-control" type="email" placeholder=" " :value="old('email')">
                                  <label>Email</label>
                               </div>
                            </div>
                            <div class="col-lg-12">
                               <div class="floating-label form-group">
                                  <input class="floating-input form-control" type="password" placeholder=" " :value="old('password')">
                                  <label>Password</label>
                               </div>
                            </div>
                            <div class="col-lg-6">
                               <div class="custom-control custom-checkbox mb-3">
                                  <input type="checkbox" class="custom-control-input" id="customCheck1">
                                  <label class="custom-control-label control-label-1" for="customCheck1">Remember Me</label>
                               </div>
                            </div>
                            <div class="col-lg-6">
                               <a href="auth-recoverpw.html" class="text-primary float-right">Forgot Password?</a>
                            </div>
                         </div>
                         <button type="submit" class="btn btn-primary">Sign In</button>
                         <p class="mt-3">
                            Create an Account <a href="auth-sign-up.html" class="text-primary">Sign Up</a>
                         </p>
                      </form>
                   </div>
                </div>
                <div class="col-lg-5 content-right">
                   <img src="../assets/images/login/01.png" class="img-fluid image-right" alt="">
                </div>
             </div>
          </div>
       </div>
    </div>
 </div>


    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
@endsection
