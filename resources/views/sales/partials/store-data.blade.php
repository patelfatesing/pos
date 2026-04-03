@php
    $grandTotal = 0;
@endphp
@foreach ($grouped as $key => $sales)
    @php
        $first = $sales->first();
        $date = \Carbon\Carbon::parse($first->created_at)->format('d-m-Y');

        // MAIN TOTAL
        $groupTotal = $sales->sum(fn($i) => (float) str_replace(',', '', $i->total));
    @endphp
    @php
        $grandTotal += $groupTotal;
    @endphp
    <!-- Main Row -->
    <tr class="store-row" data-id="{{ $key }}"
        style="background:#f8f9fa; cursor:pointer; border-bottom:1px solid #eee;">

        <td class="ps-3">
            <strong>{{ $first->branch->name }}</strong>
            <div class="text-muted" style="font-size:12px;">
                {{ $date }}
            </div>
        </td>

        <td class="pe-3">
            <div class="d-flex justify-content-between align-items-center">

                <!-- TOTAL -->
                <span style="font-weight:600;">
                    ₹{{ number_format($groupTotal, 2) }}
                </span>

                <!-- Buttons -->
                <div class="d-flex align-items-center" style="gap:6px;">

                    <a class="btn btn-sm btn-info view-row open-shift full-right" data-shift="{{ $first->shift_id }}">
                        View
                    </a>

                    <a href="{{ route('sales.add-sales', [
                        'branch_id' => $first->branch->id,
                        'shift_id' => $first->shift_id,
                    ]) . '?type=admin_sale' }}"
                        class="btn btn-sm btn-success mr-2">
                        +
                    </a>

                </div>
            </div>
        </td>
    </tr>

    <!-- Expand Row -->
    <tr class="sales-row d-none" id="sales-{{ $key }}">
        <td colspan="2" style="background:#ffffff; padding:10px;">

            @php
                $subTotal = 0;
                $total = 0;
                $discount = 0;
            @endphp

            <div class="table-responsive">
                <table class="table table-sm mb-0">

                    <thead style="background:#f1f3f5;">
                        <tr>
                            @if ($first->branch->id == 1)
                                <th>Commission User</th>
                            @else
                                <th>Party User</th>
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

                            <tr style="border-bottom:1px solid #f1f1f1;">

                                @if ($sale->branch_id == 1)
                                    <td>
                                        {{ $sale->partyUser->first_name ?? '-' }}
                                    </td>
                                @else
                                    <td>
                                        {{ $sale->commissionUser->first_name ?? '-' }}
                                    </td>
                                @endif

                                <td>{{ $rowDiscount }}</td>

                                <td>{{ number_format($rowSubTotal, 2) }}</td>

                                <td style="font-weight:500;">
                                    ₹{{ number_format($rowTotal, 2) }}
                                </td>

                                <td class="text-end">
                                    <a class="badge bg-light text-dark" href="javascript:void(0)"
                                        onclick="openInvoiceModal({{ $sale->id }})">
                                        <i class="ri-eye-line"></i>
                                    </a>

                                    <a class="badge bg-light text-dark ml-2 view-invoices"
                                        href="{{ url('/sales/edit-sales/' . $sale->id) }}?type=admin_sale">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <!-- ✅ FOOTER TOTAL -->
                    <tfoot style="background:#f8f9fa; font-weight:600;">
                        <tr>
                            <td>Total</td>
                            <td>{{ number_format($discount, 2) }}</td>
                            <td>{{ number_format($subTotal, 2) }}</td>
                            <td>₹{{ number_format($total, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>

                </table>
            </div>

        </td>
    </tr>
@endforeach
<tr style="background:#e9ecef; font-weight:700;">
    <td class="ps-3">
        GRAND TOTAL
    </td>
    <td class="text-end pe-3">
        ₹{{ number_format($grandTotal, 2) }}
    </td>
</tr>
