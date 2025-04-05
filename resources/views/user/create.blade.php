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
                                    <h4 class="card-title">Add User</h4>
                                </div>
                                <div>
                                    <a href="{{ route('users.list') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('users.store') }}" method="POST" data-toggle="validator">
                                    @csrf
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>First Name</label>
                                                <input class="floating-input form-control" type="text" value="{{ old('first_name') }}" name="first_name"
                                                    placeholder=" ">
                                                @error('first_name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Last Name</label>
                                                <input class="floating-input form-control" value="{{ old('last_name') }}" name="last_name" type="text"
                                                    placeholder=" ">
                                                @error('last_name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Email</label>
                                                <input class="floating-input form-control" value="{{ old('email') }}" name="email" type="email"
                                                    placeholder=" ">
                                                @error('email')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Phone No.</label>
                                                <input class="floating-input form-control" value="{{ old('phone_number') }}" name="phone_number"
                                                    type="text" placeholder=" ">
                                                @error('phone_number')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Password</label>
                                                <input class="floating-input form-control" value="{{ old('password') }}" name="password" type="password"
                                                    placeholder=" ">
                                                @error('password')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Confirm Password</label>
                                                <input class="floating-input form-control" value="{{ old('confirm_password') }}" name="confirm_password"
                                                    type="password" placeholder=" ">
                                                @error('confirm_password')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Role *</label>
                                                <select name="role_id" class="selectpicker form-control" data-style="py-0">
                                                    <option value="">Select Role</option>
                                                    @foreach ($roles as $id => $name)
                                                        <option value="{{ $id }}">{{ $name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('role_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Store *</label>
                                                <select name="branch_id" class="selectpicker form-control"
                                                    data-style="py-0">
                                                    <option value="">Select Store</option>
                                                    @foreach ($branch as $id => $name)
                                                        <option value="{{ $id }}">{{ $name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('branch_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Address</label>
                                                <textarea class="form-control" name="address" rows="4">{{ old('address') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary mr-2">Add Store</button>
                                    <button type="reset" class="btn btn-danger">Reset</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Page end  -->
            </div>
        </div>
    </div>
    <!-- Wrapper End-->
@endsection
