<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'balance',
        'account_number',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the wallet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transactions for the wallet.
     */
    public function sentTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'sender_wallet_id');
    }

    /**
     * Get the transactions for the wallet.
     */
    public function receivedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'receiver_wallet_id');
    }

    /**
     * Get all transactions for the wallet.
     */
    public function transactions()
    {
        return Transaction::where('sender_wallet_id', $this->id)
            ->orWhere('receiver_wallet_id', $this->id);
    }
}
