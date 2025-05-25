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
    <style>
        .flash-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 250px;
        }
        .alert.hide {
            opacity: 0;
            transition: opacity 0.5s ease-out;
        }
    </style>

</head>

<body>
    
    <!-- loader Start -->
    <div id="loading">
        <div id="loading-center">
        </div>
    </div>
    <!-- loader END -->
    <!-- Wrapper Start -->
    <div class="wrapper">
        <section class="login-content">
            <div class="container">
                @if (session('status'))
                    <div id="flash-success" class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                    </div>
                @endif
            
                @if ($errors->any())
                    <div id="flash-error" class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ $errors->first('email') }}
                    </div>
                   
                @endif
                
                <main>
                    @yield('page-content')
                </main>
            </div>
        </section>
    </div>
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
     <script>
        setTimeout(() => {
            const success = document.getElementById('flash-success');
            if (success) {
                success.classList.add('hide');
                setTimeout(() => success.remove(), 500); // wait for fade-out
            }
        }, 3000);
        setTimeout(() => {
        const error = document.getElementById('flash-error');
        if (error) {
            error.classList.add('hide');
            setTimeout(() => error.remove(), 500);
        }
    }, 4000); // 4 seconds for error
    </script>
</body>

</html>
