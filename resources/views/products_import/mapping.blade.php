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
                                <div class="container">
                                    <h2>Map Fields</h2>

                                    <form method="POST" action="{{ route('products.import') }}">
                                        @csrf

                                        <div class="form-group">
                                            <label>Product Name</label>
                                            <select name="mapping[name]" class="form-control" required>
                                                @foreach ($headings as $heading)
                                                    <option value="{{ $heading }}">{{ $heading }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>SKU</label>
                                            <select name="mapping[sku]" class="form-control" required>
                                                @foreach ($headings as $heading)
                                                    <option value="{{ $heading }}">{{ $heading }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Price</label>
                                            <select name="mapping[price]" class="form-control" required>
                                                @foreach ($headings as $heading)
                                                    <option value="{{ $heading }}">{{ $heading }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Stock</label>
                                            <select name="mapping[stock]" class="form-control" required>
                                                @foreach ($headings as $heading)
                                                    <option value="{{ $heading }}">{{ $heading }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <button type="submit" id="submitBtn"
                                                    class="btn btn-primary">Upload</button>
                                                <button type="reset" class="btn btn-danger">Reset</button>
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
    <!-- Wrapper End-->

    {{-- AJAX for Category/Subcategory --}}
    <script>
        $(document).ready(function() {
            $('#categorys').on('change', function() {
                var categoryId = $(this).val();
                if (categoryId) {
                    $.get("{{ url('/products/subcategory') }}/" + categoryId, function(data) {
                        $('#sub_category_ids').empty().append(
                            '<option value="">Select Sub Category</option>');
                        $.each(data, function(i, val) {
                            $('#sub_category_ids').append('<option value="' + val.id +
                                '">' + val.name + '</option>');
                        });
                    });
                }
            });

            $('#sub_category_ids').on('change', function() {
                var subCatId = $(this).val();
                if (subCatId) {
                    $.get("{{ url('/products/getpacksize') }}/" + subCatId, function(data) {
                        $('#pack_size').empty().append(
                            '<option value="">Select Pack Size</option>');
                        $.each(data, function(i, val) {
                            $('#pack_size').append('<option value="' + val.size + '">' + val
                                .size + '</option>');
                        });
                    });
                }
            });
        });
    </script>
@endsection
