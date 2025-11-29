<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Handle registration
     */
    public function register(Request $request)
    {
        $request->validate([
            'full_name'     => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'phone_number'  => 'required|string|max:20|unique:users,phone_number',
            'birthdate'     => 'required|date',
            'address'       => 'required|string|max:255',
            'gender'        => 'required|string',
            'valid_id'      => 'required|image|mimes:jpg,png,jpeg|max:2048',
            'password'      => 'required|string|confirmed|min:6',
            'pin'           => 'required|digits:4',
        ]);

        // 📸 Save the uploaded valid ID to storage/app/public/ids
        $idPath = $request->file('valid_id')->store('ids', 'public');

        // 🧍‍♂️ Create new user record
        $user = User::create([
            'full_name'    => $request->full_name,
            'email'        => $request->email,
            'phone_number' => $request->phone_number,
            'birthdate'    => $request->birthdate,
            'address'      => $request->address,
            'gender'       => $request->gender,
            'valid_id'     => $idPath,
            'password'     => Hash::make($request->password),
            'pin'          => Hash::make($request->pin),
        ]);

        // ✅ Redirect to login with success message
        return redirect()
            ->route('login')
            ->with('success', 'Registration successful! You can now log in using your phone number.');
    }

    /**
     * Handle login (using phone_number + password)
     */
    public function login(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'password'     => 'required|string',
        ]);

        // 🔍 Find user by phone number
        $user = User::where('phone_number', $request->phone_number)->first();

        if (!$user) {
            return back()->withErrors(['phone_number' => 'No account found for this phone number.']);
        }

        // 🧠 Check password validity
        $storedPassword = $user->password ?? $user->password_hash ?? null;

        if (!$storedPassword || !Hash::check($request->password, $storedPassword)) {
            return back()->withErrors(['password' => 'Invalid credentials.']);
        }

        // ✅ Successful login
        Auth::login($user);

        // Create Sanctum token for API access
        $token = $user->createToken('api-token')->plainTextToken;

        // Store token in session for JavaScript to use
        $request->session()->put('api_token', $token);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Welcome back, ' . ($user->full_name ?? $user->name) . '!');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'You have been logged out.');
    }
}
