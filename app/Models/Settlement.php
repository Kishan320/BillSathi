<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Settlement extends Model
{
    protected $fillable = ['user_id', 'contact_id', 'bank_account_id', 'date', 'amount', 'notes'];
    public function user() { return $this->belongsTo(User::class); }
    public function contact() { return $this->belongsTo(Contact::class); }
    public function bankAccount() { return $this->belongsTo(BankAccount::class); }
}
