<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\ShiftClosing;

class ShiftManageController extends Controller
{
    public function index()
    {
        return view('shift_manage.index');
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
                    'branches.name as branch_name',
                    'users.name as user_name',
                    'shift_closings.start_time',
                    'shift_closings.end_time',
                    'shift_closings.opening_cash',
                    'shift_closings.created_at'
                )
                ->whereDate('shift_closings.created_at', \Carbon\Carbon::today()); // ← Add this line


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
        $transactions = \DB::table('invoices')
            ->select('branch_id', \DB::raw('COUNT(id) as total_transactions'))
            ->whereIn('branch_id', $branchIds)
            ->groupBy('branch_id')
            ->pluck('total_transactions', 'branch_id'); // key = branch_id, value = count

        $records = [];

        foreach ($data as $row) {
            $endTime = $row->end_time ? \Carbon\Carbon::parse($row->end_time) : null;
            $status = ($endTime && $now->lessThanOrEqualTo($endTime)) ? 'Running' : 'Closed';

            $totalTransactions = $transactions[$row->branch_id] ?? 0;

            $endTime = $row->end_time ? Carbon::parse($row->end_time) : null;
            $now = Carbon::now();

            $action = '<div class="d-flex align-items-center list-action">
                <a class="badge bg-info mr-2 view-transactions" 
                href="javascript:void(0);" 
                data-branch-id="' . $row->branch_id . '" 
                data-branch-name="' . $row->branch_name . '"
                title="View Transactions">
                <i class="ri-eye-line"></i>
                </a>';

            // Show "Close Shift" button if end_time is within next 30 minutes
            if ($endTime && $now->diffInMinutes($endTime, false) >= 0 && $now->diffInMinutes($endTime, false) <= 30) {
                $action .= '<a class="badge bg-warning ml-2 close-shift" 
                                href="javascript:void(0);" 
                                data-id="' . $row->id . '" 
                                title="Close Shift">
                                <i class="ri-lock-line"></i> Close
                            </a>';
            }

            $action .= '</div>';


            $records[] = [
                'branch_name' => $row->branch_name,
                'user_name' => $row->user_name,
                'start_time' => \Carbon\Carbon::parse($row->start_time)->format('d-m-Y h:i A'),
                'end_time' => $endTime ? $endTime->format('d-m-Y h:i A') : '-',
                'opening_cash' => number_format($row->opening_cash, 2),
                'status' => $status,
                'total_transaction' => $totalTransactions,
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

    public function closeShift($id)
    {
        $shift = ShiftClosing::findOrFail($id);
        if (!$shift->closing_shift_time) {
            $shift->closing_shift_time = now();
            $shift->status = 'closing';
            $shift->save();
            return response()->json(['message' => 'Shift closed successfully']);
        }

        return response()->json(['message' => 'Shift already closed'], 400);
    }

}
