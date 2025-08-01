@extends('layouts.backend.layouts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                                    <h4 class="card-title">First Time Add Stocks</h4>
                                </div>
                            </div>

                            <div class="card-body">
                                <!-- Page Header -->
                                <form method="GET" action="{{ route('products.add-stocks') }}">
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
                                      
                                        <div class="col-md-1 mb-2">
                                            <a href="{{ route('products.add-stocks') }}"
                                                class="btn btn-secondary">Reset</a>
                                        </div>
                                    </div>
                                </form>
                                <form action="{{ route('products.import.stocks') }}" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Select Store *</label>
                                                <select name="from_store_id" id="from_store_id"
                                                    class="selectpicker form-control" data-style="py-0">
                                                    <option value="" disabled selected>Select Store</option>
                                                    @foreach ($stores as $category)
                                                        <option value="{{ $category->id }}"
                                                            {{ old('from_store_id') == $category->id ? 'selected' : '' }}>
                                                            {{ $category->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('from_store_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-bordered" id="stock-table">
                                                <thead>
                                                    <tr>
                                                        <th>Sr No</th>
                                                        <th>Product</th>
                                                        <th>Quantity</th>
                                                        <th>Action</th> <!-- 🆕 New column for remove button -->
                                                    </tr>
                                                </thead>
                                                <tbody id="stock-table-body">
                                                    @php
                                                        $oldItems = old('items');
                                                    @endphp

                                                    @if ($oldItems)
                                                        @foreach ($oldItems as $key => $item)
                                                            @php
                                                                $product = \App\Models\Product::find(
                                                                    $item['product_id'],
                                                                );
                                                            @endphp
                                                            @if ($product)
                                                                <tr class="item-row">
                                                                    <td>{{ $key + 1 }}</td>
                                                                    <td>{{ $product->name }}</td>
                                                                    <td>
                                                                        @php
                                                                            $quantityName = "items.{$product->id}.quantity";
                                                                        @endphp

                                                                        <input type="number"
                                                                            name="items[{{ $product->id }}][quantity]"
                                                                            class="form-control @error($quantityName) is-invalid @enderror"
                                                                            value="{{ old("items.{$product->id}.quantity") }}"
                                                                            min="1" placeholder="Enter quantity">

                                                                        @error($quantityName)
                                                                            <span
                                                                                class="text-danger">{{ $message }}</span>
                                                                        @enderror
                                                                        <input type="hidden"
                                                                            name="items[{{ $product->id }}][product_id]"
                                                                            value="{{ $product->id }}">

                                                                    </td>
                                                                    <td>
                                                                        <button type="button"
                                                                            class="btn btn-danger btn-sm remove-item">Remove</button>
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        @forelse($products as $key => $product)
                                                            <tr class="item-row">
                                                                <td>{{ $key + 1 }}</td>
                                                                <td>{{ $product->name }}</td>
                                                                <td>
                                                                    <input type="number"
                                                                        name="items[{{ $product->id }}][quantity]"
                                                                        class="form-control" min="1"
                                                                        placeholder="Enter quantity">
                                                                    <input type="hidden"
                                                                        name="items[{{ $product->id }}][product_id]"
                                                                        value="{{ $product->id }}">
                                                                </td>
                                                                <td>
                                                                    <button type="button"
                                                                        class="btn btn-danger btn-sm remove-item">Remove</button>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="3" class="text-center">✅ No available
                                                                    products
                                                            </tr>
                                                        @endforelse
                                                    @endif

                                                </tbody>
                                            </table>

                                            <button type="submit" class="btn btn-primary">Add Stocs</button> <!-- 🆕 -->
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <strong>Total Quantity:</strong> <span id="total-quantity">0</span>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- Page end  -->
            </div>
        </div>
    </div>
    <!-- Wrapper End-->

    <script>
        let itemIndex = 1;

        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-item')) {
                if (document.querySelectorAll('.item-row').length > 1) {
                    e.target.closest('.item-row').remove();
                    calculateTotalQuantity();
                }
            }
        });

        function calculateTotalQuantity() {
            let total = 0;
            document.querySelectorAll('input[name*="[quantity]"]').forEach(input => {
                const val = parseInt(input.value) || 0;
                total += val;
            });
            document.getElementById('total-quantity').textContent = total;
        }

        // Update total when quantity changes
        document.addEventListener('input', function(e) {
            if (e.target && e.target.name.includes('[quantity]')) {
                calculateTotalQuantity();
            }
        });

        // Calculate total on page load (to support old values after validation)
        window.addEventListener('DOMContentLoaded', calculateTotalQuantity);
    </script>
@endsection
