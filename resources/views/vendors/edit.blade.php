@extends('layouts.backend.layouts')

@section('page-content')
    <div class="content-page">
        <div class="container-fluid add-form-list">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between mb-3">
                <div>
                    <h4 class="mb-0">Edit Vendor</h4>
                </div>
                <a href="{{ route('vendor.list') }}" class="btn btn-secondary">Back</a>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">

                        <div class="card-body">
                            <form id="vendorForm" action="{{ route('vendor.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{ old('name', $vendor->id) }}" />
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="floating-label form-group">
                                            <label>First Name</label>
                                            <input type="text" name="name" class="form-control"
                                                value="{{ old('name', $vendor->name) }}">
                                            @error('name')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Vendor Type</label>

                                            <div>
                                                <label>
                                                    <input type="radio" name="type" value="local"
                                                        {{ old('type', $vendor->type ?? 'local') == 'local' ? 'checked' : '' }}>
                                                    Local
                                                </label>

                                                <label class="ml-3">
                                                    <input type="radio" name="type" value="main"
                                                        {{ old('type', $vendor->type) == 'main' ? 'checked' : '' }}>
                                                    Main
                                                </label>
                                            </div>

                                            @error('type')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="floating-label form-group">
                                            <label>Email</label>
                                            <input type="email" name="email" class="form-control"
                                                value="{{ old('email', $vendor->email) }}">
                                            @error('email')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="floating-label form-group">
                                            <label>Phone</label>
                                            <input type="text" name="phone" class="form-control"
                                                value="{{ old('phone', $vendor->phone) }}">
                                            @error('phone')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="floating-label form-group">
                                            <label>GST Number</label>
                                            <input type="text" name="gst_number" class="form-control"
                                                value="{{ old('gst_number', $vendor->gst_number) }}">
                                            @error('gst_number')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Address</label>
                                            <textarea class="form-control" name="address" rows="2">{{ $vendor->address }}</textarea>
                                            @error('address')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <button type="button" onclick="submitForm()" class="btn btn-primary">
                                    Update Vendor
                                </button>
                                <button type="button" onclick="resetForm()" class="btn btn-danger">
                                    Reset
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Page end -->
        </div>
    </div>
    <script>
        function submitForm() {
            document.getElementById('vendorForm').submit();
        }

        function resetForm() {
            const form = document.getElementById('vendorForm');

            // Reset all fields
            form.reset();

            // Reset radio default (local)
            const localRadio = form.querySelector('input[name="type"][value="local"]');
            if (localRadio) {
                localRadio.checked = true;
            }
        }
    </script>
@endsection
