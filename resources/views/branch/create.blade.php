@extends('layouts.backend.layouts')
@section('page-content')
    <!-- Wrapper Start -->

    <div class="content-page">
        <div class="container-fluid add-form-list">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                <div>
                    <h4 class="mb-0">Add Store</h4>
                </div>
                <div>
                    <a href="{{ route('branch.list') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">

                        <div class="card-body">
                            <form action="{{ route('branch.store') }}" method="POST" data-toggle="validator">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Name *</label>
                                            <input type="text" name="name" class="form-control"
                                                placeholder="Enter Name" required>
                                            @error('name')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Status *</label>
                                            <select name="is_active" class="selectpicker form-control" data-style="py-0">
                                                <option value="yes" selected>Active</option>
                                                <option value="no">Inactive</option>
                                            </select>
                                            @error('is_active')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Store Address</label>
                                            <textarea class="form-control" name="address" rows="2"></textarea>
                                            @error('address')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6 mt-4">
                                        <div class="form-group mt-2">
                                            <button type="submit" class="btn btn-primary mr-2" id="resetBtn">Add
                                                Store</button>
                                            <button type="reset" class="btn btn-danger">Reset</button>
                                        </div>
                                    </div>
                                    <!-- <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Description</label>
                                                            <textarea class="form-control" name="description" rows="4"></textarea>
                                                        </div>
                                                    </div> -->
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Page end  -->
        </div>
    </div>

    <!-- Wrapper End-->
    <script>
        $(document).ready(function() {

            $('#resetBtn').click(function() {
                // reset normal fields
                $('#storeForm')[0].reset();

                // reset selectpicker
                $('.selectpicker').val('yes').selectpicker('refresh');

                // remove validation error messages
                $('.text-danger').html('');
            });

        });
    </script>
@endsection
