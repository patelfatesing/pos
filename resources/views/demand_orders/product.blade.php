@extends('layouts.backend.layouts')
<style>
    .form-group {
        margin-bottom: 0rem !important;
    }
</style>
@section('page-content')
    <!-- Wrapper Start -->
    <div class="wrapper">

        <div class="content-page">
            <div class="container-fluid add-form-list">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <div class="header-title">
                                    <h4 class="card-title">View Demand Product</h4>
                                </div>
                                <div>
                                    <a href="{{ route('demand-order.list') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="card-body">
                                     <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Sr No</th>
                                                    <th>Product Name</th>
                                                    <th>Quantity</th>
                                                    <th>Barcode</th>
                                                    <th>MRP</th>
                                                    <th>Rate</th>
                                                    <th>Sell Price</th>
                                                    <th>Delivery Status</th>
                                                    <th>Delivery Quantity</th>
                                                    <th>Created At</th>
                                                    
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($demandOrderProducts as $key => $product)
                                                    <tr>
                                                        <td>{{ $key+1; }}</td>
                                                        <td>{{ $product->product_name }}</td>
                                                        <td>{{ $product->quantity }}</td>
                                                        <td>{{ $product->barcode }}</td>
                                                        <td>{{ number_format($product->mrp, 2) }}</td>
                                                        <td>{{ number_format($product->rate, 2) }}</td>
                                                        <td>{{ number_format($product->sell_price, 2) }}</td>
                                                        <td>{{ ucfirst($product->delivery_status) }}</td>
                                                        <td>{{ $product->delivery_quantity }}</td>
                                                        <td>{{ $product->created_at }}</td>
                                                       
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="12" class="text-center">No products found.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                        @if ($demandOrderProducts->hasPages())
                                            <div class="d-flex justify-content-center mt-3">
                                                {{ $demandOrderProducts->links('pagination::bootstrap-5') }}
                                            </div>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Page end -->
                </div>
            </div>
        </div>
        <!-- Wrapper End -->
    @endsection
