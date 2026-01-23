@extends('layouts.backend.layouts')

<?php
$roleId = auth()->user()->role_id;
?>
@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Wrapper Start -->
    <div class="wrapper">

        <div class="content-page">
            <div class="container-fluid">

                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">Stock Request Detail</h4>
                    </div>
                    <div>
                        <a href="{{ route('stock.requestList') }}" class="btn btn-secondary">Back</a>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-4">
                                <p><strong>From Store:</strong> {{ $stockRequest->branch->name ?? 'warehouse' }}</p>
                            </div>

                            <div class="col-sm-4">
                                <p><strong>Requested By:</strong> {{ $stockRequest->user->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-sm-4">
                                <p><strong>Status:</strong>
                                    @if ($stockRequest->status === 'pending')
                                        <span class="badge bg-warning">Pending
                                        </span>
                                    @else
                                        <span class="badge bg-success">Approved
                                        </span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-sm-4">
                                <p><strong>Date:</strong> {{ $stockRequest->requested_at->format('d M Y h:i A') }}</p>
                            </div>
                            <div class="col-sm-4">
                                <p><strong>Notes:</strong> {{ $stockRequest->notes ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><strong>Requested Items</strong></div>
                    <div class="card-body p-0">
                        <form method="POST" id="approveForm" action="">
                            @csrf
                            <input type="hidden" name="request_id" value="{{ $stockRequest->id }}">
                            <input type="hidden" name="from_store_id" value="{{ $sourceId }}">

                            <div class="card-body table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product</th>
                                            <th>Requested Qty</th>
                                            <th>Available</th>

                                            <th>Store Name</th>
                                            <th>Approve Qty</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $grouped = collect($flattened)->groupBy('product_id');
                                        @endphp

                                        @foreach ($grouped as $productId => $entries)
                                            @php
                                                $first = $entries->first();
                                                $rowspan = $entries->count();
                                                $requestedQty = $first['requested_qty'];
                                            @endphp

                                            @foreach ($entries as $i => $row)
                                                <tr class="product-row" data-product-id="{{ $row['product_id'] }}"
                                                    data-requested="{{ $requestedQty }}">

                                                    @if ($i === 0)
                                                        <td rowspan="{{ $rowspan }}">{{ $row['product_name'] }}</td>
                                                        <td rowspan="{{ $rowspan }}" class="requested-qty">
                                                            {{ $requestedQty }}</td>
                                                    @endif

                                                    <td>{{ $row['store_ava_quantity'] }}</td>

                                                    <td>{{ $row['store_name'] }}</td>
                                                    <td>
                                                        <input type="number"
                                                            class="form-control form-control-sm approve-input"
                                                            name="items[{{ $row['store_id'] }}][{{ $row['product_id'] }}]"
                                                            value="0" min="0"
                                                            max="{{ $row['store_ava_quantity'] }}"
                                                            data-product-id="{{ $row['product_id'] }}">
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" class="approve-checkbox"
                                                            name="approve_flags[{{ $row['store_id'] }}][{{ $row['product_id'] }}]"
                                                            data-product-id="{{ $row['product_id'] }}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>

                            </div>
                            @if ($roleId == 1 || getAccess($roleId, 'stock-request-approval') === 'yes')
                                <div class="card-footer text-end">
                                    <button type="submit" class="btn btn-success">Submit Approval</button>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
                <!-- Page end  -->
            </div>
        </div>
    </div>
    <!-- Wrapper End-->
    <script>
        const requestId = {{ $stockRequest->id }};
        const dynamicActionUrl = @json(route('stock-requests.approve', ['id' => '__ID__'])).replace('__ID__', requestId);
        $('#approveForm').attr('action', dynamicActionUrl);

        function syncProductRows(productId) {
            const rows = $(`.product-row[data-product-id="${productId}"]`);
            const requestedQty = parseInt(rows.first().data('requested')) || 0;
            let totalApproved = 0;

            // Step 1: Sum all approved quantities
            rows.each(function() {
                const qty = parseInt($(this).find('.approve-input').val()) || 0;
                totalApproved += qty;
            });

            // Step 2: Adjust inputs and checkboxes based on total
            rows.each(function() {
                const input = $(this).find('.approve-input');
                const checkbox = $(this).find('.approve-checkbox');
                const qty = parseInt(input.val()) || 0;

                checkbox.prop('checked', qty > 0);

                if (totalApproved >= requestedQty && qty === 0) {
                    input.prop('disabled', true);
                    checkbox.prop('disabled', true);
                } else {
                    input.prop('disabled', false);
                    checkbox.prop('disabled', false);
                }
            });
        }

        function setupCheckboxLogic(productId) {
            const rows = $(`.product-row[data-product-id="${productId}"]`);
            const requestedQty = parseInt(rows.first().data('requested')) || 0;

            rows.each(function() {
                const row = $(this);
                const input = row.find('.approve-input');
                const checkbox = row.find('.approve-checkbox');
                const available = parseInt(input.attr('max')) || 0;

                checkbox.off('change').on('change', function() {
                    let approvedOther = 0;

                    rows.each(function() {
                        if ($(this).is(row)) return;
                        const otherQty = parseInt($(this).find('.approve-input').val()) || 0;
                        approvedOther += otherQty;
                    });

                    if ($(this).is(':checked')) {
                        const remaining = Math.max(requestedQty - approvedOther, 0);
                        const fillQty = Math.min(available, remaining);
                        input.val(fillQty);
                    } else {
                        input.val(0);
                    }

                    syncProductRows(productId);
                });
            });
        }

        // Event: when input quantity changes
        $(document).on('input', '.approve-input', function() {
            const productId = $(this).data('product-id');
            syncProductRows(productId);
            setupCheckboxLogic(productId); // refresh checkbox binding
        });

        // Initialize all checkbox logic on page load
        $(document).ready(function() {
            const productIds = [...new Set($('.product-row').map(function() {
                return $(this).data('product-id');
            }).get())];

            productIds.forEach(productId => {
                syncProductRows(productId);
                setupCheckboxLogic(productId);
            });
        });
    </script>

    <script>
        // Optional: remove row
        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
        });
    </script>
@endsection
