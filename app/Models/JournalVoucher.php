<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class JournalVoucher extends Model
{
    protected $fillable = ['user_id', 'voucher_number', 'date', 'description', 'debit', 'credit'];
    public function user() { return $this->belongsTo(User::class); }
}
