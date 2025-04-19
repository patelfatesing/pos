@extends('layouts.backend.cart')
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
@section('page-content')
<style>
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
.mb-4, .my-4{
    margin-bottom: 0px !important;
}
</style>
    <livewire:shoppingcart />
@endsection
