<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    protected $fillable = ['user_id', 'contact_id', 'bank_account_id', 'reference', 'date', 'amount', 'category', 'notes', 'status'];
    public function user() { return $this->belongsTo(User::class); }
    public function contact() { return $this->belongsTo(Contact::class); }
    public function bankAccount() { return $this->belongsTo(BankAccount::class); }
}
