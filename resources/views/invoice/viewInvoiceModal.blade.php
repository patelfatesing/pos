<div class="container-fluid p-0">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-block card-stretch card-height print rounded border-0 shadow-none">
                <div class="card-body p-0">

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="table-responsive-sm">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th class="text-center" scope="col">#</th>
                                            <th scope="col">Item</th>
                                            <th class="text-center" scope="col">Quantity</th>
                                            <th class="text-center" scope="col">Price</th>
                                            <th class="text-center" scope="col">Totals</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($invoice->items as $i => $item)
                                            <tr>
                                                <th class="text-center" scope="row">{{ $i + 1 }}</th>
                                                <td><h6 class="mb-0">{{ $item['name'] }}</h6></td>
                                                <td class="text-center">{{ $item['quantity'] }}</td>
                                                <td class="text-center">
                                                    @if ($item['sell_price'] > $item['mrp'])
                                                        <span style="text-decoration: line-through; color: #999;">
                                                            ₹{{ number_format($item['sell_price'], 2) }}
                                                        </span>
                                                        <br>
                                                        <span class="text-success font-weight-bold">
                                                            ₹{{ number_format((float) str_replace(',', '', $item['mrp']), 2) }}
                                                        </span>
                                                    @else
                                                        <span class="font-weight-bold">
                                                            ₹{{ number_format($item['sell_price'], 2) }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <b>₹{{ $item['price'] }}</b>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4 mb-3">
                        <div class="offset-lg-8 col-lg-4">
                            <div class="card border-0 shadow-sm" style="border-radius: 10px; overflow: hidden;">
                                <div class="px-3 py-2 text-white d-flex align-items-center" style="background-color: #f36c3d;">
                                    <i class="ri-shopping-cart-line mr-2" style="font-size: 18px;"></i>
                                    <h6 class="mb-0 text-white font-weight-bold">Transaction Details</h6>
                                </div>
                                <div class="p-3 bg-white">
                                    <div class="mb-2 d-flex justify-content-between">
                                        <span class="text-muted">Payment Mode:</span>
                                        <span class="font-weight-bold text-dark">{{ $invoice->payment_mode }}</span>
                                    </div>
                                    <div class="mb-2 d-flex justify-content-between">
                                        <span class="text-muted">Credit:</span>
                                        <span class="font-weight-bold text-dark">{{ $invoice->creditpay != '' ? '₹' . number_format($invoice->creditpay, 2) : '-' }}</span>
                                    </div>

                                    <div class="mb-2 d-flex justify-content-between">
                                        <span class="text-muted">Sub Total:</span>
                                        <span class="font-weight-bold text-primary">₹{{ number_format($invoice->sub_total, 2) }}</span>
                                    </div>

                                    @if ($invoice->commission_amount > 0)
                                        <div class="mb-2 d-flex justify-content-between">
                                            <span class="text-muted">Commission Deduction:</span>
                                            <span class="font-weight-bold text-danger">- ₹{{ number_format($invoice->commission_amount, 2) }}</span>
                                        </div>
                                    @endif

                                    @if ($invoice->party_amount > 0)
                                        <div class="mb-2 d-flex justify-content-between">
                                            <span class="text-muted">Party Deduction:</span>
                                            <span class="font-weight-bold text-danger">- ₹{{ number_format($invoice->party_amount, 2) }}</span>
                                        </div>
                                    @endif

                                    @if ($invoice->roundof > 0)
                                        <div class="mb-2 d-flex justify-content-between">
                                            <span class="text-muted">Round off:</span>
                                            <span class="font-weight-bold text-dark">₹{{ number_format($invoice->roundof, 2) }}</span>
                                        </div>
                                    @endif

                                    @if ($invoice->payment_mode == 'cashupi')
                                        <hr class="my-2" style="border-top: 1px dashed #ddd;">
                                        <div class="mb-2 d-flex justify-content-between">
                                            <span class="text-muted">By CASE:</span>
                                            <span class="font-weight-bold text-dark">₹{{ number_format($invoice->cash_amount, 2) }}</span>
                                        </div>
                                        <div class="mb-2 d-flex justify-content-between">
                                            <span class="text-muted">By UPI:</span>
                                            <span class="font-weight-bold text-dark">₹{{ number_format($invoice->upi_amount, 2) }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="py-2 px-3 d-flex justify-content-between align-items-center text-white" style="background-color: #20c0e8;">
                                    <h6 class="mb-0 text-white font-weight-bold">Total</h6>
                                    <h4 class="mb-0 text-white font-weight-bold">
                                        @php
                                            $cleanTotal = floatval(str_replace(',', '', $invoice->sub_total ?? 0));
                                            $cleanRoundof = floatval(str_replace(',', '', $invoice->roundof ?? 0));
                                            $commisson = 0;
                                            if ($invoice->commission_amount > 0) {
                                                $commisson = floatval(str_replace(',', '', $invoice->commission_amount ?? 0));
                                            }
                                            if ($invoice->party_amount > 0) {
                                                $commisson = floatval(str_replace(',', '', $invoice->party_amount ?? 0));
                                            }
                                            $grandTotal = $invoice->roundof > 0 ? ($cleanTotal - $commisson + $cleanRoundof) : ($cleanTotal - $commisson);
                                        @endphp
                                        ₹{{ number_format($grandTotal, 2) }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic Action Buttons for Modal Header -->
<div id="dynamicHeaderButtons" class="d-none">
    @if ($invoice->admin_status == 'verify' && $invoice->super_admin_status != 'verify')
        <span class="mr-2 font-weight-bold" style="color: #FFE600;">Verify Sub Admin</span>
    @endif

    @if ($invoice->super_admin_status == 'verify')
        <span class="mr-2 font-weight-bold" style="color: #FFE600;">Verify this invoice</span>
    @endif

    @if ($invoice->party_user_id != '')
        <button onClick="showPhoto({{ $invoice->id }},'',{{ $invoice->party_user_id }})" class="btn btn-sm btn-success mr-2">
            <i class="ri-eye-line mr-0"></i> View Photos
        </button>
    @endif

    @if ($invoice->commission_user_id != '')
        <button onClick="showPhoto({{ $invoice->id }},{{ $invoice->commission_user_id }},'')" class="btn btn-sm btn-success mr-2">
            <i class="ri-eye-line mr-0"></i> View Photos
        </button>
    @endif

    <a href="{{ route('invoice.download', $invoice->id) }}" class="btn btn-sm btn-success">
        <i class="las la-file-download"></i> Download Invoice
    </a>
</div>

<script>
    $('#invoiceHeaderActions').html($('#dynamicHeaderButtons').html());
</script>