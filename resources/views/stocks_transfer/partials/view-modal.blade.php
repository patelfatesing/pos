<div class="container-fluid">

    <div class="row">
        <div class="col-md-6">
            <table class="table table-borderless">
                <tr>
                    <th>Transfer Number:</th>
                    <td>{{ $stockTransfer->transfer_number }}</td>
                </tr>
                <tr>
                    <th>From Branch:</th>
                    <td>{{ $stockTransfer->fromBranch->name }}</td>
                </tr>
                <tr>
                    <th>To Branch:</th>
                    <td>{{ $stockTransfer->toBranch->name }}</td>
                </tr>
            </table>
        </div>

        <div class="col-md-6">
            <table class="table table-borderless">
                <tr>
                    <th>Status:</th>
                    <td>
                        <span class="badge badge-{{ $stockTransfer->status == 'completed' ? 'success' : 'warning' }}">
                            {{ ucfirst($stockTransfer->status) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Date:</th>
                    <td>{{ $stockTransfer->transferred_at }}</td>
                </tr>
                <tr>
                    <th>By:</th>
                    <td>{{ $stockTransfer->transferredBy->name }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="mt-3">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Category</th>
                    <th>Sub Category</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transferProducts as $i => $p)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $p->product->name }}</td>
                        <td>{{ $p->quantity }}</td>
                        <td>{{ $p->product->category->name ?? '-' }}</td>
                        <td>{{ $p->product->subcategory->name ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>