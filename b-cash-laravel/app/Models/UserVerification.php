<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserVerification extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_verification';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'verification_status',
        'id_document_type',
        'id_document_number',
        'id_document_front_path',
        'id_document_back_path',
        'id_document_ocr_data',
        'face_encoding',
        'face_image_path',
        'liveness_score',
        'similarity_score',
        'verification_attempts',
        'last_verification_attempt',
        'verified_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id_document_ocr_data' => 'array',
            'liveness_score' => 'decimal:2',
            'similarity_score' => 'decimal:2',
            'last_verification_attempt' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the verification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the verification logs for the verification.
     */
    public function verificationLogs(): HasMany
    {
        return $this->hasMany(VerificationLog::class, 'user_id', 'user_id');
    }
}
