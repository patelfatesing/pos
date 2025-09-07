@extends('layouts.backend.layouts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid add-form-list">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <div class="header-title">
                                    <h4 class="card-title">Create Account Group</h4>
                                </div>
                                <div><a href="{{ route('accounting.groups.list') }}" class="btn btn-secondary">Back</a></div>
                            </div>

                            <div class="card-body">
                                @if ($errors->any())
                                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                                @endif
                                <form action="{{ route('accounting.groups.store') }}" method="POST">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Name</label>
                                            <input type="text" name="name" class="form-control"
                                                value="{{ old('name') }}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Code (optional)</label>
                                            <input type="text" name="code" class="form-control"
                                                value="{{ old('code') }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Nature</label>
                                            <select name="nature" class="form-control" required>
                                                @foreach (['Asset', 'Liability', 'Income', 'Expense'] as $n)
                                                    <option value="{{ $n }}" @selected(old('nature') === $n)>
                                                        {{ $n }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Parent Group</label>
                                            <select id="parent_group" class="form-control">
                                                <option value="">— None —</option>
                                                @foreach ($parents as $p)
                                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Sub Parent Group</label>
                                            <select id="sub_parent_group" class="form-control">
                                                <option value="">— Select parent first —</option>
                                            </select>
                                        </div>

                                        {{-- this is the ONLY field posted; JS keeps it in sync --}}
                                        <input type="hidden" name="parent_id" id="parent_id"
                                            value="{{ old('parent_id') }}">

                                        <div class="col-md-4">
                                            <label class="form-label">Affects Gross (P&L)</label><br>
                                            <input type="checkbox" name="affects_gross" value="1"
                                                {{ old('affects_gross') ? 'checked' : '' }}> Yes
                                        </div>
                                        {{-- <div class="col-md-4">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order',0) }}">
                  </div> --}}
                                    </div>

                                    <div class="mt-3">
                                        <button class="btn btn-primary">Create Group</button>
                                        <button type="reset" class="btn btn-danger">Reset</button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#parent_group').on('change', function() {

                var groupId = $(this).val();
                var $sub = $('#sub_group_ids');

                if (groupId) {
                    $.ajax({
                        url: "{{ url('/accounting/groups') }}/children/" + groupId + "",
                        type: "GET",
                        dataType: "json",
                        success: function(data) {
                            $('#sub_parent_group').empty();
                            $('#sub_parent_group').append(
                                '<option value="" disabled selected>Select Sub Parent Group</option>'
                            );
                            $.each(data, function(key, value) {
                                $("#fate").text(value.name);
                                $('#sub_parent_group').append('<option value="' + value
                                    .id + '">' + value.name + '</option>');
                            });
                        },
                        error: function() {
                            alert('Failed to fetch subcategories. Please try again.');
                        }
                    });
                } else {
                    $sub.prop('disabled', true)
                        .empty().append('<option value="" selected>— Select Sub Group —</option>');
                    $('#fallback_parent_id').val('');
                }
            });

            // Optional: if you allow “no sub-group”, submit the fallback main ID
            $('form').on('submit', function() {
                if ($('#sub_group_ids').prop('disabled') || !$('#sub_group_ids').val()) {
                    const fallback = $('#fallback_parent_id').val();
                    if (fallback) {
                        // create a hidden input named parent_id so main group is submitted
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'parent_id',
                            value: fallback
                        }).appendTo(this);
                    }
                }
            });

        });
    </script>
@endsection
