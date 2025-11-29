<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    /**
     * Get transaction history with pagination
     */
    public function getHistory(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
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
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', $request->get('limit', 10));
            $offset = $request->get('offset', 0);

            // Use pagination for better performance with large datasets
            $query = Transaction::forUser($user->id)
                ->with(['senderWallet.user', 'receiverWallet.user'])
                ->orderBy('created_at', 'desc');

            // If using offset/limit (for backward compatibility)
            if ($request->has('offset')) {
                $transactions = $query->limit($perPage)
                    ->offset($offset)
                    ->get();
                
                $total = Transaction::forUser($user->id)->count();

                $transactionsData = $transactions->map(function ($transaction) use ($user) {
                    return $this->formatTransaction($transaction, $user);
                });

                return response()->json([
                    'success' => true,
                    'data' => [
                        'transactions' => $transactionsData,
                        'total' => $total,
                        'limit' => $perPage,
                        'offset' => $offset
                    ]
                ]);
            } else {
                // Use Laravel pagination
                $paginatedTransactions = $query->paginate($perPage, ['*'], 'page', $page);
                
                $transactionsData = $paginatedTransactions->map(function ($transaction) use ($user) {
                    return $this->formatTransaction($transaction, $user);
                });

                return response()->json([
                    'success' => true,
                    'data' => [
                        'transactions' => $transactionsData,
                        'current_page' => $paginatedTransactions->currentPage(),
                        'total' => $paginatedTransactions->total(),
                        'per_page' => $paginatedTransactions->perPage(),
                        'last_page' => $paginatedTransactions->lastPage()
                    ]
                ]);
            }

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
    public function getStats(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        try {
            $user = Auth::user();

            // Using multiple queries for better readability and performance
            $totalSent = Transaction::forUser($user->id)
                ->whereHas('senderWallet', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->sum('amount');

            $totalReceived = Transaction::forUser($user->id)
                ->whereHas('receiverWallet', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->sum('amount');

            $transactionCount = Transaction::forUser($user->id)->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => [
                        'total_sent' => (float) $totalSent,
                        'total_received' => (float) $totalReceived,
                        'transaction_count' => $transactionCount,
                    ]
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
    public function searchTransactions(Request $request): JsonResponse
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
                      ->orWhere('transaction_type', 'like', "%{$query}%")
                      ->orWhereHas('senderWallet.user', function ($q) use ($query) {
                          $q->where('full_name', 'like', "%{$query}%")
                            ->orWhere('phone_number', 'like', "%{$query}%");
                      })
                      ->orWhereHas('receiverWallet.user', function ($q) use ($query) {
                          $q->where('full_name', 'like', "%{$query}%")
                            ->orWhere('phone_number', 'like', "%{$query}%");
                      });
                })
                ->with(['senderWallet.user', 'receiverWallet.user'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($transaction) use ($user) {
                    return $this->formatTransaction($transaction, $user);
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'transactions' => $transactions
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
     * Get transaction by reference number
     */
    public function getTransactionByReference(Request $request): JsonResponse
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

            $transactionData = $this->formatTransaction($transaction, $user);

            return response()->json([
                'success' => true,
                'data' => [
                    'transaction' => $transactionData
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format transaction data for consistent API response
     */
    private function formatTransaction(Transaction $transaction, $user): array
    {
        $isSender = $transaction->sender_wallet_id && $transaction->senderWallet && $transaction->senderWallet->user_id === $user->id;
        $isReceiver = $transaction->receiver_wallet_id && $transaction->receiverWallet && $transaction->receiverWallet->user_id === $user->id;

        // Determine transaction type and counterparty
        $type = $transaction->transaction_type;
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
            'type' => $transaction->transaction_type, // Alias for frontend compatibility
            'description' => $transaction->description,
            'status' => $transaction->status,
            'created_at' => $transaction->created_at?->toISOString(),
            'updated_at' => $transaction->updated_at?->toISOString(),
            
            // Enhanced fields for frontend display
            'direction' => $isSender ? 'sent' : ($isReceiver ? 'received' : 'unknown'),
            'counterparty' => $counterparty,
            'sender_name' => $transaction->senderWallet ? $transaction->senderWallet->user->full_name : null,
            'sender_phone' => $transaction->senderWallet ? $transaction->senderWallet->user->phone_number : null,
            'receiver_name' => $transaction->receiverWallet ? $transaction->receiverWallet->user->full_name : null,
            'receiver_phone' => $transaction->receiverWallet ? $transaction->receiverWallet->user->phone_number : null,
            
            // For dashboard display compatibility
            'reference' => $transaction->reference_number, // Alias
        ];
    }

    /**
     * Get recent transactions (simplified version for dashboard)
     */
    public function getRecentTransactions(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        try {
            $user = Auth::user();
            $limit = $request->get('limit', 5);

            $transactions = Transaction::forUser($user->id)
                ->with(['senderWallet.user', 'receiverWallet.user'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($transaction) use ($user) {
                    return $this->formatTransaction($transaction, $user);
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'transactions' => $transactions
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get recent transactions: ' . $e->getMessage()
            ], 500);
        }
    }
}