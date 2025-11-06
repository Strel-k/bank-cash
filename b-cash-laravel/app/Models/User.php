<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'phone_number',
        'email',
        'full_name',
        'password_hash',
        'pin',
        'birthdate',
        'address',
        'gender',
        'id_type',
        'id_front',
        'id_back',
        'face_image',
        'profile_picture',
        'google_id',
        'is_verified',
        'is_admin',
        'login_attempts',
        'last_login_attempt',
        'email_verified_at',
        'registration_step',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password_hash',
        'pin',
        'id_front',
        'id_back',
        'face_image',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_attempt' => 'datetime',
            'is_verified' => 'boolean',
            'is_admin' => 'boolean',
            'login_attempts' => 'integer',
        ];
    }

    /**
     * Check if the user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    /**
     * Get the password attribute for authentication.
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Get the wallet associated with the user.
     */
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * Get the verification associated with the user.
     */
    public function verification(): HasOne
    {
        return $this->hasOne(UserVerification::class);
    }

    /**
     * Get the verification logs for the user.
     */
    public function verificationLogs()
    {
        return $this->hasMany(VerificationLog::class);
    }

    /**
     * Get the security tokens for the user.
     */
    public function securityTokens()
    {
        return $this->hasMany(SecurityToken::class);
    }
}
