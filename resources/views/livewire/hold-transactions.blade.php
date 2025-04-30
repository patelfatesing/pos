<div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Sales ID</th>
                <th>Date</th>
                <th>Quantity</th>
                <th>Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @php
                 $sumqty=0;

            @endphp
            @foreach($holdTransactions as $sid => $transaction)
            @php
                foreach ($transaction->items as $key =>$item) {
                   $sumqty+=$item['quantity'];
                }
            @endphp
                <tr>
                    <td>HOLD-{{ $sid+1 }}</td>
                    <td>{{ $transaction->created_at->format('d-m-Y H:i') }}</td>
                    <td>{{ $sumqty }}</td>
                    <td>₹{{ number_format($transaction->total ?? 0, 2) }}</td>
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