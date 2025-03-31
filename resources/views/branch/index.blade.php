@extends('layouts.backend.layouts')
@section('page-content')
    <!-- Wrapper Start -->
    <div class="wrapper">

        <div class="content-page">
            <div class="container-fluid">
                <h1>My Records</h1>
                <a href="{{ route('branch.create') }}">Create New</a>

                @if (session('success'))
                    <p>{{ session('success') }}</p>
                @endif

                <table border="1">
                    <tr>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Description</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                    @foreach ($data as $record)
                        <tr>
                            <td>{{ $record->name }}</td>
                            <td>{{ $record->address }}</td>
                            <td>{{ $record->description }}</td>
                            <td>{{ $record->is_active }}</td>
                            <td>
                                <a href="{{ route('branch.edit', $record->id) }}">Edit</a>
                                <form action="{{ route('branch.destroy', $record->id) }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </table>
                <!-- Page end  -->
            </div>
        </div>
    </div>
    <!-- Wrapper End-->
@endsection
