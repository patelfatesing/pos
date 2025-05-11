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
                                    <h4 class="card-title">Edit Party User</h4>
                                </div>
                                <div>
                                    <a href="{{ route('party-users.list') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('party-users.update', $partyUser->id) }}" method="POST" enctype="multipart/form-data" data-toggle="validator">
                                    @csrf
                                    @method('PUT')
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>First Name</label>
                                                <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $partyUser->first_name) }}" required>
                                                @error('first_name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Last Name</label>
                                                <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $partyUser->last_name) }}" required>
                                                @error('last_name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Email</label>
                                                <input type="email" name="email" class="form-control" value="{{ old('email', $partyUser->email) }}" required>
                                                @error('email')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Phone</label>
                                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $partyUser->phone) }}">
                                                @error('phone')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Address</label>
                                                <input type="text" name="address" class="form-control" value="{{ old('address', $partyUser->address) }}">
                                                @error('address')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Credit Points</label>
                                                <input type="number" step="0.01" name="credit_points" class="form-control" value="{{ old('credit_points', $partyUser->credit_points) }}" required>
                                                @error('credit_points')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label>Upload Images</label>
                                                <input type="file" name="images[]" class="form-control" multiple>
                                                @error('images')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary mr-2">Update Party User</button>
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
