<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BankAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'uuid',
        'name',
        'account_type',
        'currency',
        'opening_balance',
        'current_balance',
        'status',
        'metadata',
        'closed_at',
        // legacy
        'type',
        'account_number',
    ];

    protected $casts = [
        'metadata' => 'array',
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'closed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $account) {
            if (empty($account->uuid)) {
                $account->uuid = (string) Str::uuid();
            }
            if (empty($account->account_type)) {
                $account->account_type = $account->type ?: 'bank';
            }
        });
    }

    public function user() { return $this->belongsTo(User::class); }

    public function transactions()
    {
        return $this->hasMany(BankTransaction::class, 'bank_account_id');
    }
}
