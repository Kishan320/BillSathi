<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'user_id', 'contact_id', 'bank_account_id',
        'vendor_name', 'warehouse', 'payment_terms',
        'invoice_number', 'date', 'due_date',
        'subtotal', 'tax', 'total', 'paid',
        'status', 'notes',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function contact() { return $this->belongsTo(Vendor::class, 'contact_id'); }
    public function vendor() { return $this->belongsTo(Vendor::class, 'contact_id'); }
    public function bankAccount() { return $this->belongsTo(BankAccount::class); }
    public function items() { return $this->hasMany(PurchaseItem::class); }
}
