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
                                    <h4 class="card-title">Import Products</h4>
                                </div>
                                <div>
                                    <a href="{{ route('products.download-sample') }}" class="btn btn-success">
                                        <i class="fas fa-download"></i> Download Sample
                                    </a>
                                    <a href="{{ route('products.list') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back
                                    </a>
                                </div>
                            </div>

                            <div class="card-body">
                                @if (session('success'))
                                    <div class="alert alert-success alert-dismissible fade show">
                                        {{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                @endif

                                @if (session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show">
                                        {{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                @endif

                                @if ($errors->any())
                                    <div class="alert alert-danger alert-dismissible fade show">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                @endif

                                <div class="container">
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <div class="alert alert-info">
                                                <h5 class="alert-heading">Import Instructions</h5>
                                                <ol class="mb-0">
                                                    <li>Download the sample file to see the required format</li>
                                                    <li>Prepare your CSV file with the same column structure</li>
                                                    <li>Make sure all required fields are filled</li>
                                                    <li>Upload your file and verify the field mapping</li>
                                                </ol>
                                            </div>
                                        </div>
                                    </div>

                                    <form method="POST" action="{{ route('products.upload') }}"
                                        enctype="multipart/form-data" id="importForm">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <input type="file" class="custom-file-input" id="file"
                                                        name="file">
                                                    <label class="custom-file-label" for="file">Choose file</label>

                                                    @error('file')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <small class="form-text text-muted">
                                                        Accepted formats: CSV
                                                    </small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <button class="btn btn-primary" type="submit" id="submitBtn">
                                                    <i class="fas fa-upload"></i> Upload & Continue
                                                </button>
                                            </div>
                                        </div>
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
            // File input change handler
            $('#file').on('change', function() {
                const file = this.files[0];
                if (file) {
                    // Validate file type
                    const validTypes = ['text/csv', 'text/plain'];
                    if (!validTypes.includes(file.type)) {
                        alert('Please select a valid CSV or TXT file.');
                        this.value = '';
                        return;
                    }

                    // Validate file size (10MB)
                    const maxSize = 10 * 1024 * 1024; // 10MB in bytes
                    if (file.size > maxSize) {
                        alert('File size must be less than 10MB.');
                        this.value = '';
                        return;
                    }
                }
            });

            // Form submit handler
            $('#importForm').on('submit', function(e) {
                const fileInput = $('#file')[0];
                if (!fileInput.files || !fileInput.files[0]) {
                    e.preventDefault();
                    alert('Please select a file to upload.');
                    return false;
                }
            });

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
