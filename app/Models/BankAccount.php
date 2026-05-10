<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $fillable = ['user_id', 'name', 'type', 'account_number', 'opening_balance', 'current_balance'];

    public function user() { return $this->belongsTo(User::class); }
}
