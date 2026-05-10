<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class BankTransfer extends Model
{
    protected $fillable = ['user_id', 'from_account_id', 'to_account_id', 'date', 'amount', 'notes'];
    public function user() { return $this->belongsTo(User::class); }
    public function fromAccount() { return $this->belongsTo(BankAccount::class, 'from_account_id'); }
    public function toAccount() { return $this->belongsTo(BankAccount::class, 'to_account_id'); }
}
