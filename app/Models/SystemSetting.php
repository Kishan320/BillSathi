<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = ['user_id', 'type', 'value', 'sort_order'];

    public function user() { return $this->belongsTo(User::class); }
}
