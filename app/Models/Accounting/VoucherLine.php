<?php
// app/Models/Accounting/VoucherLine.php
namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class VoucherLine extends Model
{
    protected $fillable = ['voucher_id', 'ledger_id', 'dc', 'amount', 'line_narration'];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
    public function ledger()
    {
        return $this->belongsTo(AccountLedger::class, 'ledger_id');
    }
}
