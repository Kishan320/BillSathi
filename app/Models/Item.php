<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'user_id', 'item_type', 'manage_inventory', 'serialized_product',
        'name', 'hsn', 'sku', 'category', 'unit', 'tax_category', 'stock_category',
        'short_description', 'invoice_description',
        'sale_price', 'sale_price_type', 'sale_discount', 'sale_discount_type',
        'purchase_price',
        'opening_stock_qty', 'opening_stock_cost', 'serial_numbers',
    ];

    public function user() { return $this->belongsTo(User::class); }
}
