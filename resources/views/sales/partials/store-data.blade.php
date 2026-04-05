@php
    $grandTotal = 0;

@endphp
@if ($grouped->count() > 0)
    @foreach ($grouped as $key => $sales)
        @php

            $first = $sales->first();
            $date = \Carbon\Carbon::parse($first->created_at)->format('d-m-Y');

            // MAIN TOTAL
            $groupTotal = $sales->sum(fn($i) => (float) str_replace(',', '', $i->total));
            $groupTotal += $sales->grand_total;
            $status = $shiftStatuses[$first->shift_id] ?? null;

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

                        @if ($first->shift->status == 'completed')
                            <div class="d-flex align-items-center gap-3">

                                <!-- SALES -->
                                <label class="switch">
                                    <input type="checkbox"
                                        onchange="changeVerifyStatus('sales', {{ $first->shift_id }}, this.checked)"
                                        {{ ($status['inv'] ?? '') == 'verify' ? 'checked' : '' }}>
                                    <span class="slider round"></span>
                                </label>
                                <span class="mb-2 mr-2 ml-1">Sales</span>

                                <!-- TRANSFER -->
                                <label class="switch">
                                    <input type="checkbox"
                                        onchange="changeVerifyStatus('transfer', {{ $first->shift_id }}, this.checked)"
                                        {{ ($status['tra'] ?? '') == 'verify' ? 'checked' : '' }}>
                                    <span class="slider round"></span>
                                </label>

                                <!-- CLICKABLE LINK (separate, safe) -->
                                <a href="javascript:void(0)" class="text-primary mb-2 mr-2 ml-1"
                                    onclick="handleClick('transfer', {{ $first->shift_id }}, {{ $first->branch->id }})">
                                    Transfer
                                </a>

                                <!-- SHIFT -->
                                <label class="switch">
                                    <input type="checkbox"
                                        onchange="verifyFullShift({{ $first->shift_id }}, this.checked)"
                                        {{ ($status['shift'] ?? '') == 'verify' ? 'checked' : '' }}>
                                    <span class="slider round"></span>
                                </label>
                                <span class="mb-2 mr-2 ml-1">Shift</span>

                            </div>
                        @else
                            <div class="pull-right">

                                <div class="d-flex gap-1 btn btn-sm btn-success ml-2 pull-right">
                                    <span class="text-dark">
                                        Shift Open
                                    </span>
                                </div>
                            </div>
                        @endif
                        <a class="btn btn-sm btn-info view-row open-shift full-right"
                            data-shift="{{ $first->shift_id }}">
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

                                <tr style="border-bottom:1px solid #f1f1f1;"
                                    class="{{ $sale->admin_status == 'verify' ? 'table-success' : 'table-warning' }}">
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

                                    <td class="text-end d-flex align-items-center justify-content-end gap-2">

                                        <a class="badge bg-light text-dark mr-1" href="javascript:void(0)"
                                            onclick="openInvoiceModal({{ $sale->id }})">
                                            <i class="ri-eye-line"></i>
                                        </a>

                                        <a class="badge bg-light text-dark view-invoices mr-1"
                                            href="{{ url('/sales/edit-sales/' . $sale->id) }}?type=admin_sale">
                                            <i class="fa fa-edit"></i>
                                        </a>

                                        {{-- <label class="switch m-0">
                                            <input type="checkbox" 
                                                onchange="verifyInvoice({{ $sale->id }}, this.checked)"
                                                {{ ($sale->admin_status) == 'verify' ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                        </label> --}}

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
                                @php
                                    $tt = $total + $sales->grand_total;

                                @endphp
                                <td>₹{{ number_format($tt, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                        @if ($first->branch->id == 1)
                            <tr style="background:#fff3cd; font-weight:600;">
                                <td colspan="3" class="text-end">+ Credit Collection</td>
                                <td>₹{{ number_format($sales->credit_collection ?? 0, 2) }}</td>
                                <td></td>
                            </tr>
                        @endif

                        <tr style="background:#d1ecf1; font-weight:600;">
                            <td colspan="3" class="text-end">+ Cash In Hand</td>
                            <td>₹{{ number_format($sales->total_cash_in_hand ?? 0, 2) }}</td>
                            <td></td>
                        </tr>

                        <tr style="background:#f8d7da; font-weight:600;">
                            <td colspan="3" class="text-end">- Withdraw</td>
                            <td>₹{{ number_format($sales->total_withdraw ?? 0, 2) }}</td>
                            <td></td>
                        </tr>

                        {{-- <tr style="background:#e2e3e5; font-weight:700;">
                            <td colspan="3" class="text-end">Final Shift Total</td>
                            <td>
                                ₹{{ number_format($total + ($shiftOthersTotal['grand_total'] ?? 0), 2) }}
                            </td>
                            <td></td>
                        </tr> --}}

                    </table>
                </div>

            </td>
        </tr>
    @endforeach
    <tr style="background:#e9ecef; font-weight:700;">
        <td class="ps-3" colspan="1">
            GRAND TOTAL
        </td>
        <td class="text-end pe-3">
            ₹{{ number_format($grandTotal, 2) }}
        </td>
    </tr>
@else
    <tr>
        <td colspan="2" class="text-center text-muted py-4">
            <i class="ri-information-line"></i> No Data Available
        </td>
    </tr>

@endif
