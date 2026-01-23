@extends('layouts.backend.layouts')

@section('page-content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center mt-5">

            <h1 class="display-4 text-danger">403</h1>
            <h4 class="mb-3">Access Denied</h4>

            <p class="text-muted">
                You do not have permission to access this page.
            </p>

            <a href="{{ route('dashboard') }}" class="btn btn-primary mt-3">
                <i class="las la-home"></i> Go to Home
            </a>

        </div>
    </div>
</div>
@endsection
