<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CustomAccount extends Model
{
    protected $fillable = ['user_id', 'name', 'type', 'opening_balance', 'description'];
    public function user() { return $this->belongsTo(User::class); }
}
