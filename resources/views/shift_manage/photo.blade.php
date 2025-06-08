@extends('layouts.backend.layouts')

@section('page-content')
<div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="card shadow-lg border-0" >
        <div class="card-body text-center ">
            <h2 class="text-primary fw-bold">
                <i class="bi bi-camera-fill me-2"></i>Physical Stock Photo
            </h2>
            @if (!empty($shift->physical_photo) && Storage::disk('public')->exists($shift->physical_photo))
                <div class="mb-4 p-2 bg-white rounded-4 shadow-sm border border-3">
                    <img src="{{ asset('storage/' . $shift->physical_photo) }}"
                         alt="Physical Stock Photo"
                         class="img-fluid rounded-3"
                         style="max-height: 520px; width: 100%; object-fit: contain;">
                </div>
                <a href="{{ asset('storage/' . $shift->physical_photo) }}"
                   class="btn btn-success rounded-pill mb-3"
                   download>
                    <i class="bi bi-download me-1"></i>Download Photo
                </a>
            @else
                <div class="d-flex align-items-center justify-content-center bg-light rounded-4 shadow-sm mb-4" style="height: 260px;">
                    <span class="text-muted">No photo available for this shift.</span>
                </div>
            @endif
       
            <a href="{{ route('shift-manage.list') }}" class="btn btn-outline-primary w-100 rounded-pill">
                <i class="bi bi-arrow-left me-1"></i>Back to Shift List
            </a>
        </div>
    </div>
</div>
@endsection
