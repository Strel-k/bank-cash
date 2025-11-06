<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Verification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'document_type',
        'document_number',
        'document_front_path',
        'document_back_path',
        'face_image_path',
        'status',
        'rejection_reason',
        'approved_by',
        'approved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'approved_at' => 'datetime',
    ];

    /**
     * Get the user that owns the verification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who approved the verification.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the verification logs.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(VerificationLog::class);
    }

    /**
     * Add a log entry for this verification.
     */
    public function addLog(string $action, ?string $details = null, ?int $performedBy = null): void
    {
        $this->logs()->create([
            'action' => $action,
            'details' => $details,
            'performed_by' => $performedBy,
        ]);
    }

    /**
     * Approve the verification.
     */
    public function approve(int $adminId): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);

        $this->addLog('approved', null, $adminId);

        // Update user's verified status
        $this->user->update(['is_verified' => true]);
    }

    /**
     * Reject the verification.
     */
    public function reject(int $adminId, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        $this->addLog('rejected', $reason, $adminId);
    }
}