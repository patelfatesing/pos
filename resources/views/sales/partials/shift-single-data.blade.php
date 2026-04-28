@foreach ($grouped as $key => $sales)
    @php
        $first = $sales->first();
    @endphp

    <div class="mb-3 border rounded p-2">
        @php
            $subTotal = 0;
            $total = 0;
            $discount = 0;
        @endphp
        <div class="d-flex justify-content-between align-items-center mb-2">

            <h6 class="mb-0">
                {{ $first->branch->name }}
                ({{ \Carbon\Carbon::parse($first->created_at)->format('d-m-Y') }})
            </h6>


            <a href="javascript:void(0)" class="btn btn-success"
                onclick="openAddSalesModal({{ $first->branch->id }}, {{ $first->shift_id }})">
                + Add Sales
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">

                <thead style="background:#f1f3f5;">
                    <tr>
                        @if ($first->branch->id == 1)
                            <th>Party User</th>
                        @else
                            <th>Commission User</th>
                        @endif
                        <th>Discount</th>
                        <th>Sub Total</th>
                        <th>Total</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($sales as $sale)
                        @php
                            $rowTotal = (float) str_replace(',', '', $sale->total);
                            $rowSubTotal = (float) $sale->sub_total;
                            $rowDiscount = (float) $sale->party_amount + (float) $sale->commission_amount;

                            $total += $rowTotal;
                            $subTotal += $rowSubTotal;
                            $discount += $rowDiscount;
                        @endphp

                        <tr class="{{ $sale->admin_status == 'verify' ? 'table-success' : 'table-warning' }}">

                            <td>
                                {{ $sale->branch_id == 1 ? $sale->partyUser->first_name ?? '-' : $sale->commissionUser->first_name ?? '-' }}
                            </td>

                            <td>{{ $rowDiscount }}</td>
                            <td>{{ number_format($rowSubTotal, 2) }}</td>
                            <td>₹{{ number_format($rowTotal, 2) }}</td>

                            <td class="text-end">
                                <div class="d-flex justify-content-end">

                                    <a class="badge bg-light text-dark mr-1"
                                        onclick="openInvoiceModal({{ $sale->id }})">
                                        <i class="fa fa-eye"></i>
                                    </a>

                                    <a class="badge bg-warning text-dark"
                                        onclick="editInvoiceModal({{ $sale->id }})">
                                        <i class="fa fa-edit"></i>
                                    </a>

                                </div>
                            </td>

                        </tr>
                    @endforeach
                </tbody>

                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td>{{ number_format($discount, 2) }}</td>
                        <td>{{ number_format($subTotal, 2) }}</td>
                        <td>
                            ₹{{ number_format($total + $sales->grand_total, 2) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>

                <tr style="background:#d1ecf1;">
                    <td colspan="3" class="text-end">+ Cash In Hand</td>
                    <td>₹{{ number_format($sales->total_cash_in_hand ?? 0, 2) }}</td>
                    <td></td>
                </tr>

                <tr style="background:#f8d7da;">
                    <td colspan="3" class="text-end">- Withdraw</td>
                    <td>₹{{ number_format($sales->total_withdraw ?? 0, 2) }}</td>
                    <td></td>
                </tr>

            </table>
        </div>

    </div>
@endforeach
