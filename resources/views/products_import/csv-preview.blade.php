@extends('layouts.backend.layouts')

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid add-form-list">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <div class="header-title">
                                    <h4 class="card-title">Map CSV Fields to Database Fields</h4>
                                </div>
                                <div>
                                    <a href="{{ route('products.import') }}" class="btn btn-secondary">Back to Upload</a>
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
                                        @if ($errors->has('system_error'))
                                            <strong>System Error:</strong> {{ $errors->first('system_error') }}
                                        @elseif ($errors->has('file'))
                                            <strong>File Error:</strong> {{ $errors->first('file') }}
                                        @elseif ($errors->has('mapping'))
                                            <strong>Mapping Errors:</strong>
                                            @if (is_array($errors->first('mapping')))
                                                <ul class="mb-0">
                                                    @foreach ($errors->first('mapping') as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <p class="mb-0">{{ $errors->first('mapping') }}</p>
                                            @endif
                                        @elseif ($errors->has('data_validation'))
                                            <strong>Data Validation Errors:</strong>
                                            <ul class="mb-0">
                                                @foreach ($errors->get('data_validation') as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <ul class="mb-0">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                @endif

                                <div class="container">
                                    <form action="{{ route('products.process') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="file_name" value="{{ $filename }}" />

                                        <div class="row mb-4">
                                            <div class="col-12">
                                                <div class="alert alert-info">
                                                    <h5 class="alert-heading">Field Mapping Instructions</h5>
                                                    <p class="mb-0">
                                                        Please map each database field to the corresponding column in your
                                                        CSV file.
                                                        Fields marked with <span class="text-danger">*</span> are required.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label"><strong>Database Field</strong></label>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label"><strong>CSV Column</strong></label>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label"><strong>Description</strong></label>
                                            </div>
                                        </div>

                                        @foreach ($dbFields as $key => $field)
                                            <div class="row mb-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">
                                                        {{ $field }}
                                                        @if (in_array($field, [
                                                                'name',
                                                                'barcode',
                                                                'batch_number',
                                                                'category',
                                                                'sub_category',
                                                                'cost_price',
                                                                'selling_price',
                                                                'Minimum_stock_level',
                                                            ]))
                                                            <span class="text-danger">*</span>
                                                        @endif
                                                    </label>
                                                </div>
                                                <div class="col-md-4">
                                                    <select name="mapping[{{ $field }}]"
                                                        class="form-control form-select @error('mapping.' . $field) is-invalid @enderror">
                                                        <option value="">-- Select Column --</option>
                                                        @foreach ($headers as $i => $header)
                                                            <option value="{{ $i }}" {{ $errors->any() && old('mapping.' . $field) == $i ? 'selected' : '' }}>
                                                                {{ $header }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('mapping.' . $field)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-4">
                                                    <p class="text-muted mb-0">
                                                        @switch($field)
                                                            @case('name')
                                                                Product name
                                                            @break

                                                            @case('category')
                                                                Must match existing category name
                                                            @break

                                                            @case('sub_category')
                                                                Must match existing sub-category name
                                                            @break

                                                            @case('cost_price')
                                                                Product cost price (numeric)
                                                            @break

                                                            @case('selling_price')
                                                                Product selling price (numeric)
                                                            @break

                                                            @case('Minimum_stock_level')
                                                                Minimum stock level (numeric)
                                                            @break

                                                            @case('Mfg_date')
                                                                Manufacturing date (DD-MM-YYYY)
                                                            @break

                                                            @case('Expiry_date')
                                                                Expiry date (DD-MM-YYYY)
                                                            @break

                                                            @default
                                                                {{ $field }}
                                                        @endswitch
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach

                                        <div class="row mt-4">
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-primary">
                                                    Validate & Continue
                                                </button>
                                                <button type="reset" class="btn btn-danger">Reset</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
