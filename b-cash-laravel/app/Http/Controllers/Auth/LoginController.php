<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class LoginController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $findUser = User::where('google_id', $googleUser->id)->first();

            if ($findUser) {
                Auth::login($findUser);
                return redirect()->intended('/dashboard');
            } else {
                $newUser = User::create([
                    'full_name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password_hash' => bcrypt(Str::random(16)), // Random secure password
                    'is_verified' => true, // Since Google verifies emails
                    'email_verified_at' => now(),
                ]);

                Auth::login($newUser);
                return redirect()->intended('/dashboard');
            }
        } catch (Exception $e) {
            return redirect()->route('login')->with('error', 'Something went wrong with Google login');
        }
    }
}
