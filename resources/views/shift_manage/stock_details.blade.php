@extends('layouts.backend.layouts')
<script src="{{ asset('assets/js/jquery-3.6.0.min.js')}}"></script>
@section('page-content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    .table-container {
        max-height: 70vh;
        overflow-y: auto;
        border-radius: 10px;
    }

    table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }

    thead th {
        position: sticky;
        top: 0;
        background: #f8f9fa;
        z-index: 2;
        font-size: 14px;
        white-space: nowrap;
    }

    tfoot th {
        position: sticky;
        bottom: 0;
        background: #e9ecef;
        z-index: 2;
        font-size: 15px;
    }

    th,
    td {
        padding: 10px;
        text-align: center;
        vertical-align: middle;
        border: 1px solid #dee2e6;
        white-space: nowrap;
    }

    tbody tr:hover {
        background-color: #f1f1f1;
    }

    .highlight-diff {
        background-color: #ffe5e5 !important;
        font-weight: 600;
    }

    .card {
        border-radius: 12px;
    }

    .header-title {
        font-weight: 600;
    }

    .verify-box {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 13px;
        cursor: pointer;
        font-weight: 500;
    }

    /* hide default checkbox */
    .verify-box input {
        margin-right: 5px;
    }

    /* Colors like your UI */
    .verify-box.sales {
        background: #28a745;
        color: #fff;
    }

    .verify-box.transfer {
        background: #28a745;
        color: #fff;
    }

    .verify-box.request {
        background: #fd7e14;
        color: #fff;
    }

    .verify-box.shift {
        background: #17a2b8;
        color: #fff;
    }

    /* Optional: checked effect */
    .verify-box input:checked+span {
        font-weight: 700;
    }

    td.text-start,
    th.text-start {
        text-align: left !important;
    }
</style>

