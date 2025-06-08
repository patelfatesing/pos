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
                                    <h4 class="card-title">Add Commission Customer</h4>
                                </div>
                                <div>
                                    <a href="{{ route('commission-users.list') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('commission-users.store') }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Customer Name</label>
                                                <input type="text" name="first_name" class="form-control"
                                                    value="{{ old('first_name') }}">
                                                @error('first_name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Commission Type</label>
                                                <select name="commission_type" class="form-control">
                                                    <option value="fixed"
                                                        {{ old('commission_type') == 'fixed' ? 'selected' : '' }}>Fixed
                                                    </option>
                                                </select>
                                                @error('commission_type')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Email</label>
                                                <input type="email" name="email" class="form-control"
                                                    value="{{ old('email') }}">
                                                @error('email')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Applies To</label>
                                                <select name="applies_to" class="form-control">
                                                    <option value="product"
                                                        {{ old('applies_to') == 'product' ? 'selected' : '' }}>Product
                                                    </option>
                                                </select>
                                                @error('applies_to')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Reference Name</label>
                                                <input type="text" name="reference_id" class="form-control"
                                                    value="{{ old('reference_id') }}">
                                                @error('reference_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Active</label>
                                                <select name="is_active" class="form-control">
                                                    <option value="1" {{ old('is_active') == '1' ? 'selected' : '' }}>
                                                        Yes</option>
                                                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>
                                                        No</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>Upload Image</label>
                                                <input type="file" name="image" class="form-control"
                                                    onchange="previewImage(this)">
                                                @error('image')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                                <div id="imagePreview" class="mt-2"></div>
                                            </div>
                                        </div>

                                    </div>

                                    <button type="submit" class="btn btn-primary mr-2">Add Commission Customer</button>
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
    <!-- Wrapper End -->
    <script>
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'img-thumbnail';
                    img.style.width = '150px';
                    img.style.height = '150px';
                    img.style.objectFit = 'cover';
                    preview.appendChild(img);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection
