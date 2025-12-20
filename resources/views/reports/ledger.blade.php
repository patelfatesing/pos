@extends('layouts.backend')

@section('content')
<h4>Cash & Bank Summary</h4>
<p>{{ $from->format('d-m-Y') }} to {{ $to->format('d-m-Y') }}</p>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Particulars</th>
            <th>Opening</th>
            <th>Receipts</th>
            <th>Payments</th>
            <th>Closing</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
        <tr>
            <td>
                <a href="{{ route('reports.cash-bank.ledger', [
                    $row['ledger']->id,
                    'from_date'=>$from->toDateString(),
                    'to_date'=>$to->toDateString()
                ]) }}">
                    {{ $row['ledger']->name }}
                </a>
            </td>
            <td>{{ number_format($row['opening'],2) }}</td>
            <td>{{ number_format($row['receipt'],2) }}</td>
            <td>{{ number_format($row['payment'],2) }}</td>
            <td>{{ number_format($row['closing'],2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
