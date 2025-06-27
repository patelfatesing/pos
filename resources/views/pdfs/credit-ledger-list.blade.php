<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        .title {
            text-align: center;
            font-size: 16px;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .filters {
            margin-bottom: 10px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            font-size: 11px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>

    <div class="title">Customer Credit Ledger</div>

    <div class="filters">
        From: {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : '-' }} &nbsp;
        To: {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : '-' }} <br>
        @if ($search)
            Search: "{{ $search }}"
        @endif
    </div>
    <!-- Customer Summary Table -->
    <table style="width:100%; margin-bottom: 10px;">
        
        <tr>
            <td><strong>Name</strong> {{ strtoupper($branchName) }}</td>
            <td><strong>Credit</strong> {{ number_format($totalCredit, 2) }} Cr</td>
        </tr>
        <tr>
            <td><strong>Address</strong> {{ $branchAddress ?? '-' }}</td>
            <td><strong>Debit</strong> {{ number_format($totalDebit, 2) }} Dr</td>
        </tr>
        <tr>
            <td></td>
            <td><strong>Net Outstanding</strong> {{ number_format($netOutstanding, 2) }} Dr</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Sr. No.</th>
                <th>Invoice No</th>
                <th>Party Customer</th>
                <th>Type</th>
                <th>Total Amount</th>
                <th>Credit</th>
                <th>Debit</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($ledgers as $index => $ledger)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $ledger->invoice_number }}</td>
                    <td>{{ $ledger->party_user ?? 'N/A' }}</td>
                    <td>{{ ucfirst($ledger->type) }}</td>
                    <td>{{ number_format($ledger->total_amount, 2) }}</td>
                    <td>{{ number_format($ledger->credit_amount, 2) }}</td>
                    <td>{{ number_format($ledger->debit_amount, 2) }}</td>
                    <td>{{ ucfirst($ledger->status) }}</td>
                    <td>{{ \Carbon\Carbon::parse($ledger->created_at)->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>

</html>
