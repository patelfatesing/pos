<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>LiquorHub POS</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="../assets/images/favicon.ico" />
    <link rel="stylesheet" href="../assets/css/backend-plugin.min.css">
    <link rel="stylesheet" href="../assets/css/backend.css?v=1.0.0">
    <link rel="stylesheet" href="../assets/vendor/@fortawesome/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../assets/vendor/line-awesome/dist/line-awesome/css/line-awesome.min.css">
    <link rel="stylesheet" href="../assets/vendor/remixicon/fonts/remixicon.css">
    <style>
        .bg-gradient {
            background: linear-gradient(90deg, #007bff 0%, #0056b3 100%);
        }


        #cartTable {
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #ccc;
        }

        .cart-table-scroll.scrollable {
            overflow-y: auto;
        }

        /* Default (mobile first) */
        .cart-table-scroll.scrollable {
            max-height: 300px;
        }

        /* Tablet screens and up */
        @media (min-width: 768px) {
            .cart-table-scroll.scrollable {
                max-height: 400px;
            }
        }

        /* Desktop screens and up */
        @media (min-width: 992px) {
            .cart-table-scroll.scrollable {
                max-height: 500px;
            }
        }

        /* Large desktops */
        @media (min-width: 1200px) {
            .cart-table-scroll.scrollable {
                max-height: 600px;
            }
        }

        @media (min-width: 1300px) {
            .cart-table-scroll {
                height: 420px;
                overflow-y: auto;
            }
        }

        #iframe-container {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            background-color: white;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            display: none;
            /* Initially hidden */
            width: 100%;
            /* Adjust width as needed */
            height: 90vh;
            /* Adjust height as needed */
        }

        .small-table {
            width: 200px;
            /* Reduce overall width */
            font-size: 12px;
            /* Smaller font */
            border-collapse: collapse;
        }

        .small-table th,
        .small-table td {
            padding: 4px 8px;
            /* Smaller cell padding */
            height: 25px;
            /* Decrease row height */
            border: 1px solid #ccc;
        }

        .table-success,
        .table-success>th,
        .table-success>td {
            background-color: #d9ede0;
        }

        .note-btn {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            font-size: 14px;
            border-radius: 50%;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        .popup-notifications {
            z-index: 999;
        }

        .btn.rounded-circle {
            width: 40px;
            height: 40px;
            font-size: 1.25rem;
            padding: 0;
        }

        span.fs-5 {
            min-width: 30px;
            display: inline-block;
            text-align: center;
        }

        .table td {
            padding: 10px !important;
            border: 0px;
            border-bottom: 1px solid #DCDFE8;
            color: #110A57;
        }

        .small-text-input {
            width: 100px;
            height: 25px;
            font-size: 12px;
            padding: 4px;
        }

        .form-control {
            height: 30px ! important;

        }

        .mb-4,
        .my-4 {
            margin-bottom: 0px !important;
        }

        .card .card-header {
            padding: 11px 10px ! important;
        }

        .list-group-item {
            border-bottom: 1px solid rgba(0, 0, 0, 0.125) !important;
            border-left: 0px !important;
            border-right: 0px !important;
        }

        .card {
            border-radius: 0% ! important;
        }

        .text-red {
            color: red;
        }

        .content-page {
            padding: 0%;
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

        #cartTable thead,
        #cartTable tbody tr {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .table {
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
            box-shadow: 0 0 4px rgba(0, 0, 0, 0.2);
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
            width: 300px !important;
            /* Set the width to a smaller size */
            padding: 20px;
            /* Adjust padding for a more compact look */
            font-size: 16px;
            /* Adjust font size */
        }

        .swal2-popup.small-alert .swal2-title {
            font-size: 18px;
            /* Adjust the title font size */
        }

        .swal2-popup.small-alert .swal2-content {
            font-size: 14px;
            /* Adjust the message font size */
        }

        .customtable td,
        .customtable th {
            padding: 5px !important;

        }

        .btn img {
            margin-right: 1px ! important;
        }

        .btn i {
            margin-right: 0px !important;
        }

        .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            line-height: 1.5;
            border-radius: 10px;
        }

        .iq-sidebar-logo {
            padding: 15px 0px !important;
            padding-right: 0px !important;
            margin-top: 0px !important;
            margin-bottom: 0px !important;
            margin-right: 0px !important;

        }

        .custom-input,
        .custom-btn {
            border-radius: 0px ! important;

        }

        /* Default widths */
        .col-product {
            width: 45% !important;
        }

        .col-qty {
            width: 15% !important;
        }

        .col-price {
            width: 10% !important;
        }

        .col-total {
            width: 10% !important;
        }

        .col-actions {
            width: 8% !important;
        }

        /* Responsive adjustments for tablets and small screens */
        @media (max-width: 1024px) {
            .col-product {
                width: 30% !important;
            }

            .col-qty,
            .col-price,
            .col-total,
            .col-actions {
                width: auto !important;
                text-align: center;
            }
        }

        /* Responsive adjustments for mobile */
        @media (max-width: 768px) {
            .col-product {
                width: 50% !important;
            }

            .col-qty,
            .col-price,
            .col-total,
            .col-actions {
                width: auto !important;
                text-align: center;
            }
        }

        .progress-steps {
            padding: 20px 0;
        }

        .progress-step {
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            background-color: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: bold;
            color: #6c757d;
            transition: all 0.3s ease;
        }

        .progress-step.active .step-circle {
            background-color: #0d6efd;
            color: white;
        }

        .progress-step.done .step-circle {
            background-color: #198754;
            color: white;
        }

        .step-label {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .progress-line {
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #e9ecef;
            z-index: 0;
        }

        .capture-container {
            position: relative;
            background-color: #000;
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .capture-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .capture-guide {
            border: 2px dashed rgba(255, 255, 255, 0.5);
            width: 80%;
            height: 80%;
        }

        .preview-box {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.5rem;
        }

        .review-box {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
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

    <div class="modal fade bd-example-modal-lg" id="approveModal" tabindex="-1" role="dialog"
        aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" id="modalContent">
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


        $(document).on('click', '.open-form', function() {
            let type = $(this).data('type');

            let id = $(this).data('id');

            let nfid = $(this).data('nfid');
            let id_get = $(this).attr('id');

            let get_tc = parseInt($(".notification-count").text()); // get current cou

            // console.log(get_tc,"==get_tc");
            $.ajax({
                url: '/popup/form/' + type + "?id=" + id + "&nfid=" + nfid,
                type: 'GET',
                success: function(response) {
                    $("#" + id_get).removeClass("iq-sub-card open-form mb-1 msg_unread");
                    $("#" + id_get).addClass("iq-sub-card open-form mb-1 msg_read");


                    if (get_tc > 0) {
                        get_tc = get_tc - 1;
                    }
                    $(".notification-count").text(get_tc);

                    $('#modalContent').html(response);

                    $('#approveModal').modal('show');
                },
                error: function() {
                    alert('Failed to load form.');
                }
            });
        });

        // Optional: Close modal on background click
        $(document).on('click', '#popupModal', function(e) {
            if (e.target === this) {
                $(this).fadeOut();
            }
        });

        function nfModelCls() {
            $('#approveModal').modal('hide');
        }   
    </script>
    @livewireScripts

</body>

</html>
