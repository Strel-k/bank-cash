<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    /**
     * Get wallet balance
     */
    public function getBalance(Request $request)
    {
        if (!Auth::check()) {
            // Return enhanced debug info to help diagnose auth cookie/session issues in development
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated',
                'debug' => [
                    'request_cookies' => $request->cookies->all(),
                    'request_cookie_header' => $request->headers->get('cookie'),
                    'session_id' => session()->getId(),
                    'session_domain' => config('session.domain'),
                    'sanctum_stateful' => config('sanctum.stateful'),
                ]
            ], 401);
        }

        try {
            $user = Auth::user();
            $wallet = $user->wallet;

            if (!$wallet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wallet not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'balance' => $wallet->balance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get balance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add money to wallet
     */
    public function addMoney(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01|max:10000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $wallet = $user->wallet;

            if (!$wallet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wallet not found'
                ], 404);
            }

            DB::beginTransaction();

            // Update balance
            $wallet->increment('balance', $request->amount);

            // Create transaction record
            $referenceNumber = 'ADD' . str_replace('.', '', microtime(true));
            Transaction::create([
                // For top-up, record the wallet as both sender and receiver to satisfy NOT NULL constraints
                'sender_wallet_id' => $wallet->id,
                'receiver_wallet_id' => $wallet->id,
                'amount' => $request->amount,
                'transaction_type' => 'topup',
                'reference_number' => $referenceNumber,
                'description' => 'Money added to wallet',
                'status' => 'completed',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Money added successfully',
                'reference_number' => $referenceNumber,
                'new_balance' => $wallet->fresh()->balance
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add money: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send money to another user
     */
    public function sendMoney(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'receiver_phone' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $senderWallet = $user->wallet;

            if (!$senderWallet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sender wallet not found'
                ], 404);
            }

            // Find receiver
            $receiver = User::where('phone_number', $request->receiver_phone)->first();
            if (!$receiver) {
                return response()->json([
                    'success' => false,
                    'message' => 'Receiver not found'
                ], 404);
            }

            $receiverWallet = $receiver->wallet;
            if (!$receiverWallet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Receiver wallet not found'
                ], 404);
            }

            // Check balance
            if ($senderWallet->balance < $request->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance'
                ], 400);
            }

            DB::beginTransaction();

            // Deduct from sender
            $senderWallet->decrement('balance', $request->amount);

            // Add to receiver
            $receiverWallet->increment('balance', $request->amount);

            // Create transaction record
            $referenceNumber = 'TXN' . str_replace('.', '', microtime(true));
            Transaction::create([
                'sender_wallet_id' => $senderWallet->id,
                'receiver_wallet_id' => $receiverWallet->id,
                'amount' => $request->amount,
                'transaction_type' => 'send',
                'reference_number' => $referenceNumber,
                'description' => $request->description ?: 'Money transfer',
                'status' => 'completed',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Money sent successfully',
                'reference_number' => $referenceNumber,
                'new_balance' => $senderWallet->fresh()->balance
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to send money: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pay bills
     */
    public function payBills(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'bill_account' => 'required|string',
            'amount' => 'required|numeric|min:0.01|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $wallet = $user->wallet;

            if (!$wallet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wallet not found'
                ], 404);
            }

            // Check balance
            if ($wallet->balance < $request->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance'
                ], 400);
            }

            DB::beginTransaction();

            // Deduct from wallet
            $wallet->decrement('balance', $request->amount);

            // Create transaction record
            $referenceNumber = 'BILL' . str_replace('.', '', microtime(true));
            Transaction::create([
                // Use the wallet id for receiver as well to avoid null constraint issues
                'sender_wallet_id' => $wallet->id,
                'receiver_wallet_id' => $wallet->id,
                'amount' => $request->amount,
                'transaction_type' => 'withdraw',
                'reference_number' => $referenceNumber,
                'description' => "Bill payment to account: {$request->bill_account}",
                'status' => 'completed',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bill payment successful',
                'reference_number' => $referenceNumber,
                'new_balance' => $wallet->fresh()->balance
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Bill payment failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search phone numbers for sending money
     */
    public function searchPhoneNumbers(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

    // Accept both `q` (used by frontend) and `query` for backward compatibility
    $query = $request->get('q', $request->get('query', ''));
        $user = Auth::user();

        if (empty($query)) {
            return response()->json([
                'success' => true,
                'users' => []
            ]);
        }

        try {
            $users = User::where('phone_number', 'like', "%{$query}%")
                ->orWhere('full_name', 'like', "%{$query}%")
                ->where('id', '!=', $user->id)
                ->select('id', 'phone_number', 'full_name')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'users' => $users
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
