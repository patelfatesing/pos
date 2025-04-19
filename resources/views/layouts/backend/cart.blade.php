

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <title>POS Dash </title>
      
      <!-- Favicon -->
      <link rel="shortcut icon" href="../assets/images/favicon.ico" />
      <link rel="stylesheet" href="../assets/css/backend-plugin.min.css">
      <link rel="stylesheet" href="../assets/css/backend.css?v=1.0.0">
      <link rel="stylesheet" href="../assets/vendor/@fortawesome/fontawesome-free/css/all.min.css">
      <link rel="stylesheet" href="../assets/vendor/line-awesome/dist/line-awesome/css/line-awesome.min.css">
      <link rel="stylesheet" href="../assets/vendor/remixicon/fonts/remixicon.css"> 
    <style>
        .content-page{
            padding: 1%;
            margin: 0%
        }
        .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 250px;

    }
    .search-results {
        max-height: 300px;
        overflow-y: auto;
        position: absolute;
        z-index: 999;
        width: 100%;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    #cartTable tbody {
        min-height: 450px;
        display: block;
    }
    #cartTable thead, #cartTable tbody tr {
        display: table;
        width: 100%;
        table-layout: fixed;
    }
    .table{
      border-radius: unset;
    }
     /* Hide on screen */
     .print-only {
        display: none;
    }

    /* Show only when printing */
    @media print {
        .print-only {
            display: block !important;
        }
        .no-print {
            display: none !important;
        }
    }
    </style>
    @livewireStyles
    </head>
  <body class=" color-light ">
    <!-- loader Start -->
    <div id="loading">
          <div id="loading-center">
          </div>
    </div>
    <!-- loader END -->
    <!-- Wrapper Start -->
    <div class="wrapper">
      
      <div class="content-page">
      <div class="container-fluid">
        @yield('page-content')

      </div>
      </div>
    </div>

    <!-- Backend Bundle JavaScript -->
    <script src="../assets/js/backend-bundle.min.js"></script>
    
    <!-- Table Treeview JavaScript -->
    <script src="../assets/js/table-treeview.js"></script>
    
    <!-- Chart Custom JavaScript -->
    <script src="../assets/js/customizer.js"></script>
    
    <!-- Chart Custom JavaScript -->
    <script async src="../assets/js/chart-custom.js"></script>
    
    <!-- app JavaScript -->
    <script src="../assets/js/app.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
      setTimeout(function() {
          $('.toast').fadeOut('slow');
      }, 5000); // 5 seconds before fade-out
    </script>
    @livewireScripts
  
  </body>
</html>