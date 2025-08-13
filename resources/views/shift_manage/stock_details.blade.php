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
                            <h4 class="mb-3">🧾 Product Stock Summary - {{ $branch_name->name }}</h4>
                        </div>
                        <div>
                            <a href="{{ route('shift-manage.list') }}" class="btn btn-secondary">Back</a>
                        </div>
                    </div>
                </div>
                <div class="table-responsive rounded mb-3" id="shiftTableContainer">
                    <div class="card mt-4">
                        <!-- Page Header -->
                        <form method="GET" action="{{ route('shift-manage.stock-details', $shift->id) }}">
                            <div class="row ml-2 mt-2">
                                <div class="col-md-3 mb-2">
                                    <select name="subcategory_id" id="subcategory_id" class="form-control"
                                        onchange="this.form.submit()">
                                        <option value="">All Subcategories</option>
                                        @foreach ($subcategories as $subcategory)
                                            <option value="{{ $subcategory->id }}"
                                                {{ request('subcategory_id') == $subcategory->id ? 'selected' : '' }}>
                                                {{ $subcategory->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <input type="text" name="search" class="form-control" placeholder="Search Product"
                                        value="{{ request('search') }}">
                                </div>
                                <div class="col-md-1 mb-2">
                                    <a href="{{ route('shift-manage.stock-details', $shift->id) }}"
                                        class="btn btn-secondary">Reset</a>
                                </div>
                            </div>
                        </form>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product</th>
                                            <th>Category</th>
                                            <th>Opening Stock</th>
                                            <th>Added Stock</th>
                                            <th>Transferred Stock</th>
                                            <th>Sold Stock</th>
                                            <th>Closing Stock</th>
                                            <th>Physical Stock</th>
                                            <th>Difference Stock</th>
                                            <th>Modify Sale Add Stock</th>
                                            <th>Modify Sale Remove Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totalOpening = $totalAdded = $totalTransferred = $totalSold = $totalClosing = $totalPhysical = $totalDifference = $totalModifyAdd = $totalModifyRemove = 0;
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
                                                $totalModifyAdd += $stock->modify_sale_add_qty;
                                                $totalModifyRemove += $stock->modify_sale_remove_qty;
                                            @endphp
                                            <tr
                                                style="background-color: {{ $stock->difference_in_stock != 0 ? '#ffcccc' : 'transparent' }}">
                                                <td>{{ $stock->product->name ?? 'N/A' }}</td>
                                                <td>{{ $stock->product->subcategory->name ?? 'N/A' }}</td>
                                                <td class="text-center">{{ $stock->opening_stock }}</td>
                                                <td class="text-center">{{ $stock->added_stock }}</td>
                                                <td class="text-center">{{ $stock->transferred_stock }}</td>
                                                <td class="text-center">{{ $stock->sold_stock }}</td>
                                                <td class="text-center">{{ $stock->closing_stock }}</td>
                                                <td class="text-center">{{ $stock->physical_stock }}</td>
                                                <td class="text-center">
                                                    {{ $stock->difference_in_stock }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $stock->modify_sale_add_qty }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $stock->modify_sale_remove_qty }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">No stock data
                                                    available.</td>
                                            </tr>
                                        @endforelse

                                        <tr class="fw-semibold text-end text-center" style="font-size: 1.4rem;">
                                            <th colspan="2">TOTAL</th>
                                            <th>{{ $totalOpening }}</th>
                                            <th>{{ $totalAdded }}</th>
                                            <th>{{ $totalTransferred }}</th>
                                            <th>{{ $totalSold }}</th>
                                            <th>{{ $totalClosing }}</th>
                                            <th>{{ $totalPhysical }}</th>
                                            <th
                                                style="background-color: {{ $totalDifference != 0 ? '#ffcccc' : 'transparent' }}">
                                                {{ $totalDifference }}
                                            </th>
                                            <th>{{ $totalModifyAdd }}</th>
                                            <th>{{ $totalModifyRemove }}</th>
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
