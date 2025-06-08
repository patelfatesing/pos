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
                                    <h4 class="card-title">Edit User - {{ $record->userInfo->first_name }}
                                        {{ $record->userInfo->last_name }}</h4>
                                </div>
                                <div>
                                    <a href="{{ route('users.list') }}" class="btn btn-secondary">Back</a>
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
                                                @error('first_name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Last Name</label>
                                                <input class="floating-input form-control"
                                                    value="{{ $record->userInfo->last_name }}" name="last_name"
                                                    type="text" placeholder=" ">
                                                @error('last_name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Email</label>
                                                <input class="floating-input form-control" value="{{ $record->email }}"
                                                    disabled name="email" type="email" placeholder=" ">
                                                @error('email')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>Phone No.</label>
                                                <input class="floating-input form-control"
                                                    value="{{ $record->userInfo->phone_number }}" name="phone_number"
                                                    type="text" placeholder=" ">
                                                @error('phone_number')
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
                                                        

                                                        <option value="{{ $id }}" {{ isset($record->role_id) && $record->role_id == $id ? 'selected' : '' }}>
                                                            {{ $name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('role_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6 branch-section">
                                            <div class="form-group">
                                                <label>Store *</label>
                                                <select name="branch_id" class="selectpicker form-control"
                                                    data-style="py-0">
                                                    <option value="">Select Store</option>
                                                    @foreach ($branch as $id => $name)
                                                        <option value="{{ $id }}"
                                                            {{ ($record->userInfo->branch_id ?? null) == $id ? 'selected' : '' }}>
                                                            {{ $name }}
                                                        </option>
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

    <script>
        $(document).ready(function() {
            const $branchSelect = $('select[name="branch_id"]');
            const allBranchOptions = $branchSelect.find('option').clone();

            $('select[name="role_id"]').on('change', function() {
                const selectedRoleId = $(this).val();
                // Hide or Show branch dropdown based on role
                if (selectedRoleId == '1' || selectedRoleId == '2') {
                    $('.branch-section').hide(); // hide the entire div
                    $('select[name="branch_id"]').val('').selectpicker('refresh'); // clear selection
                } else {
                    $('.branch-section').show(); // hide the entire div
                    $branchSelect.empty().append('<option value="">Select Store</option>');

                    if (selectedRoleId == '4') {
                        // Only show branch_id = 1
                        allBranchOptions.each(function() {
                            if ($(this).val() == '1') {
                                $branchSelect.append($(this));
                            }
                        });
                    } else {
                        // Show all branches except branch_id = 1
                        allBranchOptions.each(function() {
                            if ($(this).val() != '1' && $(this).val() !== '') {
                                $branchSelect.append($(this));
                            }
                        });
                    }

                    $branchSelect.selectpicker('refresh'); // Refresh if using Bootstrap Select
                }
            });

            // Trigger change on page load if old('role_id') is set
            const preselectedRole = $('select[name="role_id"]').val();
            if (preselectedRole) {
                $('select[name="role_id"]').trigger('change');
            }
        });
    </script>
@endsection
