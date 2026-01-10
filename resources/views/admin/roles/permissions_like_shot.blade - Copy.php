@extends('layouts.backend.layouts')

@section('styles')
    <style>
        .rb {
            --b: #e5e7eb;
            --mut: #6b7280;
            --txt: #111827;
        }

        .rb .accordion-item {
            border: 1px solid var(--b);
            border-radius: 8px;
            background: #fff;
            overflow: hidden;
        }

        .rb .accordion-item+.accordion-item {
            margin-top: .6rem;
        }

        .rb .accordion-button {
            background: #fff;
            padding: .6rem .9rem;
            font-weight: 600;
        }

        .rb .accordion-button:not(.collapsed) {
            background: #f2f6ff;
        }

        .rb .accordion-body {
            padding: .8rem .9rem 1rem;
        }

        .rb .table thead th {
            background: #f1f5f9;
            font-weight: 700;
            color: #111827;
        }

        .rb .w-access {
            min-width: 200px;
        }
    </style>
@endsection

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">


        <form method="POST" action="{{ route('admin.roles.permissions.update', $role) }}">
            @csrf

            <div class="accordion" id="permAccordion">
                @php
                    $has = fn($n) => in_array($n, $current, true);
                    $i = 0;
                @endphp

                @foreach ($matrix as $mSlug => $m)
                    @php
                        $i++;
                        $open = $i === 1;
                        $label = $moduleRows[$mSlug]->name ?? $m['label'];
                    @endphp

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="h-{{ $i }}">
                            <button class="accordion-button {{ $open ? '' : 'collapsed' }}" type="button"
                                data-bs-toggle="collapse" data-bs-target="#c-{{ $i }}">
                                {{ $label }}
                            </button>
                        </h2>
                        <div id="c-{{ $i }}" class="accordion-collapse collapse {{ $open ? 'show' : '' }}"
                            data-bs-parent="#permAccordion">
                            <div class="accordion-body">

                                {{-- MODULE-LEVEL TABLE (only render the actions defined) --}}
                                @if (!empty($m['actions']))
                                    <div class="table-responsive mb-3">
                                        <table class="table table-borderless align-middle">
                                            <thead>
                                                <tr>
                                                    <th style="width:50%">Module Name</th>
                                                    <th style="width:50%">Access To</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($m['actions'] as $act)
                                                    @php
                                                        $rowLabel =
                                                            ucfirst(str_replace('_', ' ', $label)) .
                                                            ' ' .
                                                            ucfirst($act === 'listing' ? 'List' : $act);
                                                        $inputName = "module[$mSlug][$act]";

                                                        // default/current selection
                                                        $value = 'none';
                                                        if (in_array($act, $binary)) {
                                                            $value = $has("$mSlug.$act") ? 'yes' : 'no';
                                                        } elseif (in_array($act, $scoped)) {
                                                            $value = $has("$mSlug.$act.all")
                                                                ? 'all'
                                                                : ($has("$mSlug.$act.own")
                                                                    ? 'own'
                                                                    : 'none');
                                                        }
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $rowLabel }}</td>
                                                        <td class="w-access">
                                                            @if (in_array($act, $binary))
                                                                <select name="{{ $inputName }}"
                                                                    class="form-select w-auto">
                                                                    <option value="yes"
                                                                        {{ $value == 'yes' ? 'selected' : '' }}>Yes
                                                                    </option>
                                                                    <option value="no"
                                                                        {{ $value == 'no' ? 'selected' : '' }}>No</option>
                                                                </select>
                                                            @elseif(in_array($act, $scoped))
                                                                <select name="{{ $inputName }}"
                                                                    class="form-select w-auto">
                                                                    <option value="none"
                                                                        {{ $value == 'none' ? 'selected' : '' }}>None
                                                                    </option>
                                                                    <option value="own"
                                                                        {{ $value == 'own' ? 'selected' : '' }}>Own
                                                                    </option>
                                                                    <option value="all"
                                                                        {{ $value == 'all' ? 'selected' : '' }}>All
                                                                    </option>
                                                                </select>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif

                                {{-- SUBMODULES TABLE (render only if defined) --}}
                                @if (!empty($m['submodules']))
                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle">
                                            <thead>
                                                <tr>
                                                    <th style="width:30%">Sub Module</th>
                                                    @php
                                                        // Build dynamic columns header based on union of actions used in submodules
                                                        $subActs = [];
                                                        foreach ($m['submodules'] as $s) {
                                                            $subActs = array_values(
                                                                array_unique(array_merge($subActs, $s['actions'])),
                                                            );
                                                        }
                                                    @endphp
                                                    @foreach ($subActs as $act)
                                                        <th>{{ ucfirst($act === 'listing' ? 'List' : $act) }}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($m['submodules'] as $sSlug => $s)
                                                    <tr>
                                                        <td><strong>{{ $s['label'] }}</strong></td>
                                                        @foreach ($subActs as $act)
                                                            @php
                                                                $inputName = "sub[$mSlug][$sSlug][$act]";
                                                                $value = 'none';
                                                                // only render controls if this submodule uses this action
                                                                $uses = in_array($act, $s['actions'], true);
                                                                if ($uses) {
                                                                    if (in_array($act, $binary)) {
                                                                        $value = $has("$mSlug.$sSlug.$act")
                                                                            ? 'yes'
                                                                            : 'no';
                                                                    } elseif (in_array($act, $scoped)) {
                                                                        $value = $has("$mSlug.$sSlug.$act.all")
                                                                            ? 'all'
                                                                            : ($has("$mSlug.$sSlug.$act.own")
                                                                                ? 'own'
                                                                                : 'none');
                                                                    }
                                                                }
                                                            @endphp
                                                            <td>
                                                                @if (!$uses)
                                                                    <span class="text-muted">—</span>
                                                                @elseif(in_array($act, $binary))
                                                                    <select name="{{ $inputName }}"
                                                                        class="form-select form-select-sm">
                                                                        <option value="yes"
                                                                            {{ $value == 'yes' ? 'selected' : '' }}>Yes
                                                                        </option>
                                                                        <option value="no"
                                                                            {{ $value == 'no' ? 'selected' : '' }}>No
                                                                        </option>
                                                                    </select>
                                                                @elseif(in_array($act, $scoped))
                                                                    <select name="{{ $inputName }}"
                                                                        class="form-select form-select-sm">
                                                                        <option value="none"
                                                                            {{ $value == 'none' ? 'selected' : '' }}>None
                                                                        </option>
                                                                        <option value="own"
                                                                            {{ $value == 'own' ? 'selected' : '' }}>Own
                                                                        </option>
                                                                        <option value="all"
                                                                            {{ $value == 'all' ? 'selected' : '' }}>All
                                                                        </option>
                                                                    </select>
                                                                @endif
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="text-end mt-3">
                <button class="btn btn-primary">Save Changes</button>
            </div>
        </form>

            </div>
        </div>
    </div>

    <div class="rb container-fluid">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Update Role - {{ $role->name }}</h5>
                <a href="{{ route('roles.list') }}" class="btn btn-outline-secondary btn-sm">Back To List</a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

    </div>
@endsection
