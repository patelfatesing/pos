@extends('layouts.backend.layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="mb-3">Vouchers</h4>
                            </div>
                            <a href="{{ route('accounting.vouchers.create') }}" class="btn btn-primary">
                                <i class="las la-plus mr-1"></i> New Voucher
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger">{{ $errors->first() }}</div>
                        @endif

                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive rounded mb-3">
                                    <table class="table table-striped align-middle" id="voucher_table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Ref No</th>
                                                <th>Branch</th>
                                                <th>Narration</th>
                                                <th class="text-end">Dr Total</th>
                                                <th class="text-end">Cr Total</th>
                                                <th>Status</th>
                                                <th class="text-nowrap">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($vouchers as $v)
                                                @php
                                                    $dr = $v->lines->where('dc', 'Dr')->sum('amount');
                                                    $cr = $v->lines->where('dc', 'Cr')->sum('amount');
                                                    $ok = round($dr, 2) === round($cr, 2);
                                                @endphp
                                                <tr>
                                                    <td>{{ $v->voucher_date->format('Y-m-d') }}</td>
                                                    <td>{{ $v->voucher_type }}</td>
                                                    <td>{{ $v->ref_no ?? '-' }}</td>
                                                    <td>{{ $branches[$v->branch_id] ?? '-' }}</td>
                                                    <td title="{{ $v->narration }}">
                                                        {{ \Illuminate\Support\Str::limit($v->narration, 40) }}</td>
                                                    <td class="text-end">{{ number_format($dr, 2) }}</td>
                                                    <td class="text-end">{{ number_format($cr, 2) }}</td>
                                                    <td>
                                                        {!! $ok ? '<span class="badge bg-success">Balanced</span>' : '<span class="badge bg-danger">Unbalanced</span>' !!}
                                                    </td>
                                                    <td class="text-nowrap">
                                                        {{-- Optional: a show route if you add it later --}}
                                                        {{-- <a href="{{ route('accounting.vouchers.show',$v->id) }}" class="btn btn-sm btn-info">View</a> --}}
                                                        <form action="{{ route('accounting.vouchers.destroy', $v->id) }}"
                                                            method="POST" class="d-inline-block frm-del">
                                                            @csrf @method('DELETE')
                                                            <button type="button"
                                                                class="btn btn-sm btn-danger btn-delete">Delete</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center text-muted">No vouchers yet.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                {{-- simple pagination if you pass a LengthAwarePaginator --}}
                                @if (method_exists($vouchers, 'links'))
                                    <div>{{ $vouchers->links() }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).on('click', '.btn-delete', function() {
            const form = $(this).closest('form');
            swal({
                title: "Delete voucher?",
                text: "This action cannot be undone.",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then(ok => {
                if (ok) form.submit();
            });
        });
    </script>
@endsection
