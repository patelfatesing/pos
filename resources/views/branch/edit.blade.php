@extends('layouts.backend.layouts')
@section('page-content')
    <!-- Wrapper Start -->

    <div class="content-page">
        <div class="container-fluid add-form-list">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                <div>
                    <h4 class="mb-0">Edit Store</h4>
                </div>
                <div>
                    <a href="{{ route('branch.list') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('branch.update', $record->id) }}" method="POST">
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

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Status *</label>
                                            <select name="is_active" class="selectpicker form-control" data-style="py-0">
                                                <option value="yes" {{ $record->is_active == 'yes' ? 'selected' : '' }}>
                                                    Yes</option>
                                                <option value="no" {{ $record->is_active == 'no' ? 'selected' : '' }}>No
                                                </option>

                                            </select>
                                            @error('is_active')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                     <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Bank Account Ledger *</label>
                                                <select name="bank_ledger_id" class="selectpicker form-control"
                                                    data-style="py-0">
                                                    <option value="">Select Bank Account Ledger</option>
                                                    @foreach ($acc_ledger as $id => $name)
                                                        <option value="{{ $id }}"
                                                            {{ isset($record->bank_ledger_id) && $record->bank_ledger_id == $id ? 'selected' : '' }}>
                                                            {{ $name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('bank_ledger_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Store Address</label>
                                            <textarea class="form-control" name="address" rows="4">{{ $record->address }}</textarea>
                                            @error('address')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <!-- <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Description</label>
                                                    <textarea class="form-control" name="description" rows="4">{{ $record->description }}</textarea>
                                                </div>
                                            </div> -->
                                </div>
                                <button type="submit" class="btn btn-success mr-2">Update Store</button>
                                <button type="reset" class="btn btn-danger" id="resetBtn">Reset</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Page end  -->
        </div>
    </div>

    <!-- Wrapper End-->
    <script>
        $(document).ready(function() {

            $('#resetBtn').click(function() {

                // Reset form fields to original values
                $('#editStoreForm')[0].reset();

                // Reset selectpicker
                $('.selectpicker').selectpicker('refresh');

                // Remove validation messages
                $('.text-danger').html('');
            });

        });
    </script>
@endsection
