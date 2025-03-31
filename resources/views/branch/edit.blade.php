@extends('layouts.backend.layouts')
@section('page-content')
    <!-- Wrapper Start -->
    <div class="wrapper">

        <div class="content-page">
            <div class="container-fluid">
                <h1>Edit Record</h1>

                <form action="{{ route('branch.update', $record->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <label>Name:</label>
                    <input type="text" name="name" value="{{ $record->name }}" required>

                    <label>Address:</label>
                    <input type="text" name="address" value="{{ $record->address }}">

                    <label>Description:</label>
                    <input type="text" name="description" value="{{ $record->description }}">

                    <label>Active:</label>
                    <select name="is_active">
                        <option value="yes" {{ $record->is_active == 'yes' ? 'selected' : '' }}>Yes</option>
                        <option value="no" {{ $record->is_active == 'no' ? 'selected' : '' }}>No</option>
                    </select>

                    <button type="submit">Update</button>
                </form>
                <!-- Page end  -->
            </div>
        </div>
    </div>
    <!-- Wrapper End-->
@endsection
