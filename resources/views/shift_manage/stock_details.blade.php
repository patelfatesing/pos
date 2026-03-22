@extends('layouts.backend.layouts')

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

                        <div class="d-flex align-items-center gap-3">

                            <div class="d-flex align-items-center gap-1 verify-box sales ml-2">
                                <input type="checkbox" name="verify_sales" id="verify_sales">
                                <span onclick="handleClick('sales')">✔ Sales</span>
                            </div>

                            <div class="d-flex align-items-center gap-1 verify-box transfer ml-2">
                                <input type="checkbox" name="verify_transfer" id="verify_transfer">
                                <span onclick="handleClick('transfer')">✔ Transfer</span>
                            </div>

                            <div class="d-flex align-items-center gap-1 verify-box request ml-2">
                                <input type="checkbox" name="verify_request" id="verify_request">
                                <span onclick="handleClick('request')">✔ Request</span>
                            </div>

                            <div class="d-flex align-items-center gap-1 verify-box shift ml-2">
                                <input type="checkbox" name="unverify_shift" id="unverify_shift">
                                <span>✖ Shift</span>
                            </div>

                        </div>
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="card">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
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
                                    <td class="text-start">{{ $stock->product->name ?? 'N/A' }}</td>
                                    <td>{{ $stock->product->subcategory->name ?? 'N/A' }}</td>
                                    <td>{{ $stock->opening_stock }}</td>
                                    <td>{{ $stock->added_stock }}</td>
                                    <td>{{ $stock->transferred_stock }}</td>
                                    <td>{{ $stock->sold_stock }}</td>
                                    <td>{{ $stock->modify_sale_add_qty }}</td>
                                    <td>{{ $stock->modify_sale_remove_qty }}</td>
                                    <td>{{ $stock->closing_stock }}</td>
                                    <td>{{ $stock->physical_stock }}</td>
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
                                <th colspan="2">TOTAL</th>
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
    function handleClick(type) {

        let id = "{{ $id }}";
        let shift_id = "{{ $shift->id }}";

        if (type === 'sales') {
            window.location.href = `/shift-manage/view/${id}/${shift_id}`;
        } else if (type === 'transfer') {
            window.location.href = "{{ route('stock-transfer.list') }}?branch_id="+id+"&shift_id="+shift_id;
        } else if (type === 'request') {
            window.location.href = "{{ route('stock.requestList') }}?branch_id="+id+"&shift_id="+shift_id;
        } 
    }
</script>
