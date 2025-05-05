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
                                    <h4 class="card-title">Import Product</h4>
                                </div>
                                <div>
                                    <a href="{{ route('products.list') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="container">
                                    <h2>Upload CSV File</h2>

                                    @if (session('success'))
                                        <div class="alert alert-success">{{ session('success') }}</div>
                                    @endif

                                    <form method="POST" action="{{ route('products.upload') }}"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="custom-file mb-3">
                                                    <input type="file" class="custom-file-input" id="file"
                                                        name="filename">
                                                    <label class="custom-file-label" for="file">Choose file</label>
                                                    @error('file')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <button class="btn btn-primary mt-2" type="submit">Upload</button>
                                    </form>
                                </div>

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
            $('#category_id').on('change', function() {

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

            // $('#sub_category_ids').on('change', function() {
            //     var categoryId = $(this).val();
            //     if (categoryId) {
            //         $.ajax({
            //             url: "{{ url('/products/getpacksize') }}/" + categoryId,
            //             type: "GET",
            //             dataType: "json",
            //             success: function(data) {
            //                 $('#pack_size').empty();
            //                 $('#pack_size').append(
            //                     '<option value="" disabled selected>Select pack size</option>'
            //                 );
            //                 $.each(data, function(key, value) {
            //                     $("#fate").text(value.name);
            //                     $('#pack_size').append('<option value="' + value
            //                         .size + '">' + value.size + '</option>');
            //                 });
            //             },
            //             error: function() {
            //                 alert('Failed to fetch subcategories. Please try again.');
            //             }
            //         });
            //     } else {
            //         $('#pack_size').empty();
            //         $('#pack_size').append(
            //             '<option value="" disabled selected>Select pack size</option>');
            //     }
            // });

        });
    </script>
@endsection
