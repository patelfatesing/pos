@extends('layouts.backend.layouts')

@section('page-content')
    <style>
        .module-wrapper {
            display: block;
            width: 100%;
        }

        .module-box {
            width: 100%;
            border: 1px solid #ddd;
            background: #fafafa;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 12px;
        }

        .module-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }

        .module-title {
            font-size: 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .toggle-icon {
            font-size: 22px;
            font-weight: bold;
        }

        .submodule-container {
            margin-top: 12px;
            background: white;
            border-radius: 7px;
            border: 1px solid #e6e6e6;
            padding: 12px;
            display: none;
        }

        .subrow {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .subrow div:nth-child(1) {
            width: 60%;
            font-size: 15px;
        }

        .subrow div:nth-child(2) {
            width: 35%;
        }


        .module-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }

        .module-title {
            font-size: 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .toggle-icon {
            font-size: 22px;
            font-weight: bold;
            cursor: pointer;
            padding-right: 4px;
        }

        .submodule-container {
            margin-top: 12px;
            background: white;
            border-radius: 7px;
            border: 1px solid #e6e6e6;
            padding: 12px;
            display: none;
        }

        .subrow {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .subrow div:nth-child(1) {
            width: 55%;
            font-size: 14px;
            font-weight: 500;
        }

        .subrow div:nth-child(2) {
            width: 40%;
        }
    </style>

    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid add-form-list">

                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4 class="card-title">Edit Roles & Permission - {{ $record->name }}</h4>
                        <a href="{{ route('roles.list') }}" class="btn btn-secondary">Back</a>
                    </div>

                    <div class="card-body">

                        <form action="{{ route('roles.update', $record->id) }}" method="POST">
                            @csrf
                            @method('POST')

                            <input type="hidden" name="id" value="{{ $record->id }}">

                            <div class="row">

                                <!-- Role Name -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Name *</label>
                                        <input type="text" name="name" class="form-control"
                                            value="{{ $record->name }}" required>
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status *</label>
                                        <select name="is_active" class="form-control">
                                            <option value="yes" {{ $record->is_active == 'yes' ? 'selected' : '' }}>Yes
                                            </option>
                                            <option value="no" {{ $record->is_active == 'no' ? 'selected' : '' }}>No
                                            </option>
                                        </select>
                                    </div>
                                </div>

                            </div>

                            <!-- MODULE WRAPPER -->
                            <div class="module-wrapper">

                                @foreach ($modules as $module)
                                    <div class="module-box">

                                        <!-- Module Header -->
                                        <div class="module-header" data-target="#module-{{ $module->id }}">
                                            <span class="module-title">
                                                <input type="hidden" name="modules[{{ $module->id }}]" value="no">

                                                <input type="checkbox" class="module-checkbox"
                                                    name="modules[{{ $module->id }}]" value="yes"
                                                    {{ $module->is_active == 'yes' ? 'checked' : '' }}>

                                                {{ $module->name }}
                                            </span>

                                            <span class="toggle-icon" id="icon-{{ $module->id }}">+</span>
                                        </div>

                                        <!-- Submodules -->
                                        <div class="submodule-container" id="module-{{ $module->id }}">

                                            @foreach ($module->submodules as $sub)
                                                <div class="subrow">

                                                    <div>{{ $sub->name }}</div>

                                                    <div>
                                                        <select class="form-control"
                                                            name="permissions[{{ $sub->id }}][access]">

                                                            @if (in_array($sub->type, ['edit', 'delete', 'list']))
                                                                {{-- New permission set: none, own, all --}}
                                                                <option value="none"
                                                                    {{ ($permissions[$sub->id] ?? '') == 'none' ? 'selected' : '' }}>
                                                                    None</option>
                                                                <option value="own"
                                                                    {{ ($permissions[$sub->id] ?? '') == 'own' ? 'selected' : '' }}>
                                                                    Own</option>
                                                                <option value="all"
                                                                    {{ ($permissions[$sub->id] ?? '') == 'all' ? 'selected' : '' }}>
                                                                    All</option>
                                                            @else
                                                                {{-- Old permission set: all, yes, no --}}
                                                                <option value="all"
                                                                    {{ ($permissions[$sub->id] ?? '') == 'all' ? 'selected' : '' }}>
                                                                    All</option>
                                                                <option value="yes"
                                                                    {{ ($permissions[$sub->id] ?? '') == 'yes' ? 'selected' : '' }}>
                                                                    Yes</option>
                                                                <option value="no"
                                                                    {{ ($permissions[$sub->id] ?? '') == 'no' ? 'selected' : '' }}>
                                                                    No</option>
                                                            @endif

                                                        </select>

                                                    </div>

                                                </div>
                                            @endforeach

                                        </div>

                                    </div>
                                @endforeach

                            </div>

                            <button type="submit" class="btn btn-primary mt-3">Update Role & Permissions</button>
                            <button type="reset" class="btn btn-danger mt-3">Reset</button>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection


@section('scripts')
    <script>
        $(document).ready(function() {

            // Toggle submodule container
            $(".module-header").on("click", function(e) {

                // If clicking the checkbox - DO NOT toggle
                if ($(e.target).is("input[type=checkbox]")) return;

                let target = $(this).data("target");
                let icon = $(this).find(".toggle-icon");

                $(target).slideToggle(200);
                icon.text(icon.text() === "+" ? "-" : "+");
            });

            // Checkbox on/off handling
            $(".module-checkbox").on("change", function() {
                let id = $(this).data("id");

                if ($(this).is(":checked")) {
                    $("#module-" + id).slideDown(200);
                    $("#icon-" + id).text("-");
                } else {
                    $("#module-" + id).slideUp(200);
                    $("#icon-" + id).text("+");

                    // Reset permissions to NO
                    $("#module-" + id + " select").val("no");
                }
            });

        });
    </script>
@endsection
