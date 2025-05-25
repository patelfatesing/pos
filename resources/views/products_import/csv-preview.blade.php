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
                                    <h4 class="card-title">Map CSV Fields to DB Fields</h4>
                                </div>
                                <div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="container">

                                    <form action="{{ route('csv.preview') }}" method="POST">
                                        @csrf

                                        <div class="row">

                                            <div class="col-md-3">
                                                <label> Map to DB Field</label>
                                            </div>

                                            <div class="col-md-3">
                                                <label> CSV Column</label>
                                            </div>
                                        </div>

                                        <input type="hidden" name="file_name" value="{{ $filename }}" />

                                        @if ($errors->has('mapping'))
                                            <div class="alert alert-danger">
                                                {{ $errors->first('mapping') }}
                                            </div>
                                        @endif

                                        @foreach ($dbFields as $field)
                                            <div class="row">
                                                <div class="col-md-3">
                                                    {{ $field }}
                                                </div>
                                                <div class="col-md-3 mt-1">
                                                    <select name="mapping[{{ $field }}]" class="form-control">
                                                        <option value="">-- Select Field --</option>
                                                        @foreach ($headers as $i => $header)
                                                            <option value="{{ $i }}">{{ $header }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        @endforeach

                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <button type="submit" id="submitBtn" class="btn btn-primary">Import
                                                    Data</button>
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
