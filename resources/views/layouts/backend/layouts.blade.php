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

    @auth
        @if (auth()->user()->hasRole('cashier'))
            @include('layouts.backend.cashierslidebar') <!-- Include header -->
        @elseif(auth()->user()->hasRole('warehouse'))
            @include('layouts.backend.warehouseslidebar') <!-- Include header -->
        @elseif(auth()->user()->hasRole('admin'))
            @include('layouts.backend.admin') <!-- Include header -->
        @else
            @include('layouts.backend.slidebar') <!-- Include header -->
        @endif
    @endauth

    @include('layouts.backend.nav') <!-- Include header -->
    <!-- loader Start -->
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

    @include('layouts.backend.footer') <!-- Include footer -->

    <script src="{{ asset('assets/js/script.js') }}"></script>
    <!-- Backend Bundle JavaScript -->
    <script src="{{ asset('assets/js/backend-bundle.min.js') }}"></script>

    <!-- Table Treeview JavaScript -->
    <script src="{{ asset('assets/js/table-treeview.js') }}"></script>

    <!-- Chart Custom JavaScript -->
    <script src="{{ asset('assets/js/customizer.js') }}"></script>

    <!-- Chart Custom JavaScript -->
    <script async src="{{ asset('assets/js/chart-custom.js') }}"></script>

    <!-- app JavaScript -->
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <!-- Add these manually AFTER backend-bundle -->
    {{-- <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
 --}}

    <!-- Include your JavaScript files here -->
    <script>
        setTimeout(function() {
            $('.toast').fadeOut('slow');
        }, 5000); // 5 seconds before fade-out
    </script>
    @yield('scripts')
</body>

</html>
