<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

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
        'password', // Add this for compatibility with Laravel's auth system
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'password_hash',
        'pin',
        'id_front',
        'id_back',
        'face_image',
        'remember_token',
        'google_id',
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
        return $this->password_hash ?? $this->password;
    }

    /**
     * Set the password attribute.
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password_hash'] = $value;
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
