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
                                     </div>
                            </div>

                            <div class="card-body">
                                <div class="container">
                                    <h2>Map Fields</h2>

                                    <h4>Map CSV Fields to DB Fields</h4>

                                    <form action="{{ route('csv.preview') }}" method="POST">
                                        @csrf

                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>CSV Column</th>
                                                    <th>Map to DB Field</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($headers as $i => $header)
                                                    <tr>
                                                        <td>{{ $header }}</td>
                                                        <td>
                                                            <select name="mapping[{{ $header }}]"
                                                                class="form-control">
                                                                <option value="">-- Select Field --</option>
                                                                @foreach ($dbFields as $field)
                                                                    <option value="{{ $field }}">{{ $field }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>

                                        <button type="submit">Import</button>
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
