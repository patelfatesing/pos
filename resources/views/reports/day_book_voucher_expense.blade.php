<div class="container-fluid">
    <h6 class="mb-1">Expense Voucher #{{ $voucher->voucher_no ?? $voucher->id }}</h6>
    <div class="small text-muted mb-2">
        Date: {{ $voucher->created_at->format('d-m-Y H:i') }}
    </div>

    <dl class="row mb-0">
        <dt class="col-sm-3">Expense Head</dt>
        <dd class="col-sm-9">{{ $voucher->expense_head ?? 'Expense' }}</dd>

        <dt class="col-sm-3">Amount</dt>
        <dd class="col-sm-9">{{ number_format($voucher->amount, 2) }}</dd>

        <dt class="col-sm-3">Note</dt>
        <dd class="col-sm-9">{{ $voucher->note ?? '-' }}</dd>
    </dl>
</div>
