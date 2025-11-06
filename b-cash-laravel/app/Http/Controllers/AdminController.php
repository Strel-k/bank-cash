<?php

namespace App\Http\Controllers;

use App\Models\UserVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * List all pending verifications for admin review
     */
    public function listPendingVerifications(Request $request)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $verifications = UserVerification::with('user')
                ->where('verification_status', 'pending_admin')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($verification) {
                    return [
                        'id' => $verification->id,
                        'user_id' => $verification->user_id,
                        'user_name' => $verification->user->full_name,
                        'user_phone' => $verification->user->phone_number,
                        'document_type' => $verification->id_document_type,
                        'document_number' => $verification->id_document_number,
                        'has_front_document' => !empty($verification->id_document_front_path),
                        'has_back_document' => !empty($verification->id_document_back_path),
                        'has_face_image' => !empty($verification->face_image_path),
                        'created_at' => $verification->created_at,
                        'attempts' => $verification->verification_attempts,
                    ];
                });

            return response()->json([
                'success' => true,
                'verifications' => $verifications
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch verifications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve a verification (delegate to VerificationController)
     */
    public function approveVerification(Request $request)
    {
        $verificationController = new VerificationController();
        return $verificationController->approveVerification($request);
    }

    /**
     * Reject a verification (delegate to VerificationController)
     */
    public function rejectVerification(Request $request)
    {
        $verificationController = new VerificationController();
        return $verificationController->rejectVerification($request);
    }
}
