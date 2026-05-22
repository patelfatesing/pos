<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'LiquorHub')</title>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/backend-plugin.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/backend.css?v=1.0.0') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/@fortawesome/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/line-awesome/dist/line-awesome/css/line-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/remixicon/fonts/remixicon.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /* Position the toast at the top-right corner */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .toast {
            min-width: 250px;
        }
    </style>
</head>

<body>
    @include('layouts.flash-message')

  
  <div id="loading">
        <div id="loading-center">
        </div>
    </div>
    <!-- loader END -->
    <!-- Wrapper Start -->
    <div class="wrapper">
        <main>
            @yield('page-content')
        </main>
    </div>


    {{-- your bundles --}}
    <script src="{{ asset('assets/js/backend-bundle.min.js') }}" defer></script>
    <script src="{{ asset('assets/js/table-treeview.js') }}" defer></script>
    <script src="{{ asset('assets/js/customizer.js') }}" defer></script>
    {{-- <script src="{{ asset('assets/js/chart-custom.js') }}" defer></script> --}}
    <script src="{{ asset('assets/js/script.js') }}" defer></script>
    <script src="{{ asset('assets/js/app.js') }}" defer></script>

    @stack('page-scripts')


    <!-- Include your JavaScript files here -->
    <script>
        setTimeout(function() {
            $('.toast').fadeOut('slow');
        }, 5000); // 5 seconds before fade-out
    </script>
    @yield('scripts')
</body>

</html>
