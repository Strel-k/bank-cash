<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    /**
     * Get wallet balance
     */
    public function getBalance(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        try {
            $user = Auth::user();
            $wallet = Wallet::where('user_id', $user->id)->first();

            if (!$wallet) {
                // Create wallet if it doesn't exist
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'balance' => 0.00,
                    'account_number' => $this->generateAccountNumber(),
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'balance' => (float) $wallet->balance
                ]
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
    public function addMoney(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01|max:100000',
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
            $amount = $request->input('amount');
            
            $wallet = Wallet::where('user_id', $user->id)->firstOrFail();
            $wallet->balance += $amount;
            $wallet->save();

            // Create transaction record
            $transaction = Transaction::create([
                'reference_number' => $this->generateReferenceNumber(),
                'amount' => $amount,
                'transaction_type' => 'topup',
                'description' => 'Wallet top-up',
                'status' => 'completed',
                'user_id' => $user->id,
                'receiver_wallet_id' => $wallet->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Money added successfully',
                'data' => [
                    'new_balance' => (float) $wallet->balance,
                    'reference_number' => $transaction->reference_number
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add money: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send money to another user
     */
    public function sendMoney(Request $request): JsonResponse
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
            $receiverPhone = $request->input('receiver_phone');
            $amount = $request->input('amount');
            $description = $request->input('description', 'Money transfer');

            // Get sender's wallet
            $senderWallet = Wallet::where('user_id', $user->id)->firstOrFail();

            // Check if sender has sufficient balance
            if ($senderWallet->balance < $amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance'
                ], 400);
            }

            // Find receiver by phone number
            $receiver = User::where('phone_number', $receiverPhone)->first();
            if (!$receiver) {
                return response()->json([
                    'success' => false,
                    'message' => 'Receiver not found'
                ], 404);
            }

            // Prevent sending to self
            if ($receiver->id === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot send money to yourself'
                ], 400);
            }

            // Get receiver's wallet
            $receiverWallet = Wallet::where('user_id', $receiver->id)->first();
            if (!$receiverWallet) {
                $receiverWallet = Wallet::create([
                    'user_id' => $receiver->id,
                    'balance' => 0.00,
                    'account_number' => $this->generateAccountNumber(),
                ]);
            }

            // Perform the transfer
            $senderWallet->balance -= $amount;
            $receiverWallet->balance += $amount;

            $senderWallet->save();
            $receiverWallet->save();

            // Create transaction record
            $transaction = Transaction::create([
                'reference_number' => $this->generateReferenceNumber(),
                'amount' => $amount,
                'transaction_type' => 'send',
                'description' => $description,
                'status' => 'completed',
                'user_id' => $user->id,
                'sender_wallet_id' => $senderWallet->id,
                'receiver_wallet_id' => $receiverWallet->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Money sent successfully',
                'data' => [
                    'new_balance' => (float) $senderWallet->balance,
                    'reference_number' => $transaction->reference_number,
                    'receiver_name' => $receiver->full_name
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send money: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pay bills
     */
    public function payBills(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'bill_account' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
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
            $billAccount = $request->input('bill_account');
            $amount = $request->input('amount');

            $wallet = Wallet::where('user_id', $user->id)->firstOrFail();

            // Check balance
            if ($wallet->balance < $amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance'
                ], 400);
            }

            // Deduct amount
            $wallet->balance -= $amount;
            $wallet->save();

            // Create transaction record
            $transaction = Transaction::create([
                'reference_number' => $this->generateReferenceNumber(),
                'amount' => $amount,
                'transaction_type' => 'bill_payment',
                'description' => 'Bill payment to ' . $billAccount,
                'status' => 'completed',
                'user_id' => $user->id,
                'sender_wallet_id' => $wallet->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bill paid successfully',
                'data' => [
                    'new_balance' => (float) $wallet->balance,
                    'reference_number' => $transaction->reference_number
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to pay bill: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search users by phone or name - FIXED METHOD NAME
     */
    public function searchUsers(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:1|max:100',
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
            $query = $request->input('q');

            $users = User::where('id', '!=', $user->id) // Exclude current user
                ->where(function ($q) use ($query) {
                    $q->where('phone_number', 'like', "%{$query}%")
                      ->orWhere('full_name', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%");
                })
                ->limit(10)
                ->get(['id', 'full_name', 'phone_number', 'email']);

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $users
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user transactions
     */
    public function getTransactions(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        try {
            $user = Auth::user();
            $page = $request->query('page', 1);
            $perPage = $request->query('per_page', 10);

            $transactions = Transaction::where('user_id', $user->id)
                ->with(['senderWallet.user', 'receiverWallet.user'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            $transactionsData = $transactions->map(function ($transaction) use ($user) {
                return $this->formatTransaction($transaction, $user);
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'transactions' => $transactionsData,
                    'current_page' => $transactions->currentPage(),
                    'total' => $transactions->total(),
                    'per_page' => $transactions->perPage(),
                    'last_page' => $transactions->lastPage()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get transactions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique account number
     */
    private function generateAccountNumber(): string
    {
        do {
            $accountNumber = 'BC' . str_pad(mt_rand(1, 9999999999), 10, '0', STR_PAD_LEFT);
        } while (Wallet::where('account_number', $accountNumber)->exists());

        return $accountNumber;
    }

    /**
     * Generate unique reference number
     */
    private function generateReferenceNumber(): string
    {
        do {
            $reference = 'REF' . strtoupper(Str::random(10));
        } while (Transaction::where('reference_number', $reference)->exists());

        return $reference;
    }

    /**
     * Format transaction for response
     */
    private function formatTransaction(Transaction $transaction, $user): array
    {
        $isSender = $transaction->sender_wallet_id && $transaction->senderWallet && $transaction->senderWallet->user_id === $user->id;
        $isReceiver = $transaction->receiver_wallet_id && $transaction->receiverWallet && $transaction->receiverWallet->user_id === $user->id;

        $counterparty = null;
        if ($isSender) {
            $counterparty = $transaction->receiverWallet ? 
                $transaction->receiverWallet->user->full_name : 
                'External Account';
        } elseif ($isReceiver) {
            $counterparty = $transaction->senderWallet ? 
                $transaction->senderWallet->user->full_name : 
                'External Account';
        }

        return [
            'id' => $transaction->id,
            'reference_number' => $transaction->reference_number,
            'amount' => (float) $transaction->amount,
            'transaction_type' => $transaction->transaction_type,
            'type' => $transaction->transaction_type,
            'description' => $transaction->description,
            'status' => $transaction->status,
            'created_at' => $transaction->created_at?->toISOString(),
            'direction' => $isSender ? 'sent' : ($isReceiver ? 'received' : 'unknown'),
            'counterparty' => $counterparty,
        ];
    }
}