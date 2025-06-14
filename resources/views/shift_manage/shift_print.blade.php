<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Shift Close Summary - {{ $branch_name ?? 'Shop' }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 13px; color: #222; }
        h2, h3, h4, h5 { margin: 0 0 10px 0; }
        .section { margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #888; padding: 6px 8px; text-align: left; }
        th { background: #f2f2f2; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .mb-2 { margin-bottom: 8px; }
        .mb-4 { margin-bottom: 24px; }
        .small { font-size: 11px; color: #666; }
        .bg-primary { background: #007bff; color: #fff; }
        .bg-warning { background: #ffeeba; }
        .table-success { background: #d4edda; }
    </style>
</head>
<body>
    <h2>Shift Close Summary - {{ $branch_name ?? 'Shop' }}</h2>
    <div class="section">
        <table>
            <tr>
                <th>Start Time</th>
                <td>{{ $shift->start_time ?? '-' }}</td>
                <th>End Time</th>
                <td>{{ $shift->end_time ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h4>Sales Details</h4>
        @foreach ($categoryTotals as $category => $items)
            <table>
                <thead>
                    <tr>
                        <th colspan="2" class="bg-primary">{{ ucfirst($category) }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $key => $value)
                        @php
                            $isTotal = strtoupper($key) === 'TOTAL';
                            $creditDetails = (strtoupper($key) === 'CREDIT' || strtoupper($key) === 'REFUND_CREDIT') ? '(Excluded from Cash)' : '';
                            $rowClass = $isTotal ? 'table-success fw-bold' : '';
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td>
                                {{ str_replace('_', ' ', $key) }}
                                @if($creditDetails) <span class="small">{{ $creditDetails }}</span> @endif
                            </td>
                            <td class="text-end">₹{{ number_format($value, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    </div>

    <div class="section">
        <h4>Cash Details</h4>
        <table>
            <thead>
                <tr>
                    <th>Denomination</th>
                    <th>Notes</th>
                    <th>x</th>
                    <th>Amount</th>
                    <th>=</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @if (!empty($shiftcash))
                    @php $totalNotes = 0; @endphp
                    @foreach ($shiftcash as $denomination => $quantity)
                        @php
                            $rowTotal = $denomination * $quantity;
                            $totalNotes += $rowTotal;
                        @endphp
                        <tr>
                            <td class="fw-bold">₹{{ number_format($denomination, 2) }}</td>
                            <td>{{ abs($quantity) }}</td>
                            <td class="text-center">x</td>
                            <td class="text-end">₹{{ number_format($denomination, 2) }}</td>
                            <td class="text-center">=</td>
                            <td class="fw-bold text-end">₹{{ number_format($rowTotal, 2) }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5" class="text-end">Total</th>
                    <th class="fw-bold text-end">₹{{ number_format(@$totalNotes, 2) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="section">
        <h4>Summary</h4>
        <table>
            <tbody>
                <tr>
                    <td class="fw-bold">System Cash Sales</td>
                    <td class="text-end">₹{{ number_format($totalNotes ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td class="fw-bold">Total Cash Amount</td>
                    <td class="text-end">₹{{ number_format(@$categoryTotals['summary']['TOTAL'], 2) }}</td>
                </tr>
                <tr>
                    <td class="fw-bold">Closing Cash</td>
                    <td class="text-end">₹{{ number_format($closing_cash ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td class="fw-bold">Discrepancy Cash</td>
                    <td class="text-end">₹{{ number_format($cash_discrepancy ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
