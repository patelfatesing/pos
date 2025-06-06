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
                                    <h4 class="card-title">Add Pack Size</h4>
                                </div>
                                <div>
                                    <a href="{{ route('packsize.list') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('packsize.store') }}" method="POST" data-toggle="validator">
                                    @csrf
                                    <div class="row">
                                        
                                        <div class="col-md-6">
                                            <label>Size *</label>
                                            <div class="input-group mb-4">
                                                
                                                <input type="text" class="form-control" name="size" placeholder="Enter Size" value="{{old('size')}}"
                                                   aria-label="Enter Size" aria-describedby="basic-addon2">
                                                <div class="input-group-append">
                                                   <span class="input-group-text" id="basic-addon2">ML</span>
                                                </div>
                                                
                                             </div>
                                             @error('size')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                    </div>
                                    <button type="submit" class="btn btn-primary mr-2">Add Pack Size</button>
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
