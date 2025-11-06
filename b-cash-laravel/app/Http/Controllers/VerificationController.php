<?php

namespace App\Http\Controllers;

use App\Models\Verification;
use App\Models\VerificationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class VerificationController extends Controller
{
    public function showVerificationPage()
    {
        $user = Auth::user();
        $verification = $user->latestVerification;
        
        return view('verification', compact('verification'));
    }
    
    public function showAdminVerifications()
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            abort(403, 'Unauthorized');
        }
        return view('admin.verifications');
    }

    /**
     * Start verification process
     */
    public function startVerification(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'document_type' => 'required|in:passport,drivers_license,national_id,other',
            'document_number' => 'required|string|max:100',
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

            // Check if user already has pending verification
            $existing = Verification::where('user_id', $user->id)
                ->where('verification_status', 'pending')
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification already in progress'
                ], 400);
            }

            // Create verification request
            $verification = Verification::create([
                'user_id' => $user->id,
                'verification_status' => 'pending',
                'id_document_type' => $request->document_type,
                'id_document_number' => $request->document_number,
                'verification_attempts' => 0,
            ]);

            // Log the action
            VerificationLog::create([
                'user_id' => $user->id,
                'action' => 'document_upload',
                'metadata' => ['document_type' => $request->document_type],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Verification started successfully',
                'verification_id' => $verification->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start verification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload document
     */
    public function uploadDocument(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'verification_id' => 'required|integer|exists:verifications,id',
            // Accept either 'document' or 'image' (some frontends use 'image')
            'document' => 'required_without:image|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
            'image' => 'required_without:document|file|mimes:jpg,jpeg,png,pdf|max:5120',
            // Accept either 'side' or 'type'
            'side' => 'required_without:type|in:front,back',
            'type' => 'required_without:side|in:front,back',
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
            $verification = Verification::where('user_id', $user->id)
                ->where('id', $request->verification_id)
                ->first();

            if (!$verification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification not found'
                ], 404);
            }

            if ($verification->verification_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification is not in pending status'
                ], 400);
            }

            // Store the file (either 'document' or 'image')
            $file = $request->file('document') ?: $request->file('image');
            $side = $request->side ?: $request->type;
            $filename = 'verification_' . $verification->id . '_' . $side . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('verifications', $filename, 'public');

            // Update verification record
            $column = $side === 'front' ? 'id_document_front_path' : 'id_document_back_path';
            $verification->update([$column => $path]);

            // Log the action
            VerificationLog::create([
                'user_id' => $user->id,
                'action' => 'document_upload',
                'metadata' => [
                    'verification_id' => $verification->id,
                    'side' => $request->side,
                    'filename' => $filename
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload face image
     */
    public function uploadFaceImage(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'verification_id' => 'required|integer|exists:verifications,id',
            'face_image' => 'required|file|mimes:jpg,jpeg,png|max:5120', // 5MB max
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
            $verification = Verification::where('user_id', $user->id)
                ->where('id', $request->verification_id)
                ->first();

            if (!$verification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification not found'
                ], 404);
            }

            if ($verification->verification_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification is not in pending status'
                ], 400);
            }

            // Store the file
            $file = $request->file('face_image');
            $filename = 'face_' . $verification->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('verifications/faces', $filename, 'public');

            // Simulate face encoding (in production, use actual face recognition API)
            $faceEncoding = json_encode(['encoding' => 'sample_face_encoding_' . time()]);

            // Update verification record
            $verification->update([
                'face_image_path' => $path,
                'face_encoding' => $faceEncoding,
            ]);

            // Log the action
            VerificationLog::create([
                'user_id' => $user->id,
                'action' => 'face_capture',
                'metadata' => [
                    'verification_id' => $verification->id,
                    'filename' => $filename
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Face image uploaded successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload face image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Perform verification - simplified to set pending for admin approval
     */
    public function performVerification(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'verification_id' => 'required|integer|exists:verifications,id',
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
            $verification = Verification::where('user_id', $user->id)
                ->where('id', $request->verification_id)
                ->first();

            if (!$verification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification not found'
                ], 404);
            }

            if ($verification->verification_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification is not in pending status'
                ], 400);
            }

            // Check if both document and face are uploaded
            if (!$verification->id_document_front_path || !$verification->face_image_path) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please upload both document and face image before submitting for verification'
                ], 400);
            }

            // Increment attempts
            $verification->increment('verification_attempts');

            // Set status to pending for admin approval
            $verification->update([
                'verification_status' => 'pending_admin',
            ]);

            // Log the action
            VerificationLog::create([
                'user_id' => $user->id,
                'action' => 'verification_submitted',
                'metadata' => [
                    'verification_id' => $verification->id,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Verification submitted for admin review',
                'status' => 'pending_admin',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verification submission failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get verification status
     */
    public function getVerificationStatus(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        try {
            $user = Auth::user();
            $verification = Verification::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->first();

            return response()->json([
                'success' => true,
                'verification' => $verification ? [
                    'id' => $verification->id,
                    'status' => $verification->verification_status,
                    'document_type' => $verification->id_document_type,
                    'document_number' => $verification->id_document_number,
                    'created_at' => $verification->created_at,
                    'verified_at' => $verification->verified_at,
                    'attempts' => $verification->verification_attempts,
                ] : null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get verification status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin approve verification
     */
    public function approveVerification(Request $request)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'verification_id' => 'required|integer|exists:verifications,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $verification = Verification::find($request->verification_id);

            if (!$verification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification not found'
                ], 404);
            }

            if ($verification->verification_status !== 'pending_admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification is not pending admin approval'
                ], 400);
            }

            // Approve verification
            $verification->update([
                'verification_status' => 'verified',
                'verified_at' => now(),
            ]);

            // Update user verification status
            $verification->user->update(['is_verified' => true]);

            // Log the action
            VerificationLog::create([
                'user_id' => $verification->user_id,
                'action' => 'verification_approved',
                'metadata' => [
                    'verification_id' => $verification->id,
                    'approved_by' => Auth::id(),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Verification approved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve verification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin reject verification
     */
    public function rejectVerification(Request $request)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'verification_id' => 'required|integer|exists:verifications,id',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $verification = Verification::find($request->verification_id);

            if (!$verification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification not found'
                ], 404);
            }

            if ($verification->verification_status !== 'pending_admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification is not pending admin approval'
                ], 400);
            }

            // Reject verification
            $verification->update([
                'verification_status' => 'rejected',
            ]);

            // Log the action
            VerificationLog::create([
                'user_id' => $verification->user_id,
                'action' => 'verification_rejected',
                'metadata' => [
                    'verification_id' => $verification->id,
                    'rejected_by' => Auth::id(),
                    'reason' => $request->reason,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Verification rejected successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject verification: ' . $e->getMessage()
            ], 500);
        }
    }
}