<div class="content-page">
    <div class="container-fluid">
        <div class="card-header mb-1 d-flex flex-wrap align-items-center justify-content-between">
            <div>
                <h4 class="mb-0">🧾 Product Stock Summary - {{ $branch_name->name }}</h4>
            </div>
            <div>
                <a href="{{ route('shift-manage.stock-details-pdf', $shift->id) }}?subcategory_id={{ request('subcategory_id') }}&search={{ request('search') }}"
                    class="btn btn-danger">
                    📄 PDF
                </a>
                <a href="{{ route('shift-manage.list') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card p-2">
            <form method="GET" action="{{ route('shift-manage.stock-details', $shift->id) }}">
                <div class="row align-items-end">

                    <!-- Subcategory -->
                    <div class="col-md-2">
                        <select name="subcategory_id" class="form-control" onchange="this.form.submit()">
                            <option value="">All Subcategories</option>
                            @foreach ($subcategories as $subcategory)
                            <option value="{{ $subcategory->id }}"
                                {{ request('subcategory_id') == $subcategory->id ? 'selected' : '' }}>
                                {{ $subcategory->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Search -->
                    <div class="col-md-2">
                        <input type="text" name="search" class="form-control" placeholder="Search Product"
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            🔍 Search
                        </button>
                        <a href="{{ route('shift-manage.stock-details', $shift->id) }}" class="btn btn-secondary">
                            🔄 Reset
                        </a>
                    </div>

                    @if($shift->status == 'completed')
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center gap-1 verify-box sales ml-2">
                            <input type="checkbox" onchange="changeVerifyStatus('sales', this.checked)"
                                {{ $finalAdminStatusInv == 'verify' ? 'checked' : '' }}>

                            <span onclick="handleClick('sales')">
                                {!! $finalAdminStatusInv == 'verify' ? '✔ Sales' : '✖ Sales' !!}
                            </span>
                        </div>


                        <div class="d-flex align-items-center gap-1 verify-box transfer ml-2">
                            <input type="checkbox" onchange="changeVerifyStatus('transfer', this.checked)"
                                {{ $finalAdminStatusTra == 'verify' ? 'checked' : '' }}>

                            <span onclick="handleClick('transfer')">
                                {!! $finalAdminStatusTra == 'verify' ? '✔ Transfer' : '✖ Transfer' !!}
                            </span>
                        </div>


                        <div class="d-flex align-items-center gap-1 verify-box request ml-2">
                            <input type="checkbox" onchange="changeVerifyStatus('request', this.checked)"
                                {{ $finalAdminStatusReq == 'verify' ? 'checked' : '' }}>

                            <span onclick="handleClick('request')">
                                {!! $finalAdminStatusReq == 'verify' ? '✔ Request' : '✖ Request' !!}
                            </span>
                        </div>


                        <div class="d-flex align-items-center gap-1 verify-box shift ml-2">
                            <input type="checkbox" onchange="verifyFullShift(this.checked)"
                                {{ $finalShiftStatus == 'verify' ? 'checked' : '' }}>

                            <span>
                                {!! $finalShiftStatus == 'verify' ? '✔ Shift Verify' : '✖ Shift Verify' !!}
                            </span>
                        </div>
                    </div>
                    @else
                    <div class="pull-right">

                        <div class="d-flex gap-1 verify-box shift ml-2 pull-right">
                            <span>
                                Shift Open
                            </span>
                        </div>
                    </div>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Sr No</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Opening</th>
                            <th>Transfer In</th>
                            <th>Transfer Out</th>
                            <th>Sold</th>
                            <th>Modify +</th>
                            <th>Modify -</th>
                            <th>Closing</th>
                            <th>Physical</th>
                            <th>Difference</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php
                        $totalOpening = $totalAdded = $totalTransferred = $totalSold = $totalClosing = 0;
                        $totalPhysical = $totalDifference = $totalModifyAdd = $totalModifyRemove = 0;
                        @endphp
                        @php $sr = 1; @endphp
                        @forelse ($rawStockData as $stock)
                        @php
                        $totalOpening += $stock->opening_stock;
                        $totalAdded += $stock->added_stock;
                        $totalTransferred += $stock->transferred_stock;
                        $totalSold += $stock->sold_stock;
                        $totalClosing += $stock->closing_stock;
                        $totalPhysical += $stock->physical_stock ?? 0;
                        $totalDifference += $stock->difference_in_stock;
                        $totalModifyAdd += $stock->modify_sale_add_qty;
                        $totalModifyRemove += $stock->modify_sale_remove_qty;
                        @endphp

                        <tr class="{{ $stock->difference_in_stock != 0 ? 'highlight-diff' : '' }}">
                            <td>{{ $sr++ }}</td>

                            <td class="text-start">{{ $stock->product->name ?? 'N/A' }}</td>
                            <td>{{ $stock->product->subcategory->name ?? 'N/A' }}</td>
                            <td>{{ $stock->opening_stock }}</td>
                            <td>{{ $stock->added_stock }}</td>
                            <td>{{ $stock->transferred_stock }}</td>
                            <td>{{ $stock->sold_stock }}</td>
                            <td>{{ $stock->modify_sale_add_qty }}</td>
                            <td>{{ $stock->modify_sale_remove_qty }}</td>
                            <td>{{ $stock->closing_stock }}</td>
                            <td>
                                <input type="number" class="form-control form-control-sm physical-input"
                                    value="{{ $stock->physical_stock }}"
                                    data-product-id="{{ $stock->product_id }}" data-stock-id="{{ $stock->id }}"
                                    style="width:90px; text-align:center;">
                            </td>
                            <td>{{ $stock->difference_in_stock }}</td>
                        </tr>

                        @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted">No stock data available</td>
                        </tr>
                        @endforelse
                    </tbody>

                    {{-- FIXED FOOTER --}}
                    <tfoot>
                        <tr class="fw-bold">
                            <th colspan="3">TOTAL</th>
                            <th>{{ $totalOpening }}</th>
                            <th>{{ $totalAdded }}</th>
                            <th>{{ $totalTransferred }}</th>
                            <th>{{ $totalSold }}</th>
                            <th>{{ $totalModifyAdd }}</th>
                            <th>{{ $totalModifyRemove }}</th>
                            <th>{{ $totalClosing }}</th>
                            <th>{{ $totalPhysical }}</th>
                            <th class="{{ $totalDifference != 0 ? 'highlight-diff' : '' }}">
                                {{ $totalDifference }}
                            </th>
                        </tr>
                    </tfoot>

                </table>
            </div>
        </div>

    </div>
</div>
@endsection

<script>
    let salesStatus = "{{ $finalAdminStatusInv }}";
    let transferStatus = "{{ $finalAdminStatusTra }}";
    let requestStatus = "{{ $finalAdminStatusReq }}";

    function changeVerifyStatus(type, isChecked) {

        let status = isChecked ? 'verify' : 'unverify';
        let shift_id = "{{ $shift->id }}";

        Swal.fire({
            title: "Are you sure?",
            text: "Do you want to change the status?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, change it!",
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    type: "POST",
                    url: "{{ route('shift.verify.status') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        type: type,
                        status: status,
                        shift_id: shift_id
                    },
                    success: function(response) {
                        Swal.fire("Success!", "Physical stock updated.", "success")
                            .then(() => location.reload());
                    },
                    error: function() {
                        Swal.fire("Error!", "Something went wrong.", "error");
                    }
                });

            } else {
                location.reload(); // revert checkbox
            }

        });
    }

    function verifyFullShift(isChecked) {

        let shift_id = "{{ $shift->id }}";
        let status = isChecked ? 'verify' : 'unverify';

        // ✅ CHECK BEFORE VERIFY
        if (status === 'verify') {

            if (salesStatus !== 'verify' || transferStatus !== 'verify' || requestStatus !== 'verify') {

                Swal.fire({
                    icon: "warning",
                    title: "Verification Required",
                    text: "Please verify Sales, Transfer and Request first!"
                });

                // ❌ revert checkbox (NO reload)
                document.querySelector('.verify-box.shift input').checked = false;

                return;
            }
        }

        // ✅ CONFIRMATION
        Swal.fire({
            title: "⚠️ Are you sure?",
            text: "Do you want to verify full shift?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, verify!",
            cancelButtonText: "Cancel"
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    type: "POST",
                    url: "{{ route('shift.verify.all') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        shift_id: shift_id,
                        status: status
                    },
                    success: function(response) {

                        Swal.fire({
                            icon: "success",
                            title: "Verified!",
                            text: "Shift fully verified"
                        });

                        // ✅ update local status (no reload)
                        salesStatus = 'verify';
                        transferStatus = 'verify';
                        requestStatus = 'verify';

                    },
                    error: function() {

                        Swal.fire({
                            icon: "error",
                            title: "Error!",
                            text: "Something went wrong"
                        });

                        // ❌ revert checkbox
                        document.querySelector('.verify-box.shift input').checked = false;
                    }
                });

            } else {
                // ❌ user cancelled → revert checkbox
                document.querySelector('.verify-box.shift input').checked = false;
            }

        });
    }

    function handleClick(type) {

        let id = "{{ $id }}";
        let shift_id = "{{ $shift->id }}";

        if (type === 'sales') {
            window.location.href = `/shift-manage/view/${id}/${shift_id}?verify=yes`;
        } else if (type === 'transfer') {
            window.location.href = "{{ route('stock-transfer.list') }}?branch_id=" + id + "&shift_id=" + shift_id +
                "?verify=yes";
        } else if (type === 'request') {
            window.location.href = "{{ route('stock.requestList') }}?branch_id=" + id + "&shift_id=" + shift_id +
                "?verify=yes";
        }
    }

    $(document).on('change', '.physical-input', function() {

        let input = $(this);
        let physical = input.val();
        let product_id = input.data('product-id');
        let stock_id = input.data('stock-id');

        $.ajax({
            url: "{{ route('stock.update.physical') }}",
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                stock_id: stock_id,
                product_id: product_id,
                physical: physical
            },
            success: function(res) {

                // Update difference column
                input.closest('tr').find('td:last').text(res.difference);

                // Highlight row if mismatch
                if (res.difference != 0) {
                    input.closest('tr').addClass('highlight-diff');
                } else {
                    input.closest('tr').removeClass('highlight-diff');
                }

                Swal.fire("Saved!", "Physical updated.", "success");
            },
            error: function() {
                Swal.fire("Error!", "Update failed.", "error");
            }
        });
    });
</script>