<?php
// app/Models/Accounting/AccountGroup.php
namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class AccountSubGroup extends Model
{
    protected $fillable = [
        'group_id',
        'name',
        'code',
        'nature',
        'affects_gross',
        'sort_order'
    ];

    public function group()
    {
        return $this->belongsTo(AccountGroup::class);
    }

    public function ledgers()
    {
        return $this->hasMany(AccountLedger::class, 'sub_group_id');
    }
}
