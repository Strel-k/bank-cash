<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sender_wallet_id',
        'receiver_wallet_id',
        'amount',
        'transaction_type',
        'reference_number',
        'description',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Get the sender wallet.
     */
    public function senderWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'sender_wallet_id');
    }

    /**
     * Get the receiver wallet.
     */
    public function receiverWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'receiver_wallet_id');
    }

    /**
     * Get the sender user.
     */
    public function senderUser()
    {
        return $this->belongsToThrough(User::class, Wallet::class, 'sender_wallet_id', 'user_id');
    }

    /**
     * Get the receiver user.
     */
    public function receiverUser()
    {
        return $this->belongsToThrough(User::class, Wallet::class, 'receiver_wallet_id', 'user_id');
    }

    /**
     * Scope a query to only include transactions for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->whereHas('senderWallet', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->orWhereHas('receiverWallet', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }
}
