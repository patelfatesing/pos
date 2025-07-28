 <div class="modal fade show d-block" tabindex="-1">
     <div class="modal-dialog modal-dialog-scrollable modal-xl">
         <div class="modal-content shadow-sm rounded-4 border-0">

             {{-- Modal Header --}}
             <div class="modal-header bg-primary text-white rounded-top-4">
                 <div class="d-flex flex-column">
                     <h5 class="modal-title fw-semibold">
                         <i class="bi bi-cash-coin me-2"></i> {{ $shift->shift_no ?? '' }} - Shift Close Summary -
                         {{ $branch_name ?? 'Shop' }}
                     </h5>
                 </div>

                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                 </button>
             </div>

             {{-- Modal Body --}}
             <div class="modal-body px-4 py-4">
                 <form wire:submit.prevent="submit">
                     {{-- Hidden Fields --}}
                     <input type="hidden" wire:model="start_time">
                     <input type="hidden" wire:model="end_time">
                     <input type="hidden" wire:model="opening_cash">
                     <input type="hidden" wire:model="today_cash">
                     <input type="hidden" wire:model="total_payments">
                     <input type="hidden" wire:model="closing_sales">


                     {{-- Sales and Cash Section --}}
                     <div class="row g-4 mb-4">
                         {{-- Sales Breakdown --}}
                         <div class="col-md-6">
                             <div class="card p-4">
                                 <div class="d-flex justify-content-between align-items-center mb-3">
                                     <h4 class="mb-0">Sales Details</h4>

                                     <button type="button" onClick="viewStock({{ $shift->id }})"
                                         class="btn btn-secondary btn-sm" title="View Stock Status">
                                         View Stock Status
                                     </button>
                                 </div>

                                 <hr class="mb-4">

                                 <div class="row">
                                     @foreach ($categoryTotals as $category => $items)
                                         @php
                                             $isSummary = $category == 'summary';
                                             $colClass = $isSummary ? 'col-12 mb-4' : 'col-md-6 mb-4';
                                         @endphp

                                         <div class="{{ $colClass }}">
                                             <div class="card h-100 border-0 shadow-sm">
                                                 <div class="card-header bg-gradient bg-primary text-white">
                                                     <h5 class="mb-0 text-capitalize">{{ ucfirst($category) }}
                                                     </h5>
                                                 </div>
                                                 <div class="card-body p-0">
                                                     <table class="table mb-0">
                                                         <tbody>
                                                             @foreach ($items as $key => $value)
                                                                 @php
                                                                     $isTotal = strtoupper($key) === 'TOTAL';
                                                                     $creditDetails =
                                                                         strtoupper($key) === 'CREDIT' ||
                                                                         strtoupper($key) === 'REFUND_CREDIT'
                                                                             ? '(Excluded from Cash)'
                                                                             : '';

                                                                     $rowClass = $isTotal
                                                                         ? 'table-success fw-bold'
                                                                         : '';
                                                                 @endphp
                                                                 <tr class="{{ $rowClass }}">
                                                                     <td class="text-muted text-capitalize">
                                                                         {{ str_replace('_', ' ', $key) }}
                                                                         <small>{{ @$creditDetails }}</small>
                                                                     </td>
                                                                     <td class="text-end fw-semibold">
                                                                         {{ format_inr($value) }}
                                                                     </td>
                                                                 </tr>
                                                             @endforeach
                                                         </tbody>
                                                     </table>
                                                 </div>
                                             </div>
                                         </div>
                                     @endforeach
                                 </div>
                             </div>

                         </div>

                         {{-- Shift Timing and Cash Details --}}
                         <div class="col-md-6">
                             <div class="card shadow-sm rounded-3">
                                 <div class="card-body p-4">
                                     {{-- Shift Timing --}}
                                     <div class="d-flex justify-content-between align-items-center mb-4">
                                         <div>
                                             <div class="row text-left mt-2">
                                                 <div class="col-6 border-end">
                                                     <div class="small text-muted">Start Time</div>
                                                     <div class="fw-semibold">{{ $shift->start_time ?? '-' }}
                                                     </div>
                                                 </div>
                                                 <div class="col-6">
                                                     <div class="small text-muted">End Time</div>
                                                     <div class="fw-semibold">{{ $shift->end_time ?? '-' }}</div>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>
                                     <hr>
                                     {{-- Cash Breakdown --}}
                                     <h5 class="card-title text-warning text-left mb-3">💵 Cash Details</h5>
                                     @if ($in_out_enable == true)
                                         <div class="table-responsive">
                                             <table class="table table-bordered table-sm text-center align-middle mb-0">
                                                 <thead class="table-light">
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
                                                         @php
                                                             $totalNotes = 0;
                                                         @endphp
                                                         @foreach ($shiftcash as $denomination => $quantity)
                                                             @php
                                                                 $rowTotal = $denomination * $quantity;
                                                                 $totalNotes += $rowTotal;
                                                             @endphp
                                                             <tr>
                                                                 <td class="fw-bold">{{ format_inr($denomination) }}
                                                                 </td>
                                                                 <td>{{ abs($quantity) }}</td>
                                                                 <td>X</td>
                                                                 <td>{{ format_inr($denomination) }}</td>
                                                                 <td>=</td>
                                                                 <td class="fw-bold">{{ format_inr($rowTotal) }}
                                                                 </td>
                                                             </tr>
                                                         @endforeach
                                                     @endif
                                                 </tbody>
                                                 <tfoot class="table-light">
                                                     <tr>
                                                         <th colspan="5" class="text-end">Total</th>
                                                         <th class="fw-bold">
                                                             {{ format_inr(@$totalNotes) }}
                                                         </th>
                                                     </tr>
                                                 </tfoot>
                                             </table>
                                         </div>
                                     @endif

                                     {{-- Summary Cash Totals --}}
                                     <div class="table-responsive mt-4">
                                         <table class="table table-sm">
                                             <tbody>
                                                 <tr>
                                                     <td class="text-start fw-bold">System Cash Sales</td>
                                                     @if ($in_out_enable == true)
                                                         <td class="text-end">{{ format_inr($totalNotes ?? 0) }}
                                                         @else
                                                         <td class="text-end">
                                                             {{ format_inr(@$categoryTotals['summary']['TOTAL']) }}
                                                         </td>
                                                     @endif
                                                     </td>
                                                 </tr>
                                                 <tr>
                                                     <td class="text-start fw-bold">Total Cash Amount</td>
                                                     <td class="text-end">
                                                         {{ format_inr(@$categoryTotals['summary']['TOTAL']) }}
                                                     </td>
                                                 </tr>
                                                 <tr>
                                                     <td class="text-start fw-bold">Closing Cash</td>
                                                     <td class="text-end">
                                                         {{ $closing_cash ?? 0 }}
                                                     </td>
                                                 </tr>
                                                 <tr>
                                                     <td class="text-start fw-bold">Discrepancy Cash</td>
                                                     <td class="text-end">
                                                         {{ $cash_discrepancy ?? 0 }}
                                                     </td>
                                                 </tr>

                                             </tbody>
                                         </table>
                                     </div>

                                 </div>
                             </div>
                         </div>

                     </div>
                 </form>
             </div>

         </div>
     </div>
 </div>

 <div class="modal fade bd-example-modal-lg" id="dailyStockDetailsModal" tabindex="-1" role="dialog"
     aria-labelledby="dailyStockDetailsModalLabel" aria-hidden="true">
     <div class="modal-dialog modal-lg" role="document">
         <div class="modal-content" id="dailyStockDetailsModalContent">
         </div>
     </div>
 </div>