<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StockTransfer extends Model
{
    protected $fillable = [
        'stock_request_id',
        'from_branch_id',
        'to_branch_id',
        'product_id',
        'quantity',
        'status',
        'transfer_by',
        'transferred_at',
        'transfer_number'
    ];
    
    public function request()
    {
        return $this->belongsTo(StockRequest::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function transferredBy()
    {
        return $this->belongsTo(User::class, 'transfer_by');
    }

    public function getTransferData()
    {
        return self::select([
                'stock_transfers.transfer_number',
                'stock_transfers.from_branch_id',
                'stock_transfers.to_branch_id',
                'stock_transfers.status',
                'stock_transfers.transfer_by',
                'stock_transfers.transferred_at',
                'from_branch.name as from_branch_name',
                'to_branch.name as to_branch_name',
                'users.name as created_by_name',
                DB::raw('COUNT(DISTINCT stock_transfers.product_id) as total_products'),
                DB::raw('SUM(stock_transfers.quantity) as total_quantity')
            ])
            ->join('branches as from_branch', 'stock_transfers.from_branch_id', '=', 'from_branch.id')
            ->join('branches as to_branch', 'stock_transfers.to_branch_id', '=', 'to_branch.id')
            ->join('users', 'stock_transfers.transfer_by', '=', 'users.id')
            ->groupBy([
                'stock_transfers.transfer_number',
                'stock_transfers.from_branch_id',
                'stock_transfers.to_branch_id',
                'stock_transfers.status',
                'stock_transfers.transfer_by',
                'stock_transfers.transferred_at',
                'from_branch.name',
                'to_branch.name',
                'users.name'
            ])
            ->get()
            ->map(function ($transfer) {
                return [
                    'id' => $transfer->transfer_number,
                    'transfer_number' => $transfer->transfer_number,
                    'from' => $transfer->from_branch_name,
                    'to' => $transfer->to_branch_name,
                    'transferred_at' => $transfer->transferred_at ? date('d-m-Y H:i', strtotime($transfer->transferred_at)) : 'N/A',
                    'status' => ucfirst($transfer->status),
                    'created_by' => $transfer->created_by_name,
                    'total_products' => $transfer->total_products,
                    'total_quantity' => $transfer->total_quantity
                ];
            });
    }

    public function getTransferDetails($transferNumber)
    {
        // Get the first transfer record for basic information
        $mainTransfer = self::with(['fromBranch', 'toBranch', 'transferredBy'])
            ->where('transfer_number', $transferNumber)
            ->first();

        if (!$mainTransfer) {
            return null;
        }

        // Get all products for this transfer
        $products = self::with(['product.category', 'product.subcategory'])
            ->where('transfer_number', $transferNumber)
            ->get();

        // Add products to the main transfer
        $mainTransfer->products = $products;
        $mainTransfer->total_quantity = $products->sum('quantity');
        $mainTransfer->total_products = $products->count();

        return $mainTransfer;
    }
}
