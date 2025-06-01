@extends('layouts.backend.layouts')

@section('page-content')
    <!-- Wrapper Start -->
    <div class="wrapper">

        <div class="content-page">
            <div class="container-fluid add-form-list">
                <div class="row">
                    <div class="col-sm-12 col-lg-12">
                        <div class="iq-card">
                            <div class="iq-card-header d-flex justify-content-between">
                                <div class="iq-header-title">
                                    <h4 class="card-title">Create Demand Order</h4>
                                </div>
                            </div>
                            <div class="iq-card-body">
                                <form action="{{ route('demand-order.step2') }}" id="productForm" method="POST">
                                    @csrf

                                    <input type="hidden" name="demand_date" value="{{ @$demand_date }}">
                                    <ul id="top-tab-list" class="p-0">
                                        <li id="account">
                                            <a href="javascript:void();">
                                                <i class="ri-lock-unlock-line"></i><span>Search Details</span>
                                            </a>
                                        </li>
                                        <li class="active" id="personal">
                                            <a href="javascript:void();">
                                                <i class="ri-user-fill"></i><span>Prediction</span>
                                            </a>
                                        </li>
                                        <li id="payment">
                                            <a href="javascript:void();">
                                                <i class="ri-file-text-line"></i><span>Final Select</span>
                                            </a>
                                        </li>
                                        <li id="confirm">
                                            <a href="javascript:void();">
                                                <i class="ri-check-fill"></i><span>Finish</span>
                                            </a>
                                        </li>
                                    </ul>
                                    <!-- fieldsets -->

                                    <fieldset>
                                        <div class="form-card text-left">
                                            <div class="row">
                                                <div class="col-7">
                                                    <h3 class="mb-4">Prediction:</h3>
                                                </div>
                                                <div class="col-5">
                                                    <h2 class="steps">Step 2 - 4</h2>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <table class="table table-bordered">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th class="py-2 text-left text-sm font-medium text-gray-700">
                                                                <input type="checkbox" id="select-all"
                                                                    class="form-checkbox mr-1"> Select
                                                            </th>
                                                            <th class="text-left text-sm font-medium text-gray-700">Product
                                                            </th>
                                                            <th class="text-left text-sm font-medium text-gray-700">Category
                                                            </th>
                                                            <th class="text-left text-sm font-medium text-gray-700">Sub
                                                                Category</th>
                                                            <th class="text-left text-sm font-medium text-gray-700">Size
                                                            </th>
                                                            <th class="text-right text-sm font-medium text-gray-700">Current
                                                                Stock</th>
                                                            <th class="text-right text-sm font-medium text-gray-700">Low
                                                                Level Stock</th>
                                                            <th class="text-right text-sm font-medium text-gray-700">Weekly
                                                                Sales</th>
                                                            <th class="text-right text-sm font-medium text-gray-700">Avg
                                                                Daily</th>
                                                            <th class="text-right text-sm font-medium text-gray-700">Delivery Pending
                                                            </th>
                                                            <th class="text-right text-sm font-medium text-gray-700">
                                                                Suggested Qty</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                    @php
                                                    $selectedProducts = session('demand_orders.step2', []); // assuming session holds array of product IDs
                                                    @endphp

                                                        @foreach ($predictions as $p)
                                                                @php
                                                            $selectCheck=(!empty($selectedProducts['selected']) && in_array($p['product_id'], $selectedProducts['selected']) )? 'checked' : '' ;
                                                            @endphp
                                                            <tr>
                                                                <td class="px-4 py-2">
                                                                    <input type="checkbox" name="selected[]"
                                                                        value="{{ $p['product_id'] }}"
                                                                        class="form-checkbox product-checkbox"   {{ $selectCheck }}>
                                                                        
                                                                </td>
                                                                <td class="px-4 py-2 text-sm text-gray-800">
                                                                    {{ $p['name'] }}</td>
                                                                <td class="px-4 py-2 text-sm text-gray-800">
                                                                    {{ $p['category_name'] }}</td>
                                                                <td class="px-4 py-2 text-sm text-gray-800">
                                                                    {{ $p['subcategory_name'] }}</td>
                                                                <td class="px-4 py-2 text-sm text-gray-800">
                                                                    {{ $p['size'] }}</td>
                                                                <td class="px-4 py-2 text-sm text-gray-800 text-right">
                                                                    {{ $p['current_stock'] }}</td>
                                                                <td class="px-4 py-2 text-sm text-gray-800 text-right">
                                                                    {{ $p['reorder_level'] }}</td>
                                                                <td class="px-4 py-2 text-sm text-gray-800 text-right">
                                                                    {{ $p['weekly_sales'] }}</td>
                                                                <td class="px-4 py-2 text-sm text-gray-800 text-right">
                                                                    {{ $p['avg_daily'] }}</td>
                                                                <td class="px-4 py-2 text-sm text-gray-800 text-right">
                                                                    {{ $p['pending'] }}</td>
                                                                <td class="px-4 py-2">
                                                                    <input type="number"
                                                                        name="order_qty[{{ $p['product_id'] }}]"
                                                                        value="{{ old('order_qty.' . $p['product_id'], $selectedProducts['order_qty'][$p['product_id']] ?? $p['suggested_order_quantity']) }}"
                                                                        min="0"
                                                                        class="w-20 border rounded p-1 text-right">
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>

                                            </div>
                                        </div>
                                        <!-- Submit to go to next step -->
                                        <button type="submit"
                                            class="btn btn-primary next action-button float-right">Next</button>

                                        <!-- Use a link to go to the previous step -->
                                        <a href="{{ route('demand-order.step1') }}"
                                            class="btn btn-dark previous action-button-previous float-right mr-3">Previous</a>

                                    </fieldset>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Wrapper End -->
@endsection

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
 
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        const selectAllCheckbox = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.product-checkbox');

        function updateSelectAllState() {
            const total = checkboxes.length;
            const checked = Array.from(checkboxes).filter(cb => cb.checked).length;

            if (checked === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            } else if (checked === total) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else {
                selectAllCheckbox.indeterminate = true;
            }
        }

        selectAllCheckbox.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateSelectAllState);
        });

        updateSelectAllState();
    });
</script>
<script>
   
</script>
