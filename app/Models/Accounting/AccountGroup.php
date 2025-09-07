<?php
// app/Models/Accounting/AccountGroup.php
namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class AccountGroup extends Model
{
    protected $fillable = ['name', 'code', 'nature', 'affects_gross', 'parent_id', 'is_primary', 'sort_order'];
    protected $casts = [
        'is_user_defined' => 'boolean',
    ];

    protected $attributes = [
        'is_user_defined' => 1, // ✅ default at model level too
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
    public function ledgers()
    {
        return $this->hasMany(AccountLedger::class, 'group_id');
    }
}
