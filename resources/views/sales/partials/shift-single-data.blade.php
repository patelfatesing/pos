@foreach ($grouped as $key => $sales)

    @php
        $first = $sales->first();

        $subTotal = 0;
        $total = 0;
        $discount = 0;
    @endphp

    <div class="mb-3 border rounded p-2">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-2">

            <h6 class="mb-0">
                {{ $shift->branch->name ?? '-' }}
                ({{ \Carbon\Carbon::parse($shift->created_at)->format('d-m-Y') }})
            </h6>

            <a href="javascript:void(0)"
                class="btn btn-success"
                onclick="openAddSalesModal({{ $shift->branch_id }}, {{ $shift->id }})">

                + Add Sales

            </a>

        </div>

        <div class="table-responsive">

            <table class="table table-sm mb-0">

                {{-- TABLE HEADER --}}
                <thead style="background:#f1f3f5;">

                    <tr>

                        @if (($shift->branch_id ?? 0) == 1)
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

                {{-- TABLE BODY --}}
                <tbody>

                    @forelse ($sales as $sale)

                        @php

                            $rowTotal = (float) str_replace(',', '', $sale->total ?? 0);

                            $rowSubTotal = (float) ($sale->sub_total ?? 0);

                            $rowDiscount =
                                (float) ($sale->party_amount ?? 0)
                                + (float) ($sale->commission_amount ?? 0);

                            $total += $rowTotal;

                            $subTotal += $rowSubTotal;

                            $discount += $rowDiscount;

                        @endphp

                        <tr class="{{ ($sale->admin_status ?? '') == 'verify'
                            ? 'table-success'
                            : 'table-warning' }}">

                            {{-- USER --}}
                            <td>

                                @if (($sale->branch_id ?? 0) == 1)

                                    {{ $sale->partyUser->first_name ?? '-' }}

                                @else

                                    {{ $sale->commissionUser->first_name ?? '-' }}

                                @endif

                            </td>

                            {{-- DISCOUNT --}}
                            <td>
                                {{ number_format($rowDiscount, 2) }}
                            </td>

                            {{-- SUB TOTAL --}}
                            <td>
                                {{ number_format($rowSubTotal, 2) }}
                            </td>

                            {{-- TOTAL --}}
                            <td>
                                ₹{{ number_format($rowTotal, 2) }}
                            </td>

                            {{-- ACTION --}}
                            <td class="text-end">

                                <div class="d-flex justify-content-end">

                                    @if ($sale->id)

                                        {{-- VIEW --}}
                                        <a class="badge bg-light text-dark mr-1"
                                            onclick="openInvoiceModal({{ $sale->id }})">

                                            <i class="fa fa-eye"></i>

                                        </a>

                                        {{-- EDIT --}}
                                        <a class="badge bg-warning text-dark"
                                            onclick="editInvoiceModal({{ $sale->id }})">

                                            <i class="fa fa-edit"></i>

                                        </a>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        {{-- EMPTY SALES --}}
                        <tr>

                            <td colspan="5"
                                class="text-center text-muted py-4">

                                No Sales Found

                            </td>

                        </tr>

                    @endforelse

                </tbody>

                {{-- FOOTER --}}
                <tfoot>

                    {{-- TOTAL --}}
                    <tr style="background:#f8f9fa;font-weight:bold;">

                        <td>Total</td>

                        <td>
                            {{ number_format($discount, 2) }}
                        </td>

                        <td>
                            {{ number_format($subTotal, 2) }}
                        </td>

                        <td>
                            ₹{{ number_format($total + ($sales->grand_total ?? 0), 2) }}
                        </td>

                        <td></td>

                    </tr>

                    {{-- CASH IN HAND --}}
                    <tr style="background:#d1ecf1;">

                        <td colspan="3" class="text-end">

                            + Cash In Hand

                        </td>

                        <td>

                            ₹{{ number_format($sales->total_cash_in_hand ?? 0, 2) }}

                        </td>

                        <td></td>

                    </tr>

                    {{-- WITHDRAW --}}
                    <tr style="background:#f8d7da;">

                        <td colspan="3" class="text-end">

                            - Withdraw

                        </td>

                        <td>

                            ₹{{ number_format($sales->total_withdraw ?? 0, 2) }}

                        </td>

                        <td></td>

                    </tr>

                </tfoot>

            </table>

        </div>

    </div>

@endforeach
