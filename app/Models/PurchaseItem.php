<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = ['purchase_id', 'item_id', 'item_name', 'qty', 'unit_price', 'discount', 'taxes', 'total'];
    protected $casts = ['taxes' => 'array'];

    public function purchase() { return $this->belongsTo(Purchase::class); }
    public function item() { return $this->belongsTo(Item::class); }
}
