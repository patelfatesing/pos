@extends('layouts.backend.layouts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                                    <h4 class="card-title">{{ __('messages.add_user') }}</h4>
                                </div>
                                <div>
                                    <a href="{{ route('users.list') }}"
                                        class="btn btn-secondary">{{ __('messages.back') }}</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('users.store') }}" method="POST" data-toggle="validator">
                                    @csrf
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>{{ __('messages.first_name') }}</label>
                                                <input class="floating-input form-control" type="text"
                                                    value="{{ old('first_name') }}" name="first_name" placeholder=" ">
                                                @error('first_name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>{{ __('messages.last_name') }}</label>
                                                <input class="floating-input form-control" value="{{ old('last_name') }}"
                                                    name="last_name" type="text" placeholder=" ">
                                                @error('last_name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>{{ __('messages.phone_number') }}</label>
                                                <input class="floating-input form-control"
                                                    value="{{ old('phone_number') }}" name="phone_number" type="tel"
                                                    placeholder=" " autocomplete="tel">
                                                @error('phone_number')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>{{ __('messages.email') }}</label>
                                                <input class="floating-input form-control" value="{{ old('email') }}"
                                                    name="email" type="email" placeholder=" ">
                                                @error('email')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>{{ __('messages.password') }}</label>
                                                <input id="password" class="floating-input form-control pr-5"
                                                    name="password" type="password" placeholder=" "
                                                    :value="old('password')">
                                                <!-- Eye icon -->
                                                <span class="position-absolute"
                                                    style="top: 50px; right: 15px; cursor: pointer;"
                                                    onclick="togglePasswordVisibility()">
                                                    <i id="togglePasswordIcon" class="fa fa-eye"></i>
                                                </span>

                                                @error('password')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="floating-label form-group">
                                                <label>{{ __('messages.confirm_password') }}</label>
                                                <input id="passwordnew" class="floating-input form-control pr-5"
                                                    name="password_confirmation" type="password" placeholder=" "
                                                    :value="old('confirm_password')">
                                                <!-- Eye icon -->
                                                <span class="position-absolute"
                                                    style="top: 50px; right: 15px; cursor: pointer;"
                                                    onclick="togglePasswordVisibilityNew()">
                                                    <i id="togglePasswordIcon" class="fa fa-eye"></i>
                                                </span>
                                                @error('confirm_password')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ __('messages.role_id') }} *</label>
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
                                        <div class="col-md-6 branch-section">
                                            <div class="form-group">
                                                <label>{{ __('messages.branch_id') }} *</label>
                                                <select name="branch_id" class="selectpicker form-control"
                                                    data-style="py-0">
                                                    <option value="">Select Store</option>
                                                    @foreach ($branch as $id => $name)
                                                        <option value="{{ $id }}" data-id="{{ $id }}">
                                                            {{ $name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('branch_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ __('messages.address') }}</label>
                                                <textarea class="form-control" name="address" rows="4">{{ old('address') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit"
                                        class="btn btn-primary mr-2">{{ __('messages.create_new_user') }}</button>
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
@endsection\
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

<script>
    function togglePasswordVisibility() {
        const input = document.getElementById('password');
        const icon = document.getElementById('togglePasswordIcon');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    function togglePasswordVisibilityNew() {
        const input = document.getElementById('passwordnew');
        const icon = document.getElementById('togglePasswordIconnew');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
