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
                                                <label>First Name</label>
                                                <input type="text" name="first_name" class="form-control"
                                                    value="{{ old('first_name', $commissionUser->first_name) }}" required>
                                                @error('first_name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Last Name</label>
                                                <input type="text" name="last_name" class="form-control"
                                                    value="{{ old('last_name', $commissionUser->last_name) }}" required>
                                                @error('last_name')
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
                                                <label>Commission Value</label>
                                                <input type="number" step="0.01" name="commission_value"
                                                    class="form-control"
                                                    value="{{ old('commission_value', $commissionUser->commission_value) }}"
                                                    required>
                                                @error('commission_value')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Applies To</label>
                                                <select name="applies_to" class="form-control">
                                                    <option value="all"
                                                        {{ old('applies_to', $commissionUser->applies_to) == 'all' ? 'selected' : '' }}>
                                                        All</option>
                                                    <option value="category"
                                                        {{ old('applies_to', $commissionUser->applies_to) == 'category' ? 'selected' : '' }}>
                                                        Category</option>
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
                                                <label>Reference ID</label>
                                                <input type="number" name="reference_id" class="form-control"
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
                                            <div class="floating-label form-group">
                                                <label>Start Date</label>
                                                <input type="date" name="start_date" class="form-control"
                                                    value="{{ old('start_date', $commissionUser->start_date) }}">
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>End Date</label>
                                                <input type="date" name="end_date" class="form-control"
                                                    value="{{ old('end_date', $commissionUser->end_date) }}">
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>Upload Images</label>
                                                <input type="file" name="images[]" class="form-control" multiple>
                                                @error('images')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Show existing images --}}
                                        @if (!empty($commissionUser->images))
                                            <div class="mt-3">
                                                <label>Existing Images</label>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach ($commissionUser->images as $img)
                                                        <div class="me-2 mb-2">
                                                            <img src="{{ asset('storage/' . $img->image_path) }}"
                                                                alt="Image" class="img-thumbnail"
                                                                style="width: 100px; height: 100px; object-fit: cover;">
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        {{-- New image preview --}}
                                        <div id="imagePreview" class="d-flex flex-wrap gap-2 mt-2"></div>
                                    </div>

                                    <button type="submit" class="btn btn-primary mr-2">Update Commission Customer</button>
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
@endsection
