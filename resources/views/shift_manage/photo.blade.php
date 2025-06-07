@extends('layouts.backend.layouts')

@section('page-content')
<div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="card shadow-lg border-0" style="max-width: 500px; width: 100%; border-radius: 20px;">
        <div class="card-body text-center p-4">
            <h2 class="mb-4 text-primary fw-bold">
                <i class="bi bi-camera-fill me-2"></i>Physical Stock Photo
            </h2>

            @if ($shift->physical_photo)
                <div class="border border-3 rounded-4 p-2 bg-white shadow-sm mb-4">
                    <img src="{{ asset('storage/' . $shift->physical_photo) }}" 
                         alt="Physical Photo" 
                         class="img-fluid rounded-3" 
                         style="max-height: 360px; object-fit: contain;">
                </div>
            @else
                <div class="alert alert-warning mb-4 rounded-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>No photo available for this shift.
                </div>
            @endif

            <a href="{{ route('shift-manage.list') }}" class="btn btn-outline-primary w-100 rounded-pill">
                <i class="bi bi-arrow-left me-1"></i>Back to Shift List
            </a>
        </div>
    </div>
</div>
@endsection
