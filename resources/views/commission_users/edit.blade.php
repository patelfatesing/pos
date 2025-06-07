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
                                    <h4 class="card-title">Edit Commission Customer</h4>
                                </div>
                                <div>
                                    <a href="{{ route('commission-users.list') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('commission-users.update', $commissionUser->id) }}" method="POST"
                                    enctype="multipart/form-data" data-toggle="validator">
                                    @csrf
                                    @method('PUT')

                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Customer Name</label>
                                                <input type="text" name="first_name" class="form-control"
                                                    value="{{ old('first_name', $commissionUser->first_name) }}">
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
                                                        {{ old('commission_type', $commissionUser->commission_type) == 'fixed' ? 'selected' : '' }}>
                                                        Fixed</option>
                                                    <option value="percentage"
                                                        {{ old('commission_type', $commissionUser->commission_type) == 'percentage' ? 'selected' : '' }}>
                                                        Percentage</option>
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
                                                    value="{{ old('email', $commissionUser->email) }}">
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
                                                        {{ old('applies_to', $commissionUser->applies_to) == 'product' ? 'selected' : '' }}>
                                                        Product</option>
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
                                                    value="{{ old('reference_id', $commissionUser->reference_id) }}">
                                                @error('reference_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Active</label>
                                                <select name="is_active" class="form-control">
                                                    <option value="1"
                                                        {{ old('is_active', $commissionUser->is_active) == '1' ? 'selected' : '' }}>
                                                        Yes</option>
                                                    <option value="0"
                                                        {{ old('is_active', $commissionUser->is_active) == '0' ? 'selected' : '' }}>
                                                        No</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>Photo</label>
                                                <input type="file" name="photo" class="form-control" accept="image/*"
                                                    onchange="previewImage(event)">
                                                @error('photo')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        @if (!empty($commissionUser->photo))
                                            <div class="form-group">
                                                <label>Current Photo</label><br>
                                                <img src="{{ asset('storage/' . $commissionUser->photo) }}" alt="Photo"
                                                    class="img-thumbnail"
                                                    style="width: 100px; height: 100px; object-fit: cover;">
                                            </div>
                                        @endif


                                        <div class="col-12">
                                            <label>Preview New Photo</label>
                                            <div id="imagePreview" class="mt-2"></div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary mr-2">Update Commission Customer</button>
                                    <button type="reset" class="btn btn-danger">Reset</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Image Preview Script --}}
    <script>
        function previewImage(event) {
            const imagePreview = document.getElementById('imagePreview');
            imagePreview.innerHTML = ''; // Clear existing

            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'img-thumbnail';
                    img.style.width = '100px';
                    img.style.height = '100px';
                    img.style.objectFit = 'cover';
                    imagePreview.appendChild(img);
                }

                reader.readAsDataURL(file);
            }
        }
    </script>
@endsection
