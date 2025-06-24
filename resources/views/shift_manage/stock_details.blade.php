@extends('layouts.backend.layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Wrapper Start -->
    <div class="wrapper">

        <div class="content-page">
            <div class="container-fluid">
                <div class="col-lg-12">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                        <div>
                            <h4 class="mb-3">🧾 Product Stock Summary</h4>
                        </div>
                        <div>
                            <a href="{{ route('shift-manage.list') }}" class="btn btn-secondary">Back</a>
                        </div>
                    </div>
                </div>
                <div class="table-responsive rounded mb-3" id="shiftTableContainer">
                    <div class="card mt-4">

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product</th>
                                            <th>Opening Stock</th>
                                            <th>Added Stock</th>
                                            <th>Transferred Stock</th>
                                            <th>Sold Stock</th>
                                            <th>Closing Stock</th>
                                            <th>Physical Stock</th>
                                            <th>Difference Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totalOpening = $totalAdded = $totalTransferred = $totalSold = $totalClosing = $totalPhysical = $totalDifference = 0;
                                        @endphp

                                        @forelse ($rawStockData as $stock)
                                            @php
                                                $totalOpening += $stock->opening_stock;
                                                $totalAdded += $stock->added_stock;
                                                $totalTransferred += $stock->transferred_stock;
                                                $totalSold += $stock->sold_stock;
                                                $totalClosing += $stock->closing_stock;
                                                $totalPhysical += !empty($stock->physical_stock)
                                                    ? $stock->physical_stock
                                                    : 0;
                                                $totalDifference += $stock->difference_in_stock;
                                            @endphp
                                            <tr
                                                style="background-color: {{ $stock->difference_in_stock < 0 ? '#ffcccc' : 'transparent' }}">
                                                <td>{{ $stock->product->name ?? 'N/A' }}</td>
                                                <td>{{ $stock->opening_stock }}</td>
                                                <td>{{ $stock->added_stock }}</td>
                                                <td>{{ $stock->transferred_stock }}</td>
                                                <td>{{ $stock->sold_stock }}</td>
                                                <td>{{ $stock->closing_stock }}</td>
                                                <td>{{ $stock->physical_stock }}</td>
                                                <td>
                                                    {{ $stock->difference_in_stock }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">No stock data
                                                    available.</td>
                                            </tr>
                                        @endforelse

                                        <tr class="fw-semibold text-end" style="font-size: 1.1rem;">
                                            <th colspan="1">Total</th>
                                            <th>{{ $totalOpening }}</th>
                                            <th>{{ $totalAdded }}</th>
                                            <th>{{ $totalTransferred }}</th>
                                            <th>{{ $totalSold }}</th>
                                            <th>{{ $totalClosing }}</th>
                                            <th>{{ $totalPhysical }}</th>
                                            <th style="background-color: {{ $totalDifference < 0 ? '#ffcccc' : 'transparent' }}">{{ $totalDifference }}</th>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- Page end  -->
            </div>
        </div>
    </div>
@endsection
