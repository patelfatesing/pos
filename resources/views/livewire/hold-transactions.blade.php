<div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Product Name</th>
                <th>Amount</th>
                <th>Quantity</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($holdTransactions as $transaction)
                <tr>
                    <td>{{ $transaction->id }}</td>
                    <td>
                        {{ $transaction->product->name ?? '-' }} 
                        ({{ $transaction->product->size ?? '-' }})
                    </td>
                    <td>₹{{ number_format($transaction->product->sell_price ?? 0, 2) }}</td>
                    <td>{{ $transaction->quantity }}</td>
                    <td>{{ $transaction->created_at->format('d-m-Y H:i') }}</td>
                    <td>
                        <button wire:click="resumeTransaction({{ $transaction->id }})" class="btn btn-success btn-sm">
                            Resume
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>