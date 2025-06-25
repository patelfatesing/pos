<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\ShiftClosing;
use App\Models\Invoice;
use App\Models\CashBreakdown;
use Illuminate\Support\Str;
use App\Models\Refund;
use App\Models\CreditHistory;
use App\Models\DailyProductStock;
use App\Models\Branch;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class ShiftManageController extends Controller
{
    public function index()
    {
        $branches = DB::table('branches')->get(); // Adjust if you use a model
        $users = DB::table('users')->get(); // Adjust if you use a model

        return view('shift_manage.index', ['branches' => $branches, 'users' => $users]);
    }

    public function getShiftClosingsData(Request $request)
    {
        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $request->input("columns.$orderColumnIndex.data", 'shift_closings.id');
        $orderDirection = $request->input('order.0.dir', 'asc');

        if (!in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'asc';
        }

        $query = \DB::table('shift_closings')
            ->leftJoin('branches', 'shift_closings.branch_id', '=', 'branches.id')
            ->leftJoin('users', 'shift_closings.user_id', '=', 'users.id')
            ->select(
                'shift_closings.id',
                'shift_closings.branch_id',
                'shift_closings.user_id',
                'branches.name as branch_name',
                'users.name as user_name',
                'shift_closings.start_time',
                'shift_closings.end_time',
                'shift_closings.opening_cash',
                'shift_closings.closing_cash',
                'shift_closings.cash_discrepancy',
                'shift_closings.created_at',
                'shift_closings.status'
            );

        if ($request->start_date && $request->end_date) {
            $query->where('shift_closings.created_at', '>=', Carbon::parse($request->start_date)->setTime(0, 0))
                ->where('shift_closings.created_at', '<=', Carbon::parse($request->end_date)->setTime(23, 59));
        }
        if (!empty($request->branch_id)) {
            $query->where('shift_closings.branch_id', $request->branch_id);
        }
        if (!empty($request->user_id)) {
            $query->where('shift_closings.user_id', $request->user_id);
        }

        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('branches.name', 'like', '%' . $searchValue . '%')
                    ->orWhere('users.name', 'like', '%' . $searchValue . '%');
            });
        }

        $recordsTotal = \DB::table('shift_closings')->count();
        $recordsFiltered = $query->count();

        $data = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        // Get current time for status check
        $now = \Carbon\Carbon::now();

        // Get branch IDs from current page data for invoice counts
        $branchIds = $data->pluck('branch_id')->unique()->toArray();

        // Get total transactions grouped by branch_id
        // $transactions = \DB::table('invoices')
        //     ->select('branch_id', \DB::raw('COUNT(id) as total_transactions'))
        //     ->whereIn('branch_id', $branchIds)
        //     ->groupBy('branch_id')
        //     ->pluck('total_transactions', 'branch_id'); // key = branch_id, value = count

        $records = [];

        foreach ($data as $row) {
            $endTime = $row->end_time ? \Carbon\Carbon::parse($row->end_time) : null;
            $status = "";
            if ($row->status == "pending") {
                $status = "Running";
            } else if ($row->status == "completed" || $row->status == "closing") {
                $status = "Closed";
            }
            $totalInvoicedAmount = \App\Models\Invoice::where('user_id', $row->user_id)
                ->where('branch_id', $row->branch_id)->whereNotIn('status',[ 'Hold','resumed','archived'])
                ->whereBetween('created_at', [$row->start_time, $endTime])
                ->count();
            //$totalSales = $transactions->whereBetween('created_at', [$row->start_time, $endTime])->where('branch_id', $row->branch_id)->get();


            // $endTime = $row->end_time ? Carbon::parse($row->end_time) : null;
            $now = Carbon::now();

            // $action = '<div class="d-flex align-items-center list-action">
            //     <a class="badge bg-info mr-2 view-transactions" 
            //     href="javascript:void(0);" 
            //     data-branch-id="' . $row->branch_id . '" 
            //     data-branch-name="' . $row->branch_name . '"
            //     title="View Transactions">
            //     <i class="ri-eye-line"></i>
            //     </a>';

            // Show "Close Shift" button if end_time is within next 30 minutes
            // if ($endTime && $now->diffInMinutes($endTime, false) >= 0 && $now->diffInMinutes($endTime, false) <= 30) {
            if ($row->status == "pending") {
                $action = '<a class="badge bg-warning ml-2 close-shift" 
                                href="javascript:void(0);" 
                                data-id="' . $row->id . '" 
                                title="Open Shift">
                                <i class="ri-lock-unlock-line"></i> Open Shift
                            </a>';
            } else {
                $action = '<a class="badge bg-secondary ml-2 close-shift" 
                                href="javascript:void(0);" 
                                data-id="' . $row->id . '" 
                                title="Closed">
                                <i class="ri-lock-line"></i> Closed
                            </a>';
            }
            // }
            $action .= '<a class="badge bg-primary ml-2 view-invoices" 
                href="' . url('/shift-manage/view/' . $row->branch_id . "/" . $row->id . "/" . $row->start_time) . "/" . $endTime . '" title="View Transactions">
                <i class="ri-eye-line"></i>
                </a>';

            $action .= '<a class="badge bg-primary ml-2 view-invoices" 
                href="' . url('/shift-manage/' . $row->id) . '" title="View Physical Stock Photo">
                <i class="ri-image-line"></i>
                </a>';
            $action .= '<a class="badge bg-primary ml-2 view-invoices" 
    href="' . url('/shift-manage/print-shift/' . $row->id) . '" title="Print Shift PDF" target="_blank">
    <i class="ri-file-pdf-line"></i>
</a>';

            $action .= '</div>';


            $records[] = [
                'branch_name' => $row->branch_name,
                'user_name' => $row->user_name,
                'start_time' => \Carbon\Carbon::parse($row->start_time)->format('d-m-Y h:i A'),
                'end_time' => $endTime ? $endTime->format('d-m-Y h:i A') : '-',
                'opening_cash' => number_format($row->opening_cash, 2),
                'closing_cash' => number_format($row->closing_cash, 2),
                'status' => $status,
                'total_transaction' => $totalInvoicedAmount,
                'difference' => number_format($row->cash_discrepancy, 2),

                'action' => $action,
            ];
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records,
        ]);
    }

    public function getInvoicesByBranch(Request $request)
    {
        $branchId = $request->input('branch_id');

        $invoices = \DB::table('invoices')
            ->where('branch_id', $branchId)
            ->orderBy('created_at', 'desc')
            ->get([
                'invoice_number',
                'cash_amount',
                'upi_amount',
                'online_amount',
                'creditpay',
                'payment_mode',
                'total_item_qty',
                'sub_total',
                'tax',
                'total',
                'status',
                'created_at',
            ]);

        return response()->json(['data' => $invoices]);
    }

    public function view($id, $shift_id, $strartdate, $endTime)
    {
        $shift = ShiftClosing::findOrFail($shift_id);

        $query = \DB::table('invoices')
            ->where('branch_id', $id)
            ->whereBetween('created_at', [$strartdate, $endTime])
            ->orderBy('created_at', 'desc')
            ->select(
                'invoice_number',
                'cash_amount',
                'upi_amount',
                'online_amount',
                'creditpay',
                'payment_mode',
                'total_item_qty',
                'sub_total',
                'tax',
                'total',
                'status',
                'created_at'
            );

        if ($shift->status == "pending") {
            $query->whereIn('status', ['Paid', 'Hold']);
        } else {
            $query->where('status', 'Paid');
        }

        $invoices = $query->paginate(10);

        return view('shift_manage.view', compact('invoices', 'shift_id'));
    }

    public function closeShift($id, $return = "htmlfile")
    {
        $shift = ShiftClosing::findOrFail($id);
        // dd($shift->user_id);

        $user_data = User::select('name')->where('id', $shift->user_id)->firstOrFail();
        $branch_data = Branch::select('name')->where('id', $shift->branch_id)->firstOrFail();
        $branch_name = $branch_data->name;
        $user_name = $user_data->name;

        if (!$shift->closing_shift_time) {
            // $shift->closing_shift_time = now();
            // $shift->status = 'completed';
            // $shift->save();
            $categoryTotals = [];
            $totals = CreditHistory::whereBetween('created_at', [$shift->start_time, $shift->end_time])
                ->where('store_id', $shift->branch_id)
                ->selectRaw('SUM(credit_amount) as credit_total, SUM(debit_amount) as debit_total')
                ->first();


                $invoices = Invoice::where(['user_id' => $shift->user_id])->where(['branch_id' => $shift->branch_id])->whereBetween('created_at', [$shift->start_time, $shift->end_time])->where('status', '!=', 'Hold')->latest()->get();
                $discountTotal = $totalSales = $totalPaid = $totalRefund = $totalCashPaid = $totalRoundOf = $totalSubTotal = $totalCreditPay = $totalUpiPaid = $totalRefundReturn = $totalOnlinePaid = $totalSalesQty = $totalPaidCredit=0;
                
                $transaction_total = 0;
                $totalSalesNew = 0;
            foreach ($invoices as $invoice) {
                $transaction_total += $transaction_total;
                $items = $invoice->items; // decode items from longtext JSON

                if (is_string($items)) {
                    $items = json_decode($items, true); // decode if not already an array
                }

                if (is_array($items)) {
                    foreach ($items as $item) {
                        if (!empty($item['subcategory'])) {

                            $category =  Str::upper($item['subcategory'])  ?? 'Unknown';
                            $amount = $item['price'] ?? 0;

                            if (!isset($categoryTotals['sales'][$category])) {
                                $categoryTotals['sales'][$category] = 0;
                            }

                            $categoryTotals['sales'][$category] += $amount;
                            $totalSalesNew += $amount;
                        }
                        $totalSalesQty += $item['quantity'] ?? 0;
                    }
                }
                $closing_sales = @$categoryTotals['sales'];
                // $discountTotal += ($invoice->commission_amount ?? 0) + ($invoice->party_amount ?? 0);
                $discountTotal += (!empty($invoice->commission_amount) && is_numeric($invoice->commission_amount)) ? (int)$invoice->commission_amount : 0;
                $discountTotal += (!empty($invoice->party_amount) && is_numeric($invoice->party_amount)) ? (int)$invoice->party_amount : 0;

                $totalCashPaid += (!empty($invoice->cash_amount) && is_numeric($invoice->cash_amount)) ? (int)$invoice->cash_amount : 0;

                $totalSubTotal += (!empty($invoice->total)) ? parseCurrency($invoice->total) : 0;
                $totalUpiPaid  += (!empty($invoice->upi_amount)  && is_numeric($invoice->upi_amount)) ? (int)$invoice->upi_amount  : 0;
                $totalOnlinePaid  += (!empty($invoice->online_amount)  && is_numeric($invoice->online_amount)) ? (int)$invoice->online_amount  : 0;
                $totalRoundOf  += (!empty($invoice->roundof)  && is_numeric($invoice->roundof)) ? (int)$invoice->roundof  : 0;
                $totalPaidCredit  += (!empty($invoice->paid_credit)  && is_numeric($invoice->paid_credit)) ? (int)$invoice->paid_credit  : 0;
                if ($invoice->status == "Returned") {
                    $totalRefundReturn += floatval(str_replace(',', '', $invoice->total));
                }



                $totalCreditPay  += (!empty($invoice->creditpay)  && is_numeric($invoice->creditpay)) ? (int)$invoice->creditpay  : 0;

                $totalSales    += (!empty($invoice->sub_total)   && is_numeric($invoice->sub_total)) ? (int)$invoice->sub_total : 0;
                $totalPaid     += (!empty($invoice->total)       && is_numeric($invoice->total)) ? (int)$invoice->total : 0;
                if ($invoice->status == "Refunded") {
                    $refund = Refund::where('invoice_id', $invoice->id)
                        ->where('user_id', auth()->id())
                        ->first();
                    if ($refund) {
                        $totalRefund     += (!empty($refund->amount)       && is_numeric($refund->amount)) ? (int)$refund->amount : 0;
                    }
                }
            }

            $creditCollacted = \DB::table('credit_collections')
                ->selectRaw('
            SUM(cash_amount) as collacted_cash_amount,
            SUM(online_amount) as collacted_online_amount,
            SUM(upi_amount) as collacted_upi_amount
            ')
                ->whereBetween('created_at', [$shift->start_time, $shift->end_time])
                ->first();
            $todayCash = $totalPaid;
            $categoryTotals['sales']["TOTAL"] = $totalSalesNew;

            $totalWith = \App\Models\WithdrawCash::where('user_id',  $shift->user_id)
                ->where('branch_id', $shift->branch_id)->whereBetween('created_at', [$shift->start_time, $shift->end_time])->sum('amount');
            $categoryTotals['payment']['CASH'] = $totalCashPaid;
            $categoryTotals['payment']['UPI PAYMENT'] =  ($totalUpiPaid + $totalOnlinePaid);
            // $categoryTotals['payment']['totalSalesQty'] =  $totalSalesQty;
            // $categoryTotals['payment']['transactionTotal'] =  $transaction_total;

            $categoryTotals['payment']['TOTAL'] = $totalCashPaid + ($totalUpiPaid + $totalOnlinePaid);
            $categoryTotals['summary']['OPENING CASH'] = @$shift->opening_cash;
            $categoryTotals['summary']['CASH ADDED'] = @$shift->cash_added;
            $categoryTotals['summary']['TOTAL SALES'] =$totals->debit_total+ $totalSubTotal + $discountTotal-$totalRefundReturn;
            $categoryTotals['summary']['DISCOUNT'] = $discountTotal * (-1);
            $categoryTotals['summary']['WITHDRAWAL PAYMENT'] = $totalWith * (-1);
            $categoryTotals['summary']['UPI PAYMENT'] = ($totalUpiPaid + $totalOnlinePaid) * (-1);
            $categoryTotals['summary']['ROUND OFF'] = $totalRoundOf;
            $categoryTotals['summary']['CREDIT'] = $totals->debit_total *(-1);

            //$categoryTotals['summary']['ONLINE PAYMENT'] = $totalOnlinePaid * (-1);
            if (!empty($creditCollacted->collacted_cash_amount))
                $categoryTotals['summary']['CREDIT COLLACTED BY CASH'] = $creditCollacted->collacted_cash_amount;
            // $categoryTotals['summary']['REFUND'] += $totalRefundReturn *(-1);
            $categoryTotals['summary']['TOTAL'] = $categoryTotals['summary']['OPENING CASH'] + $categoryTotals['summary']['TOTAL SALES'] + $categoryTotals['summary']['DISCOUNT'] + $categoryTotals['summary']['WITHDRAWAL PAYMENT'] + $categoryTotals['summary']['UPI PAYMENT'] + @$categoryTotals['summary']['REFUND'] +
                @$categoryTotals['summary']['ONLINE PAYMENT'] + @$categoryTotals['summary']['CREDIT COLLACTED BY CASH'] - $totalRoundOf+$categoryTotals['summary']['CREDIT'];
            $categoryTotals['summary']['REFUND'] = $totalRefund * (-1) + $totalRefundReturn * (-1);
            //$categoryTotals['summary']['REFUND RETURN'] = $totalRefundReturn*(-1);
            //$categoryTotals['summary']['CREDIT'] = $totals->debit_total;
            //$categoryTotals['summary']['REFUND_CREDIT'] = $totals->credit_total;
            if (!empty($categoryTotals['summary']['REFUND_CREDIT'])) {
                $categoryTotals['summary']['REFUND_CREDIT'] = (int)$categoryTotals['summary']['REFUND_CREDIT'] * (-1);
            }

            $cashBreakdowns = CashBreakdown::where('user_id', $shift->user_id)
                ->where('branch_id', $shift->branch_id)
                // ->where('type', '!=', 'cashinhand')
                ->whereBetween('created_at', [$shift->start_time, $shift->end_time])
                ->get();

            $noteCount = [];

            foreach ($cashBreakdowns as $breakdown) {
                $denominations1 = json_decode($breakdown->denominations, true);
                // echo "<pre>";
                // print_r($denominations1);
                if (is_array($denominations1)) {
                    // Handle array of objects: [{"10":{"in":"0"}},{"20":{"in":"0"}},...]
                    if (array_keys($denominations1) === range(0, count($denominations1) - 1)) {
                        foreach ($denominations1 as $item) {
                            if (is_array($item)) {
                                foreach ($item as $noteValue => $action) {
                                    if (isset($action['in'])) {
                                        if (!isset($noteCount[$noteValue])) {
                                            $noteCount[$noteValue] = 0;
                                        }
                                        $noteCount[$noteValue] += (int)$action['in'];
                                    }
                                    if (isset($action['out'])) {
                                        if (!isset($noteCount[$noteValue])) {
                                            $noteCount[$noteValue] = 0;
                                        }
                                        $noteCount[$noteValue] -= (int)$action['out'];
                                    }
                                }
                            }
                        }
                    } else {
                        // Handle object with nested arrays: {"5":{"500":{"in":4}}, "3":{"100":{"out":1}}}
                        foreach ($denominations1 as $outer) {
                            if (is_array($outer)) {
                                foreach ($outer as $noteValue => $action) {
                                    if (isset($action['in'])) {
                                        if (!isset($noteCount[$noteValue])) {
                                            $noteCount[$noteValue] = 0;
                                        }
                                        $noteCount[$noteValue] += (int)$action['in'];
                                    }
                                    if (isset($action['out'])) {
                                        if (!isset($noteCount[$noteValue])) {
                                            $noteCount[$noteValue] = 0;
                                        }
                                        $noteCount[$noteValue] -= (int)$action['out'];
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $totalOpeningStock = DailyProductStock::where('shift_id', $id)
                ->sum('opening_stock');
            // dd($totalOpeningStock);

            $shiftcash = $noteCount;
            $closing_cash = $shift->closing_cash;
            $cash_discrepancy = $shift->cash_discrepancy;
            //dd($shiftcash);
            // Render a Blade view and pass any needed data
            $html = view('shift_manage.closed', ['opening_stock' => $totalOpeningStock, 'user_name' => $user_name, 'shift' => $shift, "categoryTotals" => $categoryTotals, "shiftcash" => $shiftcash, "closing_cash" => $closing_cash, 'cash_discrepancy' => $cash_discrepancy, 'branch_name' => $branch_name])->render();
            if ($return == "html") {
                return  ['user_name' => $user_name, 'shift' => $shift, "categoryTotals" => $categoryTotals, "shiftcash" => $shiftcash, "closing_cash" => $closing_cash, 'cash_discrepancy' => $cash_discrepancy, 'branch_name' => $branch_name];
            } else {

                return response()->json([
                    'message' => 'Shift closed successfully',
                    'html' => $html,
                    'code' => 200
                ]);
            }
        }

        return response()->json([
            'message' => 'Shift already closed',
            'code' => 400

        ], 200);
    }

    public function showPhoto($id)
    {

        $shift = ShiftClosing::findOrFail($id);
        return view('shift_manage.photo', compact('shift'));
    }

    public function stockDetails($id)
    {
        $shift = ShiftClosing::findOrFail($id);

        $branch_id = $shift->branch_id;

        $rawStockData = DailyProductStock::with('product')
            ->where('branch_id', $branch_id)
            ->where('shift_id', $id)
            ->get();

        return view('shift_manage.stock_details', compact('rawStockData'));
    }

    public function printShift($id)
    {
        $shift = ShiftClosing::findOrFail($id);

        if (!$shift->closing_shift_time) {
            $closeShift = $this->closeShift($id, "html");

            $pdf = Pdf::loadView('shift_manage.shift_print', ['user_name' => $closeShift['user_name'], 'shift' => $closeShift['shift'], "categoryTotals" => $closeShift['categoryTotals'], "shiftcash" => $closeShift['shiftcash'], "closing_cash" => $closeShift['closing_cash'], 'cash_discrepancy' => $closeShift['cash_discrepancy'], 'branch_name' => $closeShift['branch_name']]);

            return $pdf->download('shift_report_' . $shift->id . '.pdf');
        }

        return response()->json([
            'message' => 'Shift already closed',
            'code' => 400
        ], 200);
    }
}
