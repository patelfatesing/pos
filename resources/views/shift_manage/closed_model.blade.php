 <div class="modal fade show d-block" tabindex="-1">
     <div class="modal-dialog modal-dialog-scrollable modal-xl">
         <div class="modal-content shadow-sm rounded-4 border-0">

             {{-- Modal Header --}}
             <div
                 class="modal-header card-header d-flex flex-wrap align-items-center justify-content-between rounded-top-4">
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
                                 <div class="d-flex justify-content-between align-items-center">
                                     <h4 class="mb-0">Sales Details</h4>

                                     <button type="button" onClick="viewStock({{ $shift->id }})"
                                         class="btn btn-secondary btn-sm" title="View Stock Status">
                                         View Stock Status
                                     </button>
                                 </div>

                                 <hr class="mb-1">

                                 <div class="row">
                                     @foreach ($categoryTotals as $category => $items)
                                         @php
                                             $isSummary = $category == 'summary';
                                             $colClass = $isSummary ? 'col-12 mb-4' : 'col-md-6 mb-4';
                                         @endphp

                                         <div class="{{ $colClass }}">
                                             <div class="card h-100 border-0 shadow rounded-4 overflow-hidden">

                                                 {{-- Header --}}
                                                 @php
                                                     // ✅ Remove TOTAL from item count
                                                     $filteredItems = collect($items)->reject(function ($v, $k) {
                                                         return strtoupper($k) === 'TOTAL';
                                                     });

                                                     $itemCount = $filteredItems->count();
                                                 @endphp

                                                 <div class="card-header d-flex justify-content-between align-items-center px-3 py-2"
                                                     style="background: linear-gradient(135deg, #36b9cc, #1c7c8c);">

                                                     <h6 class="mb-0 text-white text-uppercase fw-bold">
                                                         {{ ucfirst($category) }}
                                                     </h6>

                                                     <span class="badge bg-light text-dark fw-semibold">
                                                         {{ $itemCount }} {{ $itemCount == 1 ? 'Item' : 'Items' }}
                                                     </span>
                                                 </div>

                                                 {{-- Body --}}
                                                 <div class="card-body p-0">
                                                     <table class="table table-borderless mb-0 align-middle">
                                                         <tbody>

                                                             @foreach ($items as $key => $value)
                                                                 @php
                                                                     $isTotal = strtoupper($key) === 'TOTAL';

                                                                     $creditDetails =
                                                                         strtoupper($key) === 'CREDIT' ||
                                                                         strtoupper($key) === 'REFUND_CREDIT'
                                                                             ? '(Excluded from Cash)'
                                                                             : '';
                                                                 @endphp

                                                                 <tr class="border-bottom {{ $isTotal ? 'bg-light fw-bold' : '' }}"
                                                                     style="transition: 0.2s;">

                                                                     {{-- Label --}}
                                                                     <td class="px-3 py-2 text-capitalize text-muted">
                                                                         <span
                                                                             class="{{ $isTotal ? 'text-dark fw-bold' : '' }}">
                                                                             {{ str_replace('_', ' ', $key) }}
                                                                         </span>

                                                                         @if ($creditDetails)
                                                                             <div class="small text-danger">
                                                                                 {{ $creditDetails }}
                                                                             </div>
                                                                         @endif
                                                                     </td>

                                                                     {{-- Value --}}
                                                                     <td class="px-3 py-2 text-end">
                                                                         <span
                                                                             class="{{ $isTotal ? 'text-success fs-6 fw-bold' : 'fw-semibold text-dark' }}">
                                                                             {{ format_inr($value) }}
                                                                         </span>
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
                             <div class="card border-0 shadow rounded-4 overflow-hidden">

                                 {{-- Header --}}
                                 <div class="card-header d-flex justify-content-between align-items-center px-3 py-2"
                                     style="background: linear-gradient(135deg, #36b9cc, #1c7c8c);">

                                     <h6 class="mb-0 text-white fw-bold text-uppercase">
                                         Shift Details
                                     </h6>

                                     <span class="badge bg-light text-dark fw-semibold">
                                         {{ $shift->id ?? 'Shift' }}
                                     </span>
                                 </div>

                                 <div class="card-body p-3">

                                     {{-- Shift Timing --}}
                                     <div class="row text-center mb-3">
                                         <div class="col-6 border-end">
                                             <div class="small text-muted">Start Time</div>
                                             <div class="fw-bold text-dark">
                                                 {{ $shift->start_time ?? '-' }}
                                             </div>
                                         </div>
                                         <div class="col-6">
                                             <div class="small text-muted">End Time</div>
                                             <div class="fw-bold text-dark">
                                                 {{ $shift->end_time ?? '-' }}
                                             </div>
                                         </div>
                                     </div>

                                     <hr class="my-3">

                                     {{-- Cash Details --}}
                                     <h6 class="fw-bold text-warning mb-3">💵 Cash Details</h6>

                                     @if ($in_out_enable == true)
                                         <div class="table-responsive">
                                             <table class="table table-borderless align-middle mb-0">
                                                 <thead class="border-bottom">
                                                     <tr class="text-muted small text-uppercase">
                                                         <th>Denomination</th>
                                                         <th class="text-center">Notes</th>
                                                         <th class="text-center">×</th>
                                                         <th class="text-center">Value</th>
                                                         <th class="text-center">=</th>
                                                         <th class="text-end">Total</th>
                                                     </tr>
                                                 </thead>

                                                 <tbody>
                                                     @php $totalNotes = 0; @endphp

                                                     @foreach ($shiftcash as $denomination => $quantity)
                                                         @php
                                                             $rowTotal = $denomination * $quantity;
                                                             $totalNotes += $rowTotal;
                                                         @endphp

                                                         <tr class="border-bottom">
                                                             <td class="fw-semibold text-dark">
                                                                 {{ format_inr($denomination) }}
                                                             </td>

                                                             <td class="text-center">
                                                                 {{ abs($quantity) }}
                                                             </td>

                                                             <td class="text-center text-muted">×</td>

                                                             <td class="text-center text-muted">
                                                                 {{ format_inr($denomination) }}
                                                             </td>

                                                             <td class="text-center text-muted">=</td>

                                                             <td class="text-end fw-bold">
                                                                 {{ format_inr($rowTotal) }}
                                                             </td>
                                                         </tr>
                                                     @endforeach
                                                 </tbody>

                                                 {{-- Total --}}
                                                 <tfoot>
                                                     <tr class="bg-light fw-bold">
                                                         <td colspan="5" class="text-end">
                                                             Total
                                                         </td>
                                                         <td class="text-end text-success">
                                                             {{ format_inr($totalNotes) }}
                                                         </td>
                                                     </tr>
                                                 </tfoot>
                                             </table>
                                         </div>
                                     @endif

                                     {{-- Summary --}}
                                     <div class="mt-4">

                                         <div class="d-flex justify-content-between py-2 border-bottom">
                                             <span class="fw-semibold text-muted">System Cash Sales</span>
                                             <span class="fw-bold">
                                                 {{ $in_out_enable ? format_inr($totalNotes ?? 0) : format_inr(@$categoryTotals['summary']['TOTAL']) }}
                                             </span>
                                         </div>

                                         <div class="d-flex justify-content-between py-2 border-bottom">
                                             <span class="fw-semibold text-muted">Total Cash Amount</span>
                                             <span class="fw-bold">
                                                 {{ format_inr(@$categoryTotals['summary']['TOTAL']) }}
                                             </span>
                                         </div>

                                         <div class="d-flex justify-content-between py-2 border-bottom">
                                             <span class="fw-semibold text-muted">Closing Cash</span>
                                             <span class="fw-bold text-primary">
                                                 {{ format_inr($closing_cash ?? 0) }}
                                             </span>
                                         </div>

                                         <div class="d-flex justify-content-between py-2">
                                             <span class="fw-semibold text-muted">Discrepancy Cash</span>
                                             <span
                                                 class="fw-bold {{ ($cash_discrepancy ?? 0) != 0 ? 'text-danger' : 'text-success' }}">
                                                 {{ format_inr($cash_discrepancy ?? 0) }}
                                             </span>
                                         </div>

                                     </div>

                                 </div>

                                 {{-- Footer Highlight --}}
                                 <div class="card-footer bg-success text-white text-center fw-bold py-2">
                                     Net Cash: {{ format_inr(@$categoryTotals['summary']['TOTAL']) }}
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

 <script>
     function viewStock(id) {
         window.location.href = '{{ url('shift-manage/stock-details') }}/' + id;

         //  $.ajax({
         //      url: '{{ url('shift-manage/stock-details') }}/' + id,
         //      type: 'POST',
         //      success: function(response) {
         //          $('#dailyStockDetailsModalContent').html(response);
         //          $('#dailyStockDetailsModal').modal('show');
         //      },
         //      error: function() {
         //          alert('Photos not found.');
         //      }
         //  });
     }
 </script>
