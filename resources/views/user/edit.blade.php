@extends('layouts.backend.layouts')
@section('page-content')
    <!-- Wrapper Start -->
    <div class="wrapper">
        <?php
        // dd($record->userInfo);
        ?>
        <div class="content-page">

            <div class="container-fluid add-form-list">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <div class="header-title">
                                    <h4 class="card-title">Add User</h4>
                                </div>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('users.update') }}" method="POST" data-toggle="validator">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $record->id }}">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Full Name</label>
                                                <input class="floating-input form-control"
                                                    value="{{ $record->userInfo->first_name }}" type="text"
                                                    name="first_name" placeholder=" ">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Last Name</label>
                                                <input class="floating-input form-control"
                                                    value="{{ $record->userInfo->last_name }}" name="last_name"
                                                    type="text" placeholder=" ">
                                            </div>
                                            @error('last_name')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Email</label>
                                                <input class="floating-input form-control" value="{{ $record->email }}"
                                                    disabled name="email" type="email" placeholder=" ">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Phone No.</label>
                                                <input class="floating-input form-control"
                                                    value="{{ $record->userInfo->phone_number }}" name="phone_number"
                                                    type="text" placeholder=" ">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Role *</label>
                                                <select name="role_id" class="selectpicker form-control" data-style="py-0">
                                                    <option value="">Select Role</option>

                                                    @foreach ($roles as $id => $name)
                                                        <option value="{{ $id }}"
                                                            {{ optional($record->role_id == $id) ? 'selected' : '' }}>
                                                            {{ $name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Store *</label>
                                                <select name="branch_id" class="selectpicker form-control"
                                                    data-style="py-0">
                                                    <option value="">Select Store</option>
                                                    @foreach ($branch as $id => $name)
                                                        <option value="{{ $id }}"
                                                            {{ optional($record->userInfo)->branch_id == $id ? 'selected' : '' }}>
                                                            {{ $name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Address</label>
                                                <textarea class="form-control" name="address" rows="4">{{ $record->userInfo->address }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary mr-2">Update User</button>
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
