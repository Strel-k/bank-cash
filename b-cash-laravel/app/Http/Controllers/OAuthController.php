<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{
    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Check if user already exists with this Google ID
            $user = User::where('google_id', $googleUser->getId())->first();

            if (!$user) {
                // Check if user exists with same email
                $user = User::where('email', $googleUser->getEmail())->first();

                if ($user) {
                    // Link Google account to existing user
                    $user->update([
                        'google_id' => $googleUser->getId(),
                        'profile_picture' => $googleUser->getAvatar(),
                    ]);
                } else {
                    // Create new user
                    $user = User::create([
                        'google_id' => $googleUser->getId(),
                        'email' => $googleUser->getEmail(),
                        'full_name' => $googleUser->getName(),
                        'profile_picture' => $googleUser->getAvatar(),
                        'password_hash' => Hash::make(str_random(16)), // Random password for OAuth users
                        'is_verified' => true, // Google accounts are pre-verified
                    ]);

                    // Create wallet for new user
                    $accountNumber = 'BC' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
                    Wallet::create([
                        'user_id' => $user->id,
                        'account_number' => $accountNumber,
                        'balance' => 0.00,
                        'is_active' => true,
                    ]);
                }
            }

            // Log the user in
            Auth::login($user);

            // Redirect to dashboard or appropriate page
            return redirect('/index.php')->with('success', 'Successfully logged in with Google!');

        } catch (\Exception $e) {
            return redirect('/login.php')->with('error', 'Google login failed: ' . $e->getMessage());
        }
    }
}