<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    /**
     * Get transaction history
     */
    public function getHistory(Request $request)
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

        $validator = Validator::make($request->all(), [
            'limit' => 'nullable|integer|min:1|max:100',
            'offset' => 'nullable|integer|min:0',
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
            $limit = $request->get('limit', 10);
            $offset = $request->get('offset', 0);

            $transactions = Transaction::forUser($user->id)
                ->with(['senderWallet.user', 'receiverWallet.user'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->offset($offset)
                ->get()
                ->map(function ($transaction) use ($user) {
                    $isSender = $transaction->sender_wallet_id && $transaction->senderWallet->user_id === $user->id;
                    $isReceiver = $transaction->receiver_wallet_id && $transaction->receiverWallet->user_id === $user->id;

                    return [
                        'id' => $transaction->id,
                        'reference_number' => $transaction->reference_number,
                        'amount' => $transaction->amount,
                        'transaction_type' => $transaction->transaction_type,
                        'description' => $transaction->description,
                        'status' => $transaction->status,
                        'created_at' => $transaction->created_at,
                        'direction' => $isSender ? 'sent' : 'received',
                        'other_party' => $isSender
                            ? ($transaction->receiverWallet ? $transaction->receiverWallet->user->full_name : 'External')
                            : ($transaction->senderWallet ? $transaction->senderWallet->user->full_name : 'External'),
                    ];
                });

            // Get total count
            $total = Transaction::forUser($user->id)->count();

            return response()->json([
                'success' => true,
                'transactions' => $transactions,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get transaction history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get transaction statistics
     */
    public function getStats(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        try {
            $user = Auth::user();

            $stats = Transaction::forUser($user->id)
                ->selectRaw('
                    COUNT(*) as total_transactions,
                    SUM(CASE WHEN sender_wallet_id IS NOT NULL AND senderWallet.user_id = ? THEN amount ELSE 0 END) as total_sent,
                    SUM(CASE WHEN receiver_wallet_id IS NOT NULL AND receiverWallet.user_id = ? THEN amount ELSE 0 END) as total_received
                ', [$user->id, $user->id])
                ->first();

            return response()->json([
                'success' => true,
                'stats' => [
                    'total_transactions' => $stats->total_transactions ?? 0,
                    'total_sent' => $stats->total_sent ?? 0,
                    'total_received' => $stats->total_received ?? 0,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get transaction stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search transactions
     */
    public function searchTransactions(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:1|max:100',
            'limit' => 'nullable|integer|min:1|max:50',
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
            $query = $request->get('q');
            $limit = $request->get('limit', 20);

            $transactions = Transaction::forUser($user->id)
                ->where(function ($q) use ($query) {
                    $q->where('reference_number', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%")
                      ->orWhereHas('senderWallet.user', function ($q) use ($query) {
                          $q->where('full_name', 'like', "%{$query}%");
                      })
                      ->orWhereHas('receiverWallet.user', function ($q) use ($query) {
                          $q->where('full_name', 'like', "%{$query}%");
                      });
                })
                ->with(['senderWallet.user', 'receiverWallet.user'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($transaction) use ($user) {
                    $isSender = $transaction->sender_wallet_id && $transaction->senderWallet->user_id === $user->id;
                    $isReceiver = $transaction->receiver_wallet_id && $transaction->receiverWallet->user_id === $user->id;

                    return [
                        'id' => $transaction->id,
                        'reference_number' => $transaction->reference_number,
                        'amount' => $transaction->amount,
                        'transaction_type' => $transaction->transaction_type,
                        'description' => $transaction->description,
                        'status' => $transaction->status,
                        'created_at' => $transaction->created_at,
                        'direction' => $isSender ? 'sent' : 'received',
                        'other_party' => $isSender
                            ? ($transaction->receiverWallet ? $transaction->receiverWallet->user->full_name : 'External')
                            : ($transaction->senderWallet ? $transaction->senderWallet->user->full_name : 'External'),
                    ];
                });

            return response()->json([
                'success' => true,
                'transactions' => $transactions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get transaction by reference number
     */
    public function getTransactionByReference(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'reference' => 'required|string',
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
            $reference = $request->get('reference');

            $transaction = Transaction::where('reference_number', $reference)
                ->forUser($user->id)
                ->with(['senderWallet.user', 'receiverWallet.user'])
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }

            $isSender = $transaction->sender_wallet_id && $transaction->senderWallet->user_id === $user->id;
            $isReceiver = $transaction->receiver_wallet_id && $transaction->receiverWallet->user_id === $user->id;

            $transactionData = [
                'id' => $transaction->id,
                'reference_number' => $transaction->reference_number,
                'amount' => $transaction->amount,
                'transaction_type' => $transaction->transaction_type,
                'description' => $transaction->description,
                'status' => $transaction->status,
                'created_at' => $transaction->created_at,
                'direction' => $isSender ? 'sent' : 'received',
                'other_party' => $isSender
                    ? ($transaction->receiverWallet ? $transaction->receiverWallet->user->full_name : 'External')
                    : ($transaction->senderWallet ? $transaction->senderWallet->user->full_name : 'External'),
            ];

            return response()->json([
                'success' => true,
                'transaction' => $transactionData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get transaction: ' . $e->getMessage()
            ], 500);
        }
    }
}
