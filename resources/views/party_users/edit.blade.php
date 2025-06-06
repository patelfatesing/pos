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
                                    <h4 class="card-title">Edit Party Customer</h4>
                                </div>
                                <div>
                                    <a href="{{ route('party-users.list') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('party-users.update', $partyUser->id) }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')

                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Customer Name</label>
                                                <input type="text" name="first_name" class="form-control"
                                                    value="{{ old('first_name', $partyUser->first_name) }}" required>
                                                @error('first_name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Email</label>
                                                <input type="email" name="email" class="form-control"
                                                    value="{{ old('email', $partyUser->email) }}" required>
                                                @error('email')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Phone</label>
                                                <input type="text" name="phone" class="form-control"
                                                    value="{{ old('phone', $partyUser->phone) }}" required>
                                                @error('phone')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Address</label>
                                                <input type="text" name="address" class="form-control"
                                                    value="{{ old('address', $partyUser->address) }}">
                                                @error('address')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Credit Points</label>
                                                <input type="number" name="credit_points" step="1"
                                                    class="form-control"
                                                    value="{{ old('credit_points', $partyUser->credit_points) }}" required>
                                                @error('credit_points')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>Upload Photo</label>
                                                <input type="file" name="photo" class="form-control" accept="image/*"
                                                    onchange="previewImage(this)">
                                                @error('photo')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror

                                                @if (!empty($partyUser->photo))
                                                    <div class="mt-3">
                                                        <label>Existing Photo</label>
                                                        <div>
                                                            <img src="{{ asset('storage/' . $partyUser->photo) }}"
                                                                class="img-thumbnail"
                                                                style="width: 100px; height: 100px; object-fit: cover;"
                                                                alt="Photo">
                                                        </div>
                                                    </div>
                                                @endif

                                                <div id="imagePreview" class="mt-2"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary mr-2">Update Party Customer</button>
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
                    img.style.width = '100px';
                    img.style.height = '100px';
                    img.style.objectFit = 'cover';
                    preview.appendChild(img);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection
