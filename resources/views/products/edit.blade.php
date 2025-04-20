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
                                <h4 class="card-title">Update Product</h4>
                            </div>
                            <div>
                                <a href="{{ route('products.list') }}" class="btn btn-secondary">Back</a>
                            </div>
                        </div>

                        <div class="card-body">
                            <form action="{{ route('products.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <input type="hidden" name="id" value="{{$record->id}}">
                                    {{-- Name --}}
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Name *</label>
                                            <input type="text" name="name" class="form-control"
                                                value="{{ old('name', $record->name) }}" placeholder="Enter Name">
                                            @error('name')<span class="text-danger">{{ $message }}</span>@enderror
                                        </div>
                                    </div>

                                    {{-- Brand --}}
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Brand *</label>
                                            <input type="text" name="brand" class="form-control"
                                                value="{{ old('brand', $record->brand) }}" placeholder="Enter Brand">
                                            @error('brand')<span class="text-danger">{{ $message }}</span>@enderror
                                        </div>
                                    </div>

                                    {{-- Category --}}
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Category *</label>
                                            <select name="category_id" id="categorys" class="form-control">
                                                <option value="" disabled>Select Category</option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}"
                                                        {{ old('category_id', $record->category_id) == $category->id ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('category_id')<span class="text-danger">{{ $message }}</span>@enderror
                                        </div>
                                    </div>

                                    {{-- Sub Category --}}
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Sub Category *</label>
                                            <select id="sub_category_ids" name="subcategory_id" class="form-control">
                                                <option value="" disabled>Select Sub Category</option>
                                                @foreach ($subcategories as $subcategory)
                                                    <option value="{{ $subcategory->id }}"
                                                        {{ old('subcategory_id', $record->subcategory_id) == $subcategory->id ? 'selected' : '' }}>
                                                        {{ $subcategory->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('subcategory_id')<span class="text-danger">{{ $message }}</span>@enderror
                                        </div>
                                    </div>

                                    {{-- Pack Size --}}
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Pack Size *</label>
                                            <select id="size" name="size" class="form-control">
                                                <option value="" disabled>Select Pack Size</option>
                                                @foreach ($packSizes as $size)
                                                    <option value="{{ $size->size }}"
                                                        {{ old('size', $record->size) == $size->size ? 'selected' : '' }}>
                                                        {{ $size->size }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('size')<span class="text-danger">{{ $message }}</span>@enderror
                                        </div>
                                    </div>

                                    {{-- SKU --}}
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>SKU *</label>
                                            <input type="text" name="sku" class="form-control" value="{{ old('sku', $record->sku) }}" disabled>
                                            @error('sku')<span class="text-danger">{{ $message }}</span>@enderror
                                        </div>
                                    </div>

                                    {{-- Barcode --}}
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Barcode</label>
                                            <input type="text" name="barcode" class="form-control" value="{{ old('barcode', $record->barcode) }}" disabled>
                                            @error('barcode')<span class="text-danger">{{ $message }}</span>@enderror
                                        </div>
                                    </div>

                                    {{-- Reorder Level --}}
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Reorder Level</label>
                                            <input type="number" name="reorder_level" value="{{ old('reorder_level', $record->reorder_level) }}" class="form-control" placeholder="Enter Reorder Level">
                                            @error('reorder_level')<span class="text-danger">{{ $message }}</span>@enderror
                                        </div>
                                    </div>

                                    {{-- Image Upload --}}
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Image</label>
                                            <input type="file" name="image" class="form-control image-file" accept="image/*">
                                            @if($record->image)
                                                <img src="{{ asset('storage/'.$record->image) }}" alt="Product Image" class="img-thumbnail mt-2" style="height: 100px;">
                                            @endif
                                            @error('image')<span class="text-danger">{{ $message }}</span>@enderror
                                        </div>
                                    </div>

                                    {{-- Cost Price --}}
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Cost Price *</label>
                                            <input type="number" step="0.01" name="cost_price" class="form-control" value="{{ old('cost_price', $record->cost_price) }}">
                                            @error('cost_price')<span class="text-danger">{{ $message }}</span>@enderror
                                        </div>
                                    </div>

                                    {{-- Sell Price --}}
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Sell Price *</label>
                                            <input type="number" step="0.01" name="sell_price" class="form-control" value="{{ old('sell_price', $record->sell_price) }}">
                                            @error('sell_price')<span class="text-danger">{{ $message }}</span>@enderror
                                        </div>
                                    </div>

                                    {{-- Discount Price --}}
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Discount Price</label>
                                            <input type="number" step="0.01" name="discount_price" class="form-control" value="{{ old('discount_price', $record->discount_price) }}">
                                            @error('discount_price')<span class="text-danger">{{ $message }}</span>@enderror
                                        </div>
                                    </div>

                                    {{-- Description --}}
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Description / Product Details</label>
                                            <textarea class="form-control" name="description" rows="4">{{ old('description', $record->description) }}</textarea>
                                            @error('description')<span class="text-danger">{{ $message }}</span>@enderror
                                        </div>
                                    </div>

                                </div>

                                {{-- Buttons --}}
                                <button type="submit" class="btn btn-primary mr-2">Update Product</button>
                                <button type="reset" class="btn btn-danger">Reset</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Page end -->
        </div>
    </div>
</div>
<!-- Wrapper End-->

{{-- AJAX for Category/Subcategory --}}
<script>
    $(document).ready(function() {
        $('#categorys').on('change', function() {
            var categoryId = $(this).val();
            if (categoryId) {
                $.get("{{ url('/products/subcategory') }}/" + categoryId, function(data) {
                    $('#sub_category_ids').empty().append('<option value="">Select Sub Category</option>');
                    $.each(data, function(i, val) {
                        $('#sub_category_ids').append('<option value="' + val.id + '">' + val.name + '</option>');
                    });
                });
            }
        });

        $('#sub_category_ids').on('change', function() {
            var subCatId = $(this).val();
            if (subCatId) {
                $.get("{{ url('/products/getpacksize') }}/" + subCatId, function(data) {
                    $('#pack_size').empty().append('<option value="">Select Pack Size</option>');
                    $.each(data, function(i, val) {
                        $('#pack_size').append('<option value="' + val.size + '">' + val.size + '</option>');
                    });
                });
            }
        });
    });
</script>
@endsection
