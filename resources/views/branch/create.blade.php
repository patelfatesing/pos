@extends('layouts.backend.layouts')
@section('page-content')
    <!-- Wrapper Start -->
    <div class="wrapper">

        <div class="content-page">
            <div class="container-fluid">
                <h1>Create Record</h1>

                <form action="{{ route('branch.store') }}" method="POST">
                    @csrf
                    <label>Name:</label>
                    <input type="text" name="name" required>
                    
                    <label>Address:</label>
                    <input type="text" name="address">
                    
                    <label>Description:</label>
                    <input type="text" name="description">
            
                    <label>Active:</label>
                    <select name="is_active">
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
            
                    <button type="submit">Save</button>
                </form>
                <!-- Page end  -->
            </div>
        </div>
    </div>
    <!-- Wrapper End-->
@endsection
