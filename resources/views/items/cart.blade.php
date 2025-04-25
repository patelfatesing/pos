@extends('layouts.backend.cart')
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
@section('page-content')
    <style>
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
    </style>

    <style>

        #cartTable{
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #ccc;
            border-radius: 12px;
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
        
    </style>
    <livewire:shoppingcart />
@endsection
