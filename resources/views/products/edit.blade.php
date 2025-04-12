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
                                    <h4 class="card-title">Update Product</h4>
                                </div>
                                <div>
                                    <a href="{{ route('products.list') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </div>

                            <div class="card-body">
                                <form action="{{ route('products.store') }}" enctype="multipart/form-data" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Name *</label>
                                                <input type="text" name="name" class="form-control"
                                                    value="{{ old('name', $record->name) }}" placeholder="Enter Name" data-errors="Please Enter Name.">
                                                @error('name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Brand *</label>
                                                <input type="text" name="brand" class="form-control"
                                                    value="{{ old('brand', $record->brand) }}" placeholder="Enter Brand" data-errors="Please Enter Brand.">
                                                @error('brand')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Category *</label>
                                                <select name="category_id" id="categorys" class="selectpicker form-control" data-style="py-0">
                                                    <option value="" disabled>Select Category</option>
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}" {{ old('category_id', $record->category_id) == $category->id ? 'selected' : '' }}>
                                                            {{ $category->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('category_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Sub Category *</label>
                                                <select id="sub_category_ids" name="subcategory_id" class="form-control" data-style="py-0">
                                                    <option value="" disabled>Select Sub Category</option>
                                                    @foreach ($subcategories as $subcategory)
                                                        <option value="{{ $subcategory->id }}" {{ old('subcategory_id', $record->subcategory_id) == $subcategory->id ? 'selected' : '' }}>
                                                            {{ $subcategory->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('subcategory_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Pack Size *</label>
                                                <select id="pack_size" name="size" class="form-control" data-style="py-0">
                                                    <option value="" disabled>Select Pack Size</option>
                                                    @foreach ($packSizes as $size)
                                                        <option value="{{ $size->packSizes }}" {{ old('size', $record->size) == $size ? 'selected' : '' }}>
                                                            {{ $size->size }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('size')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>SKU *</label>
                                                <input type="text" name="code" class="form-control" disabled
                                                    value="{{ old('code', $record->sku) }}" placeholder="Enter Code" data-errors="Please Enter Code.">
                                                @error('code')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Barcode</label>
                                               <p> <img src="{{ route('barcode.generate', ['productCode' => $productCode]) }}" alt="Barcode"></p>
                                                <p>{{ str_replace('-', '', $record->sku) }}</p>
                                            </div>
                                        </div>                                         
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Description / Product Details</label>
                                                <textarea class="form-control" name="description" rows="4">{{ old('description', $record->description) }}</textarea>
                                            </div>
                                        </div>

                                    </div>
                                    <button type="submit" class="btn btn-primary mr-2">Update Product</button>
                                    <button type="reset" class="btn btn-danger">Reset</button>
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
                                    .size + '">' + value.size + '</option>');
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
