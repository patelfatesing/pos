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
                                    <h4 class="card-title">Edit subcategories</h4>
                                </div>
                                <div>
                                    <a href="{{ route('subcategories.list') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('subcategories.update') }}" method="POST">
                                    @csrf
                                    @method('POST')
                                    <input type="hidden" name="id" value="{{ $record->id }}">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Name *</label>
                                                <input type="text" name="name" value="{{ $record->name }}"
                                                    class="form-control" placeholder="Enter Name" required>
                                                @error('name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- filepath: d:\xampp\htdocs\pos\resources\views\subcategories\edit.blade.php --}}
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Main Category *</label>
                                                <select name="category_id" class="selectpicker form-control"
                                                    data-style="py-0" required>
                                                    <option value="" disabled>Select Main Category</option>
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}"
                                                            {{ $record->main_category_id == $category->id ? 'selected' : '' }}>
                                                            {{ $category->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('category_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary mr-2">Update subcategories</button>
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
