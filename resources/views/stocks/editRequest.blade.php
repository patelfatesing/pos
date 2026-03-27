@extends('layouts.backend.layouts')

<?php $roleId = auth()->user()->role_id; ?>

@section('page-content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="content-page">
<div class="container-fluid">

<!-- HEADER -->
<div class="card-header d-flex justify-content-between">
    <h4 class="card-title">Stock Request Detail</h4>
    <a href="{{ route('stock.requestList') }}" class="btn btn-secondary">Back</a>
</div>

<!-- INFO -->
<div class="card mb-4">
    <div class="card-body row">
        <div class="col-md-4"><b>From Store:</b> {{ $stockRequest->branch->name }}</div>
        <div class="col-md-4"><b>Requested By:</b> {{ $stockRequest->user->name }}</div>
        <div class="col-md-4"><b>Status:</b> 
            <span class="badge bg-success">Approved</span>
        </div>
        <div class="col-md-4"><b>Date:</b> {{ $stockRequest->requested_at->format('d M Y h:i A') }}</div>
        <div class="col-md-4"><b>Notes:</b> {{ $stockRequest->notes ?? '-' }}</div>
    </div>
</div>

<!-- TABLE -->
<div class="card">
<form method="POST" action="{{ route('stock.updateApproved', $stockRequest->id) }}">
@csrf
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
@php $grouped = collect($flattened)->groupBy('product_id'); @endphp

@foreach ($grouped as $productId => $entries)

    @php
        $first = $entries->first();
        $rowspan = $entries->count();
        $requestedQty = $first['requested_qty'];
    @endphp

    @foreach ($entries as $i => $row)
    <tr class="product-row"
        data-product-id="{{ $row['product_id'] }}"
        data-requested="{{ $requestedQty }}">

        @if ($i === 0)
            <td rowspan="{{ $rowspan }}">{{ $row['product_name'] }}</td>
            <td rowspan="{{ $rowspan }}" class="requested-qty">{{ $requestedQty }}</td>
        @endif

        <td>{{ $row['store_ava_quantity'] }}</td>
        <td>{{ $row['store_name'] }}</td>

        <td>
            @if ($row['store_ava_quantity'] > 0)
                <input type="number"
                    class="form-control form-control-sm approve-input"
                    name="items[{{ $row['store_id'] }}][{{ $row['product_id'] }}]"
                    value="{{ $row['approved_qty'] ?? 0 }}"
                    min="0"
                    max="{{ $row['store_ava_quantity'] }}"
                    data-product-id="{{ $row['product_id'] }}">
            @else
                <span class="text-muted">N/A</span>
            @endif
        </td>

        <td>
            <input type="checkbox"
                class="approve-checkbox"
                {{ ($row['approved_qty'] ?? 0) > 0 ? 'checked' : '' }}
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
    <button type="submit" class="btn btn-success">Update Approval</button>
    <button type="reset" class="btn btn-danger">Reset</button>
</div>
@endif

</form>
</div>

</div>
</div>

<!-- SAME JS AS APPROVE -->
<script>

function syncProductRows(productId) {
    const rows = $(`.product-row[data-product-id="${productId}"]`);
    const requestedQty = parseInt(rows.first().data('requested')) || 0;

    let totalApproved = 0;

    rows.each(function() {
        totalApproved += parseInt($(this).find('.approve-input').val()) || 0;
    });

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
                approvedOther += parseInt($(this).find('.approve-input').val()) || 0;
            });

            if ($(this).is(':checked')) {
                const remaining = Math.max(requestedQty - approvedOther, 0);
                input.val(Math.min(available, remaining));
            } else {
                input.val(0);
            }

            syncProductRows(productId);
        });
    });
}

$(document).on('input', '.approve-input', function() {
    const productId = $(this).data('product-id');
    syncProductRows(productId);
    setupCheckboxLogic(productId);
});

$(document).ready(function() {

    const productIds = [...new Set($('.product-row').map(function() {
        return $(this).data('product-id');
    }).get())];

    productIds.forEach(productId => {
        syncProductRows(productId);
        setupCheckboxLogic(productId);
    });

});

// RESET FIX
$(document).on('click', 'button[type="reset"]', function() {

    $('.approve-input').each(function() {
        $(this).val(0).prop('disabled', false);
    });

    $('.approve-checkbox').prop('checked', false).prop('disabled', false);

});

</script>

@endsection