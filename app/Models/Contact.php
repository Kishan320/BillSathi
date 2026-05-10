<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'user_id', 'name', 'gstin', 'pan', 'mobile', 'email', 'type',
        'due_in_days', 'currency',
        'billing_address1', 'billing_address2', 'billing_city', 'billing_pincode', 'billing_state', 'billing_country',
        'shipping_same_as_billing',
        'shipping_address1', 'shipping_address2', 'shipping_city', 'shipping_pincode', 'shipping_state', 'shipping_country',
        'opening_balance', 'opening_balance_type',
        'enable_customer_portal', 'notes',
    ];

    public function user() { return $this->belongsTo(User::class); }
}
