@extends('layouts.backend.layouts')

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
                                    <h4 class="card-title">Demand Order Predictions</h4>
                                </div>
                                <div>
                                    <a href="{{ route('purchase.list') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="card">
                                    <form action="" method="POST">
                                        @csrf

                                        <table class="table table-striped">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="py-2 text-left text-sm font-medium text-gray-700">
                                                        Select</th>
                                                    <th class=" text-left text-sm font-medium text-gray-700">
                                                        Product</th>
                                                    <th class=" text-left text-sm font-medium text-gray-700">
                                                        Category</th>
                                                    <th class=" text-left text-sm font-medium text-gray-700">
                                                        Sub Category</th>
                                                    <th class="text-left text-sm font-medium text-gray-700">
                                                        Size</th>
                                                    <th class="text-right text-sm font-medium text-gray-700">
                                                        Current Stock</th>
                                                    <th class="text-right text-sm font-medium text-gray-700">
                                                        Reorder Level</th>
                                                    <th class="text-right text-sm font-medium text-gray-700">
                                                        Weekly Sales</th>
                                                    <th class="text-right text-sm font-medium text-gray-700">
                                                        Monthly Sales</th>
                                                    <th class="text-right text-sm font-medium text-gray-700">
                                                        Avg Daily</th>
                                                    <th class="text-right text-sm font-medium text-gray-700">
                                                        Pending</th>
                                                    <th class="text-right text-sm font-medium text-gray-700">
                                                        Suggested Qty</th>

                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach ($predictions as $p)
                                                    <tr>
                                                        <td class="px-4 py-2">
                                                            <input type="checkbox" name="selected[]"
                                                                value="{{ $p['product_id'] }}" class="form-checkbox"
                                                                checked>
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-800">
                                                            {{ $p['name'] }}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-800">
                                                            {{ $p['category_name'] }}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-800">
                                                            {{ $p['subcategory_name'] }}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-800">
                                                            {{ $p['size'] }}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-800 text-right">
                                                            {{ $p['current_stock'] }}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-800 text-right">
                                                            {{ $p['reorder_level'] }}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-800 text-right">
                                                            {{ $p['weekly_sales'] }}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-800 text-right">
                                                            {{ $p['monthly_sales'] }}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-800 text-right">
                                                            {{ $p['avg_daily'] }}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-800 text-right">
                                                            {{ $p['pending'] }}
                                                        </td>
                                                        <td class="px-4 py-2">
                                                            <input type="number" name="order_qty[{{ $p['product_id'] }}]"
                                                                value="{{ $p['suggested_order_quantity'] }}" min="0"
                                                                class="w-20 border rounded p-1 text-right">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <div class="row">
                                            <div class="col-md-1"></div>
                                            <div class="col-md-4 mb-3">
                                            <button type="submit"
                                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Generate
                                                Demand Orders</button>
                                            </div>
                                        </div>

                                    </form>

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
