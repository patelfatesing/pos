@extends('layouts.backend.layouts')

@section('page-content')
    <style>
        .access-denied-wrapper {
            height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f7fb;
            position: relative;
            overflow: hidden;
        }

        /* Cloud background */
        .cloud {
            position: absolute;
            background: #e9edf5;
            border-radius: 50px;
            opacity: 0.6;
        }

        .cloud:before,
        .cloud:after {
            content: '';
            position: absolute;
            background: #e9edf5;
            border-radius: 50%;
        }

        .cloud1 {
            width: 120px;
            height: 40px;
            top: 20%;
            left: 15%;
        }

        .cloud1:before {
            width: 50px;
            height: 50px;
            top: -25px;
            left: 10px;
        }

        .cloud1:after {
            width: 60px;
            height: 60px;
            top: -30px;
            right: 10px;
        }

        .cloud2 {
            width: 150px;
            height: 50px;
            top: 30%;
            right: 10%;
        }

        .cloud2:before {
            width: 60px;
            height: 60px;
            top: -30px;
            left: 20px;
        }

        .cloud2:after {
            width: 70px;
            height: 70px;
            top: -35px;
            right: 20px;
        }

        /* Lock Icon Box */
        .lock-box {
            width: 90px;
            height: 90px;
            background: #3b82f6;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            position: relative;
        }

        .lock-box i {
            color: #fff;
            font-size: 30px;
        }

        .lock-shackle {
            width: 40px;
            height: 30px;
            border: 3px solid #333;
            border-bottom: none;
            border-radius: 20px 20px 0 0;
            position: absolute;
            top: -25px;
            left: 50%;
            transform: translateX(-50%);
            background: transparent;
        }

        .access-text h3 {
            font-weight: 600;
            margin-bottom: 10px;
        }

        .access-text p {
            color: #6c757d;
            font-size: 14px;
        }
    </style>

    <div class="access-denied-wrapper">

        <!-- Clouds -->
        <div class="cloud cloud1"></div>
        <div class="cloud cloud2"></div>

        <div class="text-center access-text">

            <div class="lock-box">
                <div class="lock-shackle"></div>
                <i class="las la-key"></i>
            </div>

            <h3>Access denied</h3>

            <p>
                You do not have access to this feature.<br>
                Please contact your manager to ask for permission.
            </p>

            <a href="{{ route('dashboard') }}" class="btn btn-primary mt-3">
                <i class="las la-home"></i> Go to Home
            </a>

        </div>

    </div>
@endsection
