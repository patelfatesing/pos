

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
      .text-red
      {
        color: red;
      }
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
    .numpad {
            position: absolute;
            display: grid;
            grid-template-columns: repeat(3, 60px);
            gap: 10px;
            background: #f1f1f1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 10px;
            z-index: 1000;
        }

        .numpad button {
            width: 60px;
            height: 60px;
            font-size: 18px;
            border: none;
            background-color: #fff;
            cursor: pointer;
            border-radius: 5px;
            box-shadow: 0 0 4px rgba(0,0,0,0.2);
        }

        .numpad button:hover {
            background-color: #ddd;
        }
        /* Ensuring logo aligns with the text */
        .light-logo {
           color: #32bdea !important;
           
        }
        /* CSS for small SweetAlert */
        .swal2-popup.small-alert {
            width: 300px !important;  /* Set the width to a smaller size */
            padding: 20px;            /* Adjust padding for a more compact look */
            font-size: 16px;          /* Adjust font size */
        }

        .swal2-popup.small-alert .swal2-title {
            font-size: 18px;          /* Adjust the title font size */
        }

        .swal2-popup.small-alert .swal2-content {
            font-size: 14px;          /* Adjust the message font size */
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