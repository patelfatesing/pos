<h5>{{ $voucher->voucher_type }} Voucher</h5>
<p>Date: {{ $voucher->voucher_date }}</p>
<p>Ref No: {{ $voucher->ref_no }}</p>
<p>Narration: {{ $voucher->narration }}</p>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Ledger</th>
            <th>Dr</th>
            <th>Cr</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($voucher->lines as $line)
            <tr>
                <td>{{ $line->ledger->name }}</td>
                <td>{{ $line->dc === 'Dr' ? number_format($line->amount, 2) : '' }}</td>
                <td>{{ $line->dc === 'Cr' ? number_format($line->amount, 2) : '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="d-flex gap-2 mt-3">
    <a href="{{ url('/accounting/vouchers/edit/'.$voucher->id) }}"
        class="btn btn-outline-primary btn-sm">
        Edit Voucher
    </a>

    <button type="button" data-id="{{$voucher->id}} }}"
        class="btn btn-outline-danger btn-sm ml-2" id="deleteVoucherBtn">
        Delete Voucher
    </button>
</div>
