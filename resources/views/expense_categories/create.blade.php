@extends('layouts.backend.layouts')
@section('page-content')
    <!-- Wrapper Start -->
    <div class="wrapper">

        <div class="content-page">

            <div class="container-fluid add-form-list">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0">Add Expense Category</h4>
                    </div>
                    <div>
                        <a href="{{ route('exp_category.list') }}" class="btn btn-secondary">Back</a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                           
                            <div class="card-body">
                                <form action="{{ route('exp_category.store') }}" method="POST" data-toggle="validator">
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

                                    </div>
                                    <button type="submit" class="btn btn-success mr-2">Add Expense Category</button>
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
