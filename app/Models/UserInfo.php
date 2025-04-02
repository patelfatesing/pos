<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInfo extends Model
{
    //
    protected $table = 'user_info';
    protected $fillable = [ 'first_name', 'last_name', 'user_id', 'branch_id', 'address', 'phone_number', 'date_of_birth'];

    public function users()
    {
        return $this->belongsTo(User::class);
    }
}
