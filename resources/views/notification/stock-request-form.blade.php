<style>
    #priceUpdateForm .modal-header {
        padding: 11px;
    }
    #priceUpdateForm .modal-body {
        padding: 15px !important;
        font-size: 14px;
    }
    #priceUpdateForm .card {
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        margin-bottom: 10px !important;
    }
    #priceUpdateForm .card-body {
        padding: 8px 12px !important;
    }
    #priceUpdateForm .form-group {
        margin-bottom: 0px !important;
    }
    #priceUpdateForm label {
        font-weight: 600;
        color: #555;
        margin-bottom: 0;
    }
    #priceUpdateForm table {
        margin-bottom: 0 !important;
        font-size: 14px;
    }
    #priceUpdateForm .table thead th {
        background-color: #f8f9fa !important;
        color: #333;
        padding: 8px !important;
        border-bottom: 2px solid #dee2e6;
    }
    #priceUpdateForm .table td {
        padding: 6px 8px !important;
        vertical-align: middle;
    }
    #priceUpdateForm .badge {
        font-size: 11px;
        padding: 4px 8px;
    }
</style>
<form id="priceUpdateForm">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title" id="approveModalLabel">Stock Request Detail</h5>
        @if (auth()->user()->hasRole('admin'))
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
        @else
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        @endif
    </div>

    <div class="modal-body">
        <div class="container-fluid p-0">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Store: </label> <span>{{ $branch_name }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Requested By: </label>
                                <span> {{ $stockRequest->user->name ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Date:</label>
                                <span> {{ $stockRequest->requested_at->format('d M Y h:i A') }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Status: </label>
                                <span class="badge {{ $stockRequest->status === 'pending' ? 'bg-warning' : ($stockRequest->status === 'approved' ? 'bg-success' : 'bg-danger') }}">
                                    {{ ucfirst($stockRequest->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Notes: </label>
                                <span>{{ $stockRequest->notes ?? '-' }}</span>
                            </div>
                        </div>
                        @if ($stockRequest->status === 'rejected')
                        <div class="col-12">
                            <div class="form-group">
                                <label>Reason: </label>
                                <span>{{ $stockRequest->reject_reason ?? '-' }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-bordered table-striped mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Size</th>
                                <th>Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalQty = 0; @endphp
                            @foreach ($stockRequest->items as $index => $item)
                                @php $totalQty += $item->quantity; @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->product->name }}</td>
                                    <td>{{ $item->product->size }}</td>
                                    <td>{{ $item->quantity }}</td>
                                </tr>
                            @endforeach
                            @if ($stockRequest->items->isEmpty())
                                <tr>
                                    <td colspan="4" class="text-center">No items found.</td>
                                </tr>
                            @else
                                <tr>
                                    <td colspan="3" class="text-right font-weight-bold">Total:</td>
                                    <td class="font-weight-bold">{{ $totalQty }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</form>
