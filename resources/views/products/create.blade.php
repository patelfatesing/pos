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
                                    <a href="{{ route('products.list') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </div>

                            <div class="card-body">
                                <form action="{{ route('products.store') }}" enctype="multipart/form-data" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Product Type *</label>
                                                <select name="product_type" class="selectpicker form-control"
                                                    data-style="py-0">
                                                    <option>Standard</option>
                                                    <option>Combo</option>
                                                    <option>Digital</option>
                                                    <option>Service</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Name *</label>
                                                <input type="text" name="name" class="form-control"
                                                    placeholder="Enter Name" data-errors="Please Enter Name.">
                                                @error('name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Code *</label>
                                                <input type="text" name="code" class="form-control"
                                                    placeholder="Enter Code" data-errors="Please Enter Code.">
                                                @error('code')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        {!! $barcode !!}
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Barcode Symbology *</label>
                                                <select name="barcode_symbology" name="barcode_symbology"
                                                    class="selectpicker form-control" data-style="py-0">
                                                    <option>CREM01</option>
                                                    <option>UM01</option>
                                                    <option>SEM01</option>
                                                    <option>COF01</option>
                                                    <option>FUN01</option>
                                                    <option>DIS01</option>
                                                    <option>NIS01</option>
                                                </select>
                                                @error('code')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Category *</label>
                                                <select name="category" class="selectpicker form-control"
                                                    data-style="py-0">
                                                    <option>Beauty</option>
                                                    <option>Grocery</option>
                                                    <option>Food</option>
                                                    <option>Furniture</option>
                                                    <option>Shoes</option>
                                                    <option>Frames</option>
                                                    <option>Jewellery</option>
                                                </select>
                                                @error('code')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Cost *</label>
                                                <input type="text" name="cost" class="form-control"
                                                    placeholder="Enter Cost" data-errors="Please Enter Cost.">
                                                @error('cost')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Price *</label>
                                                <input type="text" name="price" class="form-control"
                                                    placeholder="Enter Price" data-errors="Please Enter Price.">
                                                @error('price')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Tax Method *</label>
                                                <select name="tax_method" class="selectpicker form-control"
                                                    data-style="py-0">
                                                    <option>Exclusive</option>
                                                    <option>Inclusive</option>
                                                </select>
                                                @error('tax_method')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Quantity *</label>
                                                <input type="text" name="quantity" class="form-control"
                                                    placeholder="Enter Quantity">
                                                @error('quantity')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Image</label>
                                                <input type="file" name="image" class="form-control image-file"
                                                    name="pic" accept="image/*">
                                                @error('image')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Description / Product Details</label>
                                                <textarea class="form-control" name="description" rows="4"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary mr-2">Add Product</button>
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
