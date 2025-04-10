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
                                    <h4 class="card-title">Stock Request</h4>
                                </div>
                                <div>
                                    <a href="{{ route('products.list') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </div>

                            <div class="card-body">

                                <form method="POST" action="{{ route('stock.store') }}">
                                    @csrf

                                    <div class="mb-3">
                                        <label for="store_id" class="form-label">Select Store</label>
                                        <select name="store_id" id="store_id" class="form-control" required>
                                            <option value="">-- Select Store --</option>
                                            @foreach ($stores as $store)
                                                <option value="{{ $store->id }}">{{ $store->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div id="product-items">
                                        <h5>Products</h5>
                                        <div class="item-row mb-3">
                                            <select name="items[0][product_id]" class="form-control d-inline w-50" required>
                                                <option value="">-- Select Product --</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}">{{ $product->name }}
                                                        ({{ $product->sku }})</option>
                                                @endforeach
                                            </select>
                                            <input type="number" name="items[0][quantity]"
                                                class="form-control d-inline w-25 ms-2" placeholder="Qty" min="1"
                                                required>
                                            <button type="button" class="btn btn-danger btn-sm ms-2 remove-item">X</button>
                                        </div>
                                    </div>

                                    <button type="button" id="add-item" class="btn btn-secondary btn-sm mb-3">+ Add
                                        Another Product</button>

                                    <div class="mb-3">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea name="notes" id="notes" class="form-control"></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary">Submit Request</button>
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
        document.getElementById('add-item').addEventListener('click', function() {
            const row = document.querySelector('.item-row').cloneNode(true);
            row.querySelectorAll('select, input').forEach(el => {
                const name = el.getAttribute('name');
                const updatedName = name.replace(/\[\d+\]/, `[${itemIndex}]`);
                el.setAttribute('name', updatedName);
                if (el.tagName === 'INPUT') el.value = '';
            });
            document.getElementById('product-items').appendChild(row);
            itemIndex++;
        });

        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-item')) {
                if (document.querySelectorAll('.item-row').length > 1) {
                    e.target.closest('.item-row').remove();
                }
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#categorys').on('change', function() {
                var categoryId = $(this).val();
                if (categoryId) {
                    $.ajax({
                        url: "{{ url('/products/subcategory') }}/" + categoryId,
                        type: "GET",
                        dataType: "json",
                        success: function(data) {
                            $('#sub_category_ids').empty();
                            $('#sub_category_ids').append(
                                '<option value="" disabled selected>Select Sub Category</option>'
                            );
                            $.each(data, function(key, value) {
                                $("#fate").text(value.name);
                                $('#sub_category_ids').append('<option value="' + value
                                    .id + '">' + value.name + '</option>');
                            });
                        },
                        error: function() {
                            alert('Failed to fetch subcategories. Please try again.');
                        }
                    });
                } else {
                    $('#sub_category_ids').empty();
                    $('#sub_category_ids').append(
                        '<option value="" disabled selected>Select Sub Category</option>');
                }
            });


            $('#sub_category_ids').on('change', function() {
                var categoryId = $(this).val();
                if (categoryId) {
                    $.ajax({
                        url: "{{ url('/products/getpacksize') }}/" + categoryId,
                        type: "GET",
                        dataType: "json",
                        success: function(data) {
                            $('#pack_size').empty();
                            $('#pack_size').append(
                                '<option value="" disabled selected>Select pack size</option>'
                            );
                            $.each(data, function(key, value) {
                                $("#fate").text(value.name);
                                $('#pack_size').append('<option value="' + value
                                    .id + '">' + value.size + '</option>');
                            });
                        },
                        error: function() {
                            alert('Failed to fetch subcategories. Please try again.');
                        }
                    });
                } else {
                    $('#pack_size').empty();
                    $('#pack_size').append(
                        '<option value="" disabled selected>Select pack size</option>');
                }
            });

        });
    </script>
@endsection
