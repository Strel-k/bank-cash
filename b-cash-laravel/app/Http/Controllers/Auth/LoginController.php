<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class LoginController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle(Request $request): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Direct API OAuth login is not supported. Please use the web interface at ' . url('/login'),
                'login_url' => url('/login')
            ], 400);
        }

        // Set intended URL if provided
        if ($request->has('callback_url')) {
            session()->put('url.intended', $request->callback_url);
        }

        // Store CSRF state in session
        session()->put('google_oauth_state', $state = Str::random(40));

        // Configure and redirect to Google OAuth
        return Socialite::driver('google')
            ->with(['state' => $state])
            ->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function handleGoogleCallback(Request $request): RedirectResponse|JsonResponse
    {
        try {
            // Verify state to prevent CSRF
            $state = session()->pull('google_oauth_state');
            if (!$state || $request->get('state') !== $state) {
                throw new Exception('Invalid OAuth state');
            }
            
            try {
                $googleUser = Socialite::driver('google')->stateless()->user();
            } catch (Exception $e) {
                Log::error('Failed to get Google user data', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw new Exception('Failed to retrieve Google user data: ' . $e->getMessage());
            }
            
            // Validate and get Google user data
            if (!method_exists($googleUser, 'getId') || !$googleUser->getId()) {
                Log::error('Invalid Google user data', ['user' => $googleUser]);
                throw new Exception('Invalid Google user data received');
            }
            
            $googleId = (string) $googleUser->getId();
            $googleEmail = method_exists($googleUser, 'getEmail') ? $googleUser->getEmail() : null;
            $googleName = method_exists($googleUser, 'getName') ? $googleUser->getName() : 'Google User';
            
            if (empty($googleId)) {
                throw new Exception('Google ID is required for authentication');
            }
            
            Log::info('Google user data received', [
                'id' => $googleId,
                'email' => $googleEmail ?: 'No email provided',
                'name' => $googleName
            ]);
            
            // Start DB transaction
            DB::beginTransaction();
            
            try {
                // Look up user with validated data
                $findUser = User::where(function($query) use ($googleId, $googleEmail) {
                    $query->where('google_id', $googleId);
                    if ($googleEmail) {
                        $query->orWhere('email', $googleEmail);
                    }
                })->first();

                if ($findUser) {
                    // Update Google ID if user was found by email
                    if (!$findUser->google_id) {
                        $findUser->google_id = $googleId;
                        $findUser->save();
                    }
                    $user = $findUser;
                } else {
                    // Create new user with default values
                    $user = User::create([
                        'full_name' => $googleName,
                        'email' => $googleEmail,
                        'google_id' => $googleId,
                        'password_hash' => bcrypt(Str::random(16)),
                        'phone_number' => null, 
                        'is_verified' => true,
                        'is_admin' => false,
                        'login_attempts' => 0,
                        'email_verified_at' => now(),
                        'registration_step' => 1
                    ]);

                    // Create wallet for the user
                    Wallet::create([
                        'user_id' => $user->id,
                        'balance' => 0,
                        'currency' => 'USD'
                    ]);
                }

                DB::commit();

                // Log the user in and create session
                Auth::login($user, true);
                $request->session()->regenerate();

                // Generate token for API access
                $token = $user->createToken('auth-token')->plainTextToken;
                session(['api_token' => $token]);

                // Return response based on request type
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Successfully logged in with Google',
                        'user' => $user,
                        'token' => $token,
                        'redirect' => url('/dashboard')
                    ]);
                }

                return redirect()->intended('/dashboard')
                    ->withCookie(cookie('auth_token', $token, 60 * 24));

            } catch (Exception $e) {
                DB::rollBack();
                Log::error('Failed during user creation/update', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        } catch (Exception $e) {
            Log::error('Google authentication failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google login failed',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('login')
                ->with('error', 'Authentication failed: ' . $e->getMessage());
        }
    }
}
