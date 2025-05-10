@extends('layouts.backend.cart')

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 5 CSS -->

<!-- Bootstrap 5 JS with Popper included -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@section('page-content')
    <livewire:shoppingcart />
    <div id="iframe-container"></div>

@endsection
