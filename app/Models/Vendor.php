<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = [
        'user_id',
        'billing_name',
        'mobile_number',
        'tax_number',
        'payment_terms',
        'billing_address',
        'billing_address_line2',
        'city',
        'state',
        'country',
        'zip_code',
        'notes',
        'same_as_billing',
        'shipping_name',
        'shipping_address',
        'shipping_address_line2',
        'shipping_city',
        'shipping_state',
        'shipping_country',
        'shipping_zip_code',
    ];

    protected $casts = [
        'same_as_billing' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
