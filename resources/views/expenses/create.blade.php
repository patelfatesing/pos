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
                                <h4 class="card-title">Add Expense</h4>
                            </div>
                            <div>
                                <a href="{{ route('exp.list') }}" class="btn btn-secondary">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('exp.store') }}" method="POST">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="expense_category_id">Expense Category *</label>
                                            <select name="expense_category_id" class="form-control">
                                                <option value="">Select Category</option>
                                                @foreach($categories as $id => $name)
                                                    <option value="{{ $id }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                            @error('expense_category_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title">Title *</label>
                                            <input name="title" type="text" class="form-control" placeholder="Enter Expense Title">
                                            @error('title')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="amount">Amount *</label>
                                            <input name="amount" type="number" step="0.01" class="form-control" placeholder="Enter Amount">
                                            @error('amount')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="expense_date">Expense Date *</label>
                                            <input name="expense_date" type="date" class="form-control">
                                            @error('expense_date')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="description">Description</label>
                                            <textarea name="description" class="form-control" rows="3" placeholder="Enter description (optional)"></textarea>
                                            @error('description')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary mr-2">Save Expense</button>
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
