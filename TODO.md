# Simplify Verification System - Keep Face AI and Document Upload

## Step 1: Add Admin Role to User System
- [ ] Ensure 'is_admin' column exists in users table (migration: 2025_11_05_220806_add_is_admin_to_users_table.php)
- [ ] Update User model to include admin functionality
- [ ] Create admin user creation script

## Step 2: Simplify VerificationController
- [ ] Keep startVerification (document type/number)
- [ ] Keep uploadDocument (front/back)
- [ ] Keep uploadFaceImage (face AI checker)
- [ ] Remove performVerification simulation method
- [ ] Modify performVerification to set status to 'pending' after uploads complete
- [ ] Add admin approval/rejection methods to VerificationController

## Step 3: Create AdminController
- [ ] Create AdminController for verification management
- [ ] Add listPendingVerifications endpoint
- [ ] Add approveVerification endpoint
- [ ] Add rejectVerification endpoint

## Step 4: Update API Routes
- [ ] Add admin routes to routes/api.php with middleware
- [ ] Keep existing verification routes (start, upload document, upload face)
- [ ] Remove performVerification route (replace with admin approval)

## Step 5: Move Frontend Assets
- [ ] Copy all files from public/ to b-cash-laravel/public/
- [ ] Update .htaccess for Laravel
- [ ] Update frontend JavaScript to use new API endpoints

## Step 6: Update Frontend JavaScript
- [ ] Update verification.js for simplified flow (document + face upload, then pending for admin)
- [ ] Ensure auth.js, wallet.js, transaction.js work with new endpoints

## Step 7: Testing
- [ ] Test user verification flow (document + face upload)
- [ ] Test admin approval/rejection
- [ ] Test authentication, wallet, transactions
