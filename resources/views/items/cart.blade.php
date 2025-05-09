@extends('layouts.backend.cart')
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

@section('page-content')
    <style>
        .table-success, .table-success > th, .table-success > td {
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
        box-shadow: 0 1px 2px rgba(0,0,0,0.2);
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
            padding: 11px 20px ! important;
        }
        .list-group-item{
            border-bottom: 1px solid rgba(0, 0, 0, 0.125) !important;
            border-left: 0px !important;
            border-right: 0px !important;
        }
    </style>

    <style>
        .bg-gradient {
    background: linear-gradient(90deg, #007bff 0%, #0056b3 100%);
}


        #cartTable{
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
            display: none; /* Initially hidden */
            width: 100%;      /* Adjust width as needed */
            height: 90vh;    /* Adjust height as needed */
        }
        .small-table {
        width: 200px;         /* Reduce overall width */
        font-size: 12px;      /* Smaller font */
        border-collapse: collapse;
        }

        .small-table th, .small-table td {
        padding: 4px 8px;     /* Smaller cell padding */
        height: 25px;         /* Decrease row height */
        border: 1px solid #ccc;
        }

    </style>
    <livewire:shoppingcart />
    <div id="iframe-container"></div>

@endsection
