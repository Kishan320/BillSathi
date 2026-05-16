<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = [
        'user_id', 'name', 'address', 'city', 'zip_code',
        'contact_person', 'phone', 'email', 'status', 'notes',
    ];

    public function user() { return $this->belongsTo(User::class); }
}
