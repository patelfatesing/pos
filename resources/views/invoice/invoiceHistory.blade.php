@if ($logs->isEmpty())
    <p class="text-muted">No activity history found.</p>
@else
    <div class="timeline">
        @foreach ($logs as $log)
            <div class="border-bottom pb-2 mb-2">
                <strong class="text-capitalize">{{ $log->action }}</strong>
                <span class="text-muted small">({{ $log->created_at->format('d-m-Y H:i') }})</span>
                by <b>{{ $log->user->name ?? 'System' }}</b><br>
                <span class="text-dark">{{ $log->description }}</span>

                @if ($log->action === 'update' && $log->old_data && $log->new_data)
                    <ul class="mb-0 mt-1 small text-secondary">
                        <li>Product: <b>{{ $log->new_data['name'] ?? ($log->old_data['name'] ?? '') }}</b></li>
                        @if (($log->old_data['quantity'] ?? null) != ($log->new_data['quantity'] ?? null))
                            <li>Quantity: <b>{{ $log->old_data['quantity'] }}</b> →
                                <b>{{ $log->new_data['quantity'] }}</b>
                            </li>
                            <li>Add/Remove Quantity : {{ $log->new_data['quantity'] - $log->old_data['quantity'] }}</>
                            </li>
                        @endif
                        @if (($log->old_data['mrp'] ?? null) != ($log->new_data['mrp'] ?? null))
                            <li>Price: ₹<b>{{ $log->old_data['mrp'] }}</b> → ₹<b>{{ $log->new_data['mrp'] }}</b></li>
                        @endif
                    </ul>
                @elseif ($log->action === 'credit_change')
                    <ul class="mb-0 mt-1 small text-secondary">

                        <li>Credit: ₹<b>{{ $log->old_data['creditpay'] ?? '0.00' }}</b> →
                            ₹<b>{{ $log->new_data['creditpay'] ?? '0.00' }}</b></li>
                    </ul>
                @elseif ($log->action === 'add' || $log->action === 'remove')
                    <ul class="mb-0 mt-1 small text-secondary">
                        <li>Product: <b>{{ $log->new_data['name'] ?? ($log->old_data['name'] ?? '') }}</b></li>
                        <li>Qty: <b>{{ $log->new_data['quantity'] ?? ($log->old_data['quantity'] ?? '') }}</b></li>
                        <li>MRP: ₹<b>{{ $log->new_data['mrp'] ?? ($log->old_data['mrp'] ?? '') }}</b></li>
                    </ul>
                @endif
            </div>
        @endforeach
    </div>
@endif
