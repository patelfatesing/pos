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
use App\Models\StockRequest;
use App\Models\StockTransfer;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\InvoiceActivityLog;
use App\Models\Accounting\Voucher;

class ShiftManageController extends Controller
{
    public function index()
    {
        if (auth()->user()->role_id == 1 || canDo(auth()->user()->role_id, 'Shift-manage')) {
            $branches = DB::table('branches')->get(); // Adjust if you use a model
            $users = DB::table('users')->get(); // Adjust if you use a model

            return view('shift_manage.index', ['branches' => $branches, 'users' => $users]);
        } else {
            return view('errors.403', [
                'message' => 'You do not have permission to view this stock request.'
            ]);
        }
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
                'shift_closings.shift_no',
                'physical_photo',
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

        if (auth()->user()->role_id == 1) {
            $query->where('admin_status', 'verify');
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
        $roleId = auth()->user()->role_id;

        // Get total transactions grouped by branch_id
        // $transactions = \DB::table('invoices')
        //     ->select('branch_id', \DB::raw('COUNT(id) as total_transactions'))
        //     ->whereIn('branch_id', $branchIds)
        //     ->groupBy('branch_id')
        //     ->pluck('total_transactions', 'branch_id'); // key = branch_id, value = count

        $records = [];

        foreach ($data as $row) {
            // $ownerId = $row->created_by;
            $img = $row->physical_photo;
            $endTime = $row->end_time ? \Carbon\Carbon::parse($row->end_time) : null;
            $status = "";
            if ($row->status == "pending") {
                $status = "Running";
            } else if ($row->status == "completed" || $row->status == "closing") {
                $status = "Closed";
            }

            // $totalInvoicedAmount = \App\Models\Invoice::where('user_id', $row->user_id)
            //     ->where('branch_id', $row->branch_id)->whereNotIn('status', ['Hold', 'resumed', 'archived'])
            //     ->whereBetween('created_at', [$row->start_time, $endTime])
            //     ->count();

            $invoiceQuery = \App\Models\Invoice::where('branch_id', $row->branch_id)
                ->whereNotIn('status', ['Hold', 'resumed', 'archived'])
                ->whereBetween('created_at', [$row->start_time, $endTime]);

            // ✅ Apply same logic as invoice list
            if (auth()->user()->role_id == 1) {
                $invoiceQuery->where('admin_status', 'verify');
            }

            $totalInvoicedAmount = $invoiceQuery->count();

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
            // if (canDo($roleId, 'view-transactions', $ownerId)) {
            $action .= '<a class="badge bg-primary ml-2 view-invoices" 
                href="' . url('/shift-manage/view/' . $row->branch_id . "/" . $row->id) . '" title="View Transactions">
                <i class="ri-eye-line"></i>
                </a>';
            // }

            $action .= '<a class="badge bg-primary ml-2 view-image-btn"
                    href="javascript:void(0);"
                    data-image="' . e($img) . '"
                    title="View Physical Stock Photo">
                    <i class="ri-image-line"></i>
                </a>';

            // $action .= '<a class="badge bg-primary ml-2 view-invoices" 
            //     href="' . url('/shift-manage/' . $row->id) . '" title="View Physical Stock Photo">
            //     <i class="ri-image-line"></i>
            //     </a>';
            // if (canDo($roleId, 'view-physical-stock-photo', $ownerId)) {
            // $action .= '<a class="badge bg-primary ml-2 view-invoices" 
            //     href="javascript:void(0);" onclick="showImage(getImagePath(\'' . $row->physical_photo . '\'))" title="View Physical Stock Photo">
            //     <i class="ri-image-line"></i>
            // </a>';
            // }

            // if (canDo($roleId, 'print-shift-PDF', $ownerId)) {
            $action .= '<a class="badge bg-primary ml-2 view-invoices" 
                    href="' . url('/shift-manage/print-shift/' . $row->id) . '" title="Print Shift PDF" target="_blank">
                    <i class="ri-file-pdf-line"></i>
                </a>';
            // }

            $action .= '</div>';


            $records[] = [
                'shift_no' => $row->shift_no,
                'branch_name' => $row->branch_name,
                'user_name' => $row->user_name,
                'start_time' => \Carbon\Carbon::parse($row->start_time)->format('d-m-Y h:i A'),
                'end_time' => $endTime ? $endTime->format('d-m-Y h:i A') : '-',
                'opening_cash' => number_format($row->opening_cash, 0),
                'closing_cash' => number_format($row->closing_cash, 0),
                'status' => $status,
                'total_transaction' => $totalInvoicedAmount,
                'difference' => number_format($row->cash_discrepancy, 0),

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

    public function view($id, $shift_id, Request $request)
    {
        $verify = '';
        if (isset($_GET['verify'])) {
            $verify = $_GET['verify'];
        }

        $shift = ShiftClosing::findOrFail($shift_id);
        $branch = Branch::findOrFail($id);
        $branch_name = $branch->name;

        $query = \DB::table('invoices')
            ->where('branch_id', $id)
            ->whereBetween('created_at', [$shift->start_time, $shift->end_time]);

        $partyUsers = DB::table('party_users')
            ->select('id', 'first_name')
            ->where('is_delete', 'No')->where('status', 'Active')->orderBy('first_name')->get();

        $commissionUsers = DB::table('commission_users')
            ->select('id', 'first_name')
            ->where('is_deleted', 'No')->where('status', 'Active')->orderBy('first_name')->get();


        return view('shift_manage.view', compact(
            'shift_id',
            'branch_name',
            'id',
            'partyUsers',
            'commissionUsers',
            'verify'
        ));
    }

    public function getData(Request $request)
    {
        $query = DB::table('invoices')
            ->join('branches', 'invoices.branch_id', '=', 'branches.id')
            ->leftJoin('party_users', 'invoices.party_user_id', '=', 'party_users.id')
            ->leftJoin('commission_users', 'invoices.commission_user_id', '=', 'commission_users.id')
            ->select(
                'invoices.id',
                'invoices.invoice_number',
                'invoices.party_amount',
                'invoices.status',
                'invoices.sub_total',
                'invoices.tax',
                'invoices.commission_amount',
                'invoices.creditpay',
                'invoices.total',
                'invoices.items',
                'invoices.branch_id',
                'invoices.sales_type',
                'branches.name as branch_name',
                'invoices.created_at',
                'invoices.commission_user_id',
                'invoices.payment_mode',
                'invoices.party_user_id',
                'party_users.first_name as party_user',
                'commission_users.first_name as commission_user',
                'invoices.edit_in' // Include the 'edit_in' field
            );

        // Date filter
        if ($request->start_date && $request->end_date) {
            // $query->whereBetween('invoices.created_at', [
            //     Carbon::parse($request->start_date)->startOfDay(),
            //     Carbon::parse($request->end_date)->endOfDay(),
            // ]);
        }

        // Filter for last 7 days
        // $query->where('invoices.created_at', '>=', Carbon::now()->subDays(7));

        // Role-based filter
        if (auth()->user()->role_id == 1) {
            $query->where('invoices.admin_status', 'verify');
        }

        $query->where('invoices.status', '!=', 'Hold');

        if (!empty($request->branch_id)) {
            $query->where('invoices.branch_id', $request->branch_id);
        }

        $verify = '';
        if (!empty($request->verify)) {
            $verify = "?verify=" . $request->verify;
        }

        if (!empty($request->shift_id)) {
            $shift = ShiftClosing::findOrFail($request->shift_id);
            $query->where('invoices.shift_id', $request->shift_id);

            // $query->whereBetween('invoices.created_at', [$shift->start_time, $shift->end_time]);
        }


        // Party filter
        if (!empty($request->party_user_id)) {
            $query->where('invoices.party_user_id', $request->party_user_id);
        }

        // Type filter
        if (!empty($request->type)) {
            if ($request->type == 'commission') {
                $query->whereNotNull('invoices.commission_user_id');
            } elseif ($request->type == 'one_time') {
                $query->where('invoices.sales_type', 'one_time');
            }
        }

        $totalRecords = $query->count();

        // Search filter
        if (!empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('invoices.invoice_number', 'like', "%{$searchValue}%")
                    ->orWhere('invoices.status', 'like', "%{$searchValue}%")
                    ->orWhere('branches.name', 'like', "%{$searchValue}%")
                    ->orWhere('party_users.first_name', 'like', "%{$searchValue}%")
                    ->orWhere('commission_users.first_name', 'like', "%{$searchValue}%")
                    ->orWhere('invoices.items', 'like', "%{$searchValue}%");
            });
        }

        $filteredRecords = $query->count();

        // Column map — MUST match frontend column order
        $columns = [
            'invoices.id',              // 0
            'invoices.invoice_number',  // 1
            'party_users.first_name',   // 2
            'commission_users.first_name', // 3
            'invoices.commission_amount',  // 4
            'invoices.party_amount',    // 5
            'invoices.creditpay',       // 6
            'invoices.sub_total',       // 7
            'invoices.total',           // 8
            'invoices.items',           // 9 (items_count)
            'branches.name',            // 10
            'invoices.status',          // 11
            'invoices.payment_mode',    // 12
            'invoices.created_at',       // 13 ✅
            'invoices.edit_in'          // 14 (for View History condition)
        ];

        if ($request->order) {
            $orderColumnIndex = $request->order[0]['column'];
            $orderDir = $request->order[0]['dir'] ?? 'desc';
            $orderColumn = $columns[$orderColumnIndex] ?? 'invoices.created_at';
            $query->orderBy($orderColumn, $orderDir);
        } else {
            $query->orderBy('invoices.created_at', 'desc');
        }

        // Pagination
        if ($request->length > 0) {
            $query->skip($request->start)->take($request->length);
        }

        $invoices = $query->get();

        $data = [];

        foreach ($invoices as $invoice) {
            $items = json_decode($invoice->items, true);
            $itemCount = collect($items)->sum('quantity');

            // Determine if Edit button should be shown (last 7 days)
            $showEditButton = Carbon::parse($invoice->created_at)->greaterThanOrEqualTo(Carbon::now()->subDays(7));

            // Determine if View History button should be shown (edit_in == 'yes')
            $showViewHistoryButton = $invoice->edit_in === 'yes';

            $data[] = [
                'invoice_number' => '<a href="' . url('/view-invoice/' . $invoice->id) . $verify . '" class="badge badge-success">' . $invoice->invoice_number . '</a>',
                'status' => $invoice->status,
                'sub_total' => $invoice->sub_total,
                'total' => $invoice->total,
                'commission_amount' => $invoice->commission_amount,
                'creditpay' => $invoice->creditpay,
                'party_amount' => $invoice->party_amount,
                'items_count' => $itemCount,
                'payment_mode' => ($invoice->payment_mode == 'online') ? 'UPI' : $invoice->payment_mode,
                'created_at' => date('Y-m-d H:i:s', strtotime($invoice->created_at)),
                'party_user' => $invoice->party_user ?? 'N/A',
                'commission_user' => $invoice->commission_user ?? 'N/A',
                'action' => '
                        ' . ($showEditButton ? '
                            <a href="' . url('/sales/edit-sales/' . $invoice->id) . $verify . '" class="btn btn-sm btn-success mb-1" title="Edit Invoice">
                                <i class="fa fa-edit"></i>
                            </a>
                        ' : '') . '
                        ' . ($showViewHistoryButton ? '
                            <br>
                            <button type="button"
                                    class="btn btn-outline-dark btn-sm view-history-btn"
                                    data-invoice-id="' . $invoice->id . '"
                                    title="View History">
                                📝 View History
                            </button>
                        ' : '') . '
                        ' . ($invoice->sales_type == "admin_sale" ? '
                            <span class="text-danger ms-2 fw-bold" style="font-size:12px;">
                                (Admin)
                            </span>
                        ' : '') . '
                    ',
            ];
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    public function closeShift($id, $return = "htmlfile")
    {
        $shift = ShiftClosing::findOrFail($id);

        $user_data = User::select('name')->where('id', $shift->user_id)->firstOrFail();
        $branch_data = Branch::select('name', 'in_out_enable')->where('id', $shift->branch_id)->firstOrFail();
        $branch_name = $branch_data->name;
        $user_name = $user_data->name;
        $in_out_enable = $branch_data->in_out_enable;

        if (!$shift->closing_shift_time) {

            $categoryTotals = [];
            $totals = CreditHistory::whereBetween('created_at', [$shift->start_time, $shift->end_time])
                ->where('store_id', $shift->branch_id)
                ->where('transaction_kind', '!=', 'collact_credit')
                ->selectRaw('SUM(credit_amount) as credit_total, SUM(debit_amount) as debit_total')
                ->first();


            $invoices = Invoice::where(['branch_id' => $shift->branch_id])->whereBetween('created_at', [$shift->start_time, $shift->end_time])->whereNotIn('status', ['Hold', 'resumed', 'archived', 'Returned'])->latest()->get();

            // $invoices = Invoice::where(['user_id' => $shift->user_id])->where(['branch_id' => $shift->branch_id])->whereBetween('created_at', [$shift->start_time, $shift->end_time])->whereNotIn('status', ['Hold', 'resumed', 'archived', 'Returned'])->latest()->get();
            $discountTotal = $totalSales = $totalPaid = $totalRefund = $totalCashPaid = $totalRoundOf = $totalSubTotal = $totalCreditPay = $totalUpiPaid = $totalRefundReturn = $totalOnlinePaid = $totalSalesQty = $totalPaidCredit = $totalCreditPaid = 0;

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
                            $amount = (float) str_replace(',', '', $item['price']) ?? 0;

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
                $totalCreditPaid += (!empty($invoice->creditpay) && is_numeric($invoice->creditpay)) ? (int)$invoice->creditpay : 0;

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
                        ->where('user_id', $shift->user_id)
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

            $cashAdded = CashBreakdown::where('user_id', $shift->user_id)
                ->where('branch_id', $shift->branch_id)
                ->where('type',  'add cash')
                ->whereBetween('created_at', [$shift->start_time, $shift->end_time])
                ->sum('total');
            $totalWith = \App\Models\WithdrawCash::where('user_id',  $shift->user_id)
                ->where('branch_id', $shift->branch_id)->whereBetween('created_at', [$shift->start_time, $shift->end_time])->sum('amount');
            $categoryTotals['payment']['CASH'] = $totalCashPaid;
            $categoryTotals['payment']['UPI PAYMENT'] =  ($totalUpiPaid + $totalOnlinePaid);
            $categoryTotals['payment']['CREDIT'] =  ($totalCreditPaid);
            // $categoryTotals['payment']['totalSalesQty'] =  $totalSalesQty;
            // $categoryTotals['payment']['transactionTotal'] =  $transaction_total;

            $categoryTotals['payment']['TOTAL'] = $totalCashPaid + ($totalUpiPaid + $totalOnlinePaid) + $totalCreditPaid;
            $categoryTotals['summary']['OPENING CASH'] = @$shift->opening_cash;
            $categoryTotals['summary']['CASH ADDED'] = @$cashAdded;

            $categoryTotals['summary']['TOTAL SALES'] = $totals->credit_total - $totalPaidCredit + $totalSubTotal + $discountTotal - $totalRefundReturn - $totalRoundOf;


            $categoryTotals['summary']['DISCOUNT'] = $discountTotal * (-1);
            $categoryTotals['summary']['WITHDRAWAL PAYMENT'] = $totalWith * (-1);
            $categoryTotals['summary']['UPI PAYMENT'] = ($totalUpiPaid + $totalOnlinePaid) * (-1);
            $categoryTotals['summary']['ROUND OFF'] = $totalRoundOf;
            $categoryTotals['summary']['CREDIT'] = $totals->credit_total * (-1);
            $categoryTotals['summary']['REFUND_CREDIT'] = $totals->debit_total;
            //$categoryTotals['summary']['ONLINE PAYMENT'] = $totalOnlinePaid * (-1);
            if (!empty($creditCollacted->collacted_cash_amount))
                $categoryTotals['summary']['CREDIT COLLACTED BY CASH'] = $creditCollacted->collacted_cash_amount;
            // $categoryTotals['summary']['REFUND'] += $totalRefundReturn *(-1);

            $categoryTotals['summary']['TOTAL'] = $categoryTotals['summary']['CASH ADDED'] + $categoryTotals['summary']['OPENING CASH'] + $categoryTotals['summary']['TOTAL SALES'] + $categoryTotals['summary']['DISCOUNT'] + $categoryTotals['summary']['WITHDRAWAL PAYMENT'] + $categoryTotals['summary']['UPI PAYMENT'] + @$categoryTotals['summary']['REFUND'] +
                @$categoryTotals['summary']['ONLINE PAYMENT'] + @$categoryTotals['summary']['CREDIT COLLACTED BY CASH'] + $totalRoundOf + $categoryTotals['summary']['CREDIT'] + $totalRefundReturn + @$creditCollacted->collacted_cash_amount;

            $categoryTotals['summary']['REFUND'] = $totalRefund * (-1) + $totalRefundReturn * (-1);
            //$categoryTotals['summary']['REFUND RETURN'] = $totalRefundReturn*(-1);
            //$categoryTotals['summary']['CREDIT'] = $totals->debit_total;

            // if (!empty($categoryTotals['summary']['REFUND_CREDIT'])) {
            //     $categoryTotals['summary']['REFUND_CREDIT'] = (int)$categoryTotals['summary']['REFUND_CREDIT'] * (-1);
            // }

            $cashBreakdowns = CashBreakdown::where('user_id', $shift->user_id)
                ->where('branch_id', $shift->branch_id)
                ->where('type', '!=', 'Returned')
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

            $shiftcash = $noteCount;
            $closing_cash = $shift->closing_cash;
            $cash_discrepancy = $shift->cash_discrepancy;

            // Render a Blade view and pass any needed data
            $html = view('shift_manage.closed', ['opening_stock' => $totalOpeningStock, 'user_name' => $user_name, 'shift' => $shift, "categoryTotals" => $categoryTotals, "shiftcash" => $shiftcash, "closing_cash" => $closing_cash, 'cash_discrepancy' => $cash_discrepancy, 'branch_name' => $branch_name, 'in_out_enable' => $in_out_enable])->render();
            if ($return == "html") {
                return  ['user_name' => $user_name, 'shift' => $shift, "categoryTotals" => $categoryTotals, "shiftcash" => $shiftcash, "closing_cash" => $closing_cash, 'cash_discrepancy' => $cash_discrepancy, 'branch_name' => $branch_name, 'in_out_enable' => $in_out_enable];
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

    public function closeShiftModel($id, $return = "htmlfile")
    {
        $shift = ShiftClosing::findOrFail($id);

        $user_data = User::select('name')->where('id', $shift->user_id)->firstOrFail();
        $branch_data = Branch::select('name', 'in_out_enable')->where('id', $shift->branch_id)->firstOrFail();
        $branch_name = $branch_data->name;
        $user_name = $user_data->name;
        $in_out_enable = $branch_data->in_out_enable;

        if (!$shift->closing_shift_time) {

            $categoryTotals = [];
            $totals = CreditHistory::whereBetween('created_at', [$shift->start_time, $shift->end_time])
                ->where('store_id', $shift->branch_id)
                ->where('transaction_kind', '!=', 'collact_credit')
                ->selectRaw('SUM(credit_amount) as credit_total, SUM(debit_amount) as debit_total')
                ->first();


            $invoices = Invoice::where(['branch_id' => $shift->branch_id])->whereBetween('created_at', [$shift->start_time, $shift->end_time])->whereNotIn('status', ['Hold', 'resumed', 'archived', 'Returned'])->latest()->get();

            // $invoices = Invoice::where(['user_id' => $shift->user_id])->where(['branch_id' => $shift->branch_id])->whereBetween('created_at', [$shift->start_time, $shift->end_time])->whereNotIn('status', ['Hold', 'resumed', 'archived', 'Returned'])->latest()->get();
            $discountTotal = $totalSales = $totalPaid = $totalRefund = $totalCashPaid = $totalRoundOf = $totalSubTotal = $totalCreditPay = $totalUpiPaid = $totalRefundReturn = $totalOnlinePaid = $totalSalesQty = $totalPaidCredit = $totalCreditPaid = 0;

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
                            $amount = (float) str_replace(',', '', $item['price']) ?? 0;

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
                $totalCreditPaid += (!empty($invoice->creditpay) && is_numeric($invoice->creditpay)) ? (int)$invoice->creditpay : 0;

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
                        ->where('user_id', $shift->user_id)
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

            $cashAdded = CashBreakdown::where('user_id', $shift->user_id)
                ->where('branch_id', $shift->branch_id)
                ->where('type',  'add cash')
                ->whereBetween('created_at', [$shift->start_time, $shift->end_time])
                ->sum('total');
            $totalWith = \App\Models\WithdrawCash::where('user_id',  $shift->user_id)
                ->where('branch_id', $shift->branch_id)->whereBetween('created_at', [$shift->start_time, $shift->end_time])->sum('amount');
            $categoryTotals['payment']['CASH'] = $totalCashPaid;
            $categoryTotals['payment']['UPI PAYMENT'] =  ($totalUpiPaid + $totalOnlinePaid);
            $categoryTotals['payment']['CREDIT'] =  ($totalCreditPaid);
            // $categoryTotals['payment']['totalSalesQty'] =  $totalSalesQty;
            // $categoryTotals['payment']['transactionTotal'] =  $transaction_total;

            $categoryTotals['payment']['TOTAL'] = $totalCashPaid + ($totalUpiPaid + $totalOnlinePaid) + $totalCreditPaid;
            $categoryTotals['summary']['OPENING CASH'] = @$shift->opening_cash;
            $categoryTotals['summary']['CASH ADDED'] = @$cashAdded;

            $categoryTotals['summary']['TOTAL SALES'] = $totals->credit_total - $totalPaidCredit + $totalSubTotal + $discountTotal - $totalRefundReturn - $totalRoundOf;


            $categoryTotals['summary']['DISCOUNT'] = $discountTotal * (-1);
            $categoryTotals['summary']['WITHDRAWAL PAYMENT'] = $totalWith * (-1);
            $categoryTotals['summary']['UPI PAYMENT'] = ($totalUpiPaid + $totalOnlinePaid) * (-1);
            $categoryTotals['summary']['ROUND OFF'] = $totalRoundOf;
            $categoryTotals['summary']['CREDIT'] = $totals->credit_total * (-1);
            $categoryTotals['summary']['REFUND_CREDIT'] = $totals->debit_total;
            //$categoryTotals['summary']['ONLINE PAYMENT'] = $totalOnlinePaid * (-1);
            if (!empty($creditCollacted->collacted_cash_amount))
                $categoryTotals['summary']['CREDIT COLLACTED BY CASH'] = $creditCollacted->collacted_cash_amount;
            // $categoryTotals['summary']['REFUND'] += $totalRefundReturn *(-1);

            $categoryTotals['summary']['TOTAL'] = $categoryTotals['summary']['CASH ADDED'] + $categoryTotals['summary']['OPENING CASH'] + $categoryTotals['summary']['TOTAL SALES'] + $categoryTotals['summary']['DISCOUNT'] + $categoryTotals['summary']['WITHDRAWAL PAYMENT'] + $categoryTotals['summary']['UPI PAYMENT'] + @$categoryTotals['summary']['REFUND'] +
                @$categoryTotals['summary']['ONLINE PAYMENT'] + @$categoryTotals['summary']['CREDIT COLLACTED BY CASH'] + $totalRoundOf + $categoryTotals['summary']['CREDIT'] + $totalRefundReturn + @$creditCollacted->collacted_cash_amount;

            $categoryTotals['summary']['REFUND'] = $totalRefund * (-1) + $totalRefundReturn * (-1);
            //$categoryTotals['summary']['REFUND RETURN'] = $totalRefundReturn*(-1);
            //$categoryTotals['summary']['CREDIT'] = $totals->debit_total;

            // if (!empty($categoryTotals['summary']['REFUND_CREDIT'])) {
            //     $categoryTotals['summary']['REFUND_CREDIT'] = (int)$categoryTotals['summary']['REFUND_CREDIT'] * (-1);
            // }

            $cashBreakdowns = CashBreakdown::where('user_id', $shift->user_id)
                ->where('branch_id', $shift->branch_id)
                ->where('type', '!=', 'Returned')
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

            $shiftcash = $noteCount;
            $closing_cash = $shift->closing_cash;
            $cash_discrepancy = $shift->cash_discrepancy;

            // Render a Blade view and pass any needed data
            $html = view('shift_manage.closed_model', ['opening_stock' => $totalOpeningStock, 'user_name' => $user_name, 'shift' => $shift, "categoryTotals" => $categoryTotals, "shiftcash" => $shiftcash, "closing_cash" => $closing_cash, 'cash_discrepancy' => $cash_discrepancy, 'branch_name' => $branch_name, 'in_out_enable' => $in_out_enable])->render();
            if ($return == "html") {
                return  ['user_name' => $user_name, 'shift' => $shift, "categoryTotals" => $categoryTotals, "shiftcash" => $shiftcash, "closing_cash" => $closing_cash, 'cash_discrepancy' => $cash_discrepancy, 'branch_name' => $branch_name, 'in_out_enable' => $in_out_enable];
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

    public function view_old($id, $shift_id, Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search'); // <-- Search term

        $shift = ShiftClosing::findOrFail($shift_id);
        $branch = Branch::findOrFail($id);
        $branch_name = $branch->name;

        $query = \DB::table('invoices')
            ->where('branch_id', $id)
            ->whereBetween('created_at', [$shift->start_time, $shift->end_time]);

        // if ($shift->status === "pending") {
        //     $query->whereIn('status', ['Paid', 'Hold']);
        // } else {
        //     $query->where('status', 'Paid');
        // }

        // ✅ Filter by product name inside items JSON (MySQL LIKE)
        // if ($search) {
        //     $query->where('items', 'LIKE', '%' . $search . '%');
        // }

        $partyUsers = DB::table('party_users')
            ->select('id', 'first_name')
            ->where('is_delete', 'No')->where('status', 'Active')->orderBy('first_name')->get();

        $commissionUsers = DB::table('commission_users')
            ->select('id', 'first_name')
            ->where('is_deleted', 'No')->where('status', 'Active')->orderBy('first_name')->get();


        // Totals (before pagination)
        // $totals = (clone $query)->selectRaw('
        // SUM(cash_amount) as total_cash,
        // SUM(upi_amount + online_amount) as total_upi,
        // SUM(creditpay) as total_credit,
        //     SUM(total_item_qty) as total_items,
        //     SUM(sub_total) as total_subtotal,
        //     SUM(total) as total_total
        // ')->first();

        // $invoices = $query->orderBy('created_at', 'desc')
        //     ->select(
        //         'id',
        //         'invoice_number',
        //         'cash_amount',
        //         'upi_amount',
        //         'online_amount',
        //         'creditpay',
        //         'payment_mode',
        //         'total_item_qty',
        //         'sub_total',
        //         'tax',
        //         'total',
        //         'status',
        //         'created_at'
        //     )
        //     ->paginate($perPage)
        //     ->appends(['per_page' => $perPage, 'search' => $search]); // Preserve search

        return view('shift_manage.view', compact(
            'invoices',
            'shift_id',
            'branch_name',
            'id',
            'perPage',
            'totals',
            'partyUsers',
            'commissionUsers'
        ) + [
            // 'totalCashAmount' => $totals->total_cash,
            // 'totalUPIAmount' => $totals->total_upi,
            // 'totalCreditPay' => $totals->total_credit,
            // 'totalItems' => $totals->total_items,
            // 'totalSubTotal' => $totals->total_subtotal,
            // 'totalTotal' => $totals->total_total,
            // 'search' => $search,
        ]);
    }

    public function showPhoto($id)
    {

        $shift = ShiftClosing::findOrFail($id);
        return view('shift_manage.photo', compact('shift'));
    }

    public function stockDetails($id, Request $request)
    {
        $shift = ShiftClosing::findOrFail($id);
        $branch_id = $shift->branch_id;
        $branch_name = Branch::findOrFail($branch_id);
        $subcategories = DB::table('sub_categories')->where('is_deleted', 'no')->get();

        $subcategoryId = $request->input('subcategory_id');
        $searchKeyword = $request->input('search'); // 🔍 new line

        $rawStockQuery = DailyProductStock::with(['product.subcategory', 'product.category'])
            ->where('branch_id', $branch_id)
            ->where('shift_id', $id);

        if (!empty($subcategoryId)) {
            $rawStockQuery->whereHas('product', function ($query) use ($subcategoryId) {
                $query->where('subcategory_id', $subcategoryId);
            });
        }

        if (!empty($searchKeyword)) {
            $rawStockQuery->whereHas('product', function ($query) use ($searchKeyword) {
                $query->where('name', 'like', '%' . $searchKeyword . '%');
            });
        }

        // ✅ Reusable function
        $checkStatus = function ($model) use ($id) {

            $query = $model::where('shift_id', $id);

            // ✅ If no records → consider as verified
            if (!$query->exists()) {
                return 'verify';
            }

            return $query->where('admin_status', 'unverify')->exists()
                ? 'unverify'
                : 'verify';
        };

        // ✅ Apply to all models
        // ✅ Apply to all models (with shift_id)
        $finalAdminStatusReq = $checkStatus(StockRequest::class);
        $finalAdminStatusTra = $checkStatus(StockTransfer::class);
        $finalAdminStatusInv = $checkStatus(Invoice::class);

        // ✅ Shift has no shift_id → use id
        $finalAdminStatusShift = ShiftClosing::where('id', $id)
            ->where('admin_status', 'unverify')
            ->exists()
            ? 'unverify'
            : 'verify';

        // ✅ OPTIONAL: Overall shift status
        $finalShiftStatus = (
            $finalAdminStatusReq === 'verify' &&
            $finalAdminStatusTra === 'verify' &&
            $finalAdminStatusInv === 'verify' &&
            $finalAdminStatusShift === 'verify'
        ) ? 'verify' : 'unverify';
        // dd($finalAdminStatusTra);
        $rawStockData = $rawStockQuery->get();

        return view('shift_manage.stock_details', compact(
            'id',
            'rawStockData',
            'subcategories',
            'shift',
            'branch_name',
            'subcategoryId',
            'searchKeyword',
            'finalAdminStatusTra',
            'finalAdminStatusReq',
            'finalAdminStatusInv',
            'finalShiftStatus'
        ));
    }

    public function printShift($id)
    {
        $shift = ShiftClosing::findOrFail($id);

        if (!$shift->closing_shift_time) {
            $closeShift = $this->closeShift($id, "html");

            $stockTotals = DB::table('daily_product_stocks')
                ->where('shift_id', $id)
                ->selectRaw('
                    SUM(opening_stock) as total_opening_stock,
                    SUM(added_stock) as total_added_stock,
                    SUM(transferred_stock) as total_transferred_stock,
                    SUM(sold_stock) as total_sold_stock,
                    SUM(closing_stock) as total_closing_stock,
                    SUM(physical_stock) as total_physical_stock,
                    SUM(difference_in_stock) as total_difference_in_stock
                ')
                ->first();

            $totalTrasaction = \App\Models\Invoice::where('branch_id', $shift->branch_id)->whereNotIn('status', ['Hold', 'resumed', 'archived'])
                ->whereBetween('created_at', [$shift->start_time, $shift->end_time])
                ->count();

            // $totalTrasaction = \App\Models\Invoice::where('user_id', $shift->user_id)
            //     ->where('branch_id', $shift->branch_id)->whereNotIn('status', ['Hold', 'resumed', 'archived'])
            //     ->whereBetween('created_at', [$shift->start_time, $shift->end_time])
            //     ->count();

            $pdf = Pdf::loadView('shift_manage.shift_print', ['totalTrasaction' => $totalTrasaction, 'stockTotals' => $stockTotals, 'user_name' => $closeShift['user_name'], 'shift' => $closeShift['shift'], "categoryTotals" => $closeShift['categoryTotals'], "shiftcash" => $closeShift['shiftcash'], "closing_cash" => $closeShift['closing_cash'], 'cash_discrepancy' => $closeShift['cash_discrepancy'], 'closeShift' => $closeShift, 'branch_name' => $closeShift['branch_name']]);
            return $pdf->download('shift_report_' . Str::slug($shift->shift_no) . '.pdf');
        }

        return response()->json([
            'message' => 'Shift already closed',
            'code' => 400
        ], 200);
    }

    public function stockDetailsPdf($id, Request $request)
    {
        $shift = ShiftClosing::findOrFail($id);
        $branch_id = $shift->branch_id;
        $branch_name = Branch::findOrFail($branch_id);
        $subcategories = DB::table('sub_categories')->where('is_deleted', 'no')->get();

        $subcategoryId = $request->input('subcategory_id');
        $searchKeyword = $request->input('search');

        $rawStockQuery = DailyProductStock::with(['product.subcategory', 'product.category'])
            ->where('branch_id', $branch_id)
            ->where('shift_id', $id);

        if (!empty($subcategoryId)) {
            $rawStockQuery->whereHas('product', function ($query) use ($subcategoryId) {
                $query->where('subcategory_id', $subcategoryId);
            });
        }

        if (!empty($searchKeyword)) {
            $rawStockQuery->whereHas('product', function ($query) use ($searchKeyword) {
                $query->where('name', 'like', '%' . $searchKeyword . '%');
            });
        }

        $rawStockData = $rawStockQuery->get();

        $pdf = Pdf::loadView('pdfs.stock_summary', compact(
            'rawStockData',
            'shift',
            'branch_name'
        ))->setPaper('A4', 'landscape');

        return $pdf->download('stock-summary.pdf');
    }

    public function verifyStatus(Request $request)
    {
        $type = $request->type;
        $status = $request->status;
        $shift_id = $request->shift_id;

        DB::beginTransaction();

        try {

            if ($type === 'sales') {
                // Get invoice IDs
                $invoiceIds = Invoice::where('shift_id', $shift_id)->pluck('id');

                // Update invoices
                Invoice::whereIn('id', $invoiceIds)
                    ->update(['admin_status' => $status]);

                // Update vouchers (ONLY Sales type)
                Voucher::whereIn('gen_id', $invoiceIds)
                    ->where('voucher_type', 'Sales')
                    ->update(['admin_status' => $status]);
                \Log::info('Invoice Updated: ' . $invoiceIds);
            }

            if ($type === 'transfer') {
                $updated = StockTransfer::where('shift_id', $shift_id)
                    ->update(['admin_status' => $status]);

                \Log::info('Transfer Updated: ' . $updated);
            }

            if ($type === 'request') {
                $updated = StockRequest::where('shift_id', $shift_id)
                    ->update(['admin_status' => $status]);

                \Log::info('Request Updated: ' . $updated);
            }

            // ✅ Re-check status
            $hasUnverified =
                Invoice::where('shift_id', $shift_id)->where('admin_status', 'unverify')->exists() ||
                StockTransfer::where('shift_id', $shift_id)->where('admin_status', 'unverify')->exists() ||
                StockRequest::where('shift_id', $shift_id)->where('admin_status', 'unverify')->exists();

            ShiftClosing::where('id', $shift_id)->update([
                'admin_status' => $hasUnverified ? 'unverify' : 'verify'
            ]);

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyAll(Request $request)
    {
        $shift_id = $request->shift_id;
        $status = $request->status;

        DB::beginTransaction();

        try {

            // ✅ Update all modules

            $invoiceIds = Invoice::where('shift_id', $shift_id)->pluck('id');

            // Update invoices
            Invoice::whereIn('id', $invoiceIds)
                ->update(['admin_status' => $status]);

            // Update vouchers (ONLY Sales type)
            Voucher::whereIn('gen_id', $invoiceIds)
                ->where('voucher_type', 'Sales')
                ->update(['admin_status' => $status]);

            StockTransfer::where('shift_id', $shift_id)
                ->update(['admin_status' => $status]);

            StockRequest::where('shift_id', $shift_id)
                ->update(['admin_status' => $status]);

            // ✅ Update shift itself
            ShiftClosing::where('id', $shift_id)
                ->update(['admin_status' => $status]);

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePhysical(Request $request)
    {
        $stock = DailyProductStock::findOrFail($request->stock_id);

        $qty = (float)$request->physical;

        // SAME LOGIC AS YOUR save()
        // $sales_plus = $stock->opening_stock + $stock->added_stock;
        // $sales_minus = $stock->transferred_stock + $stock->sold_stock + $qty;

        // $one_time_sale = $sales_plus - $sales_minus;

        $oldPhysical = $stock->physical_stock;

        // ✅ APPLY SAME UPDATE
        $stock->physical_stock = $qty;
        // $stock->sold_stock = $stock->sold_stock + $one_time_sale;
        // $stock->closing_stock = $stock->closing_stock - $one_time_sale;
        // $stock->difference_in_stock = $stock->physical_stock - $stock->closing_stock;

        $stock->save();


        return response()->json([
            'difference' => $stock->difference_in_stock,
            'closing' => $stock->closing_stock,
            'sold' => $stock->sold_stock
        ]);
    }
}
