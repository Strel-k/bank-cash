# B-Cash Laravel - Finish and Simplify System

## Step 1: Add Admin Role to User System
- [ ] Update users table migration to add 'is_admin' column
- [ ] Update User model to include admin functionality
- [ ] Create admin user creation script

## Step 2: Simplify Verification System
- [ ] Remove face recognition from VerificationController
- [ ] Keep document upload functionality
- [ ] Add admin approval/rejection endpoints to VerificationController
- [ ] Update UserVerification model for manual approval

## Step 3: Create Admin Controller
- [ ] Create AdminController for verification management
- [ ] Add endpoints for listing pending verifications
- [ ] Add endpoints for approving/rejecting verifications

## Step 4: Update API Routes
- [ ] Add admin routes to routes/api.php
- [ ] Ensure proper middleware for admin routes
- [ ] Update verification routes to remove face upload

## Step 5: Move Frontend Assets
- [ ] Copy all files from public/ to b-cash-laravel/public/
- [ ] Update .htaccess for Laravel
- [ ] Update frontend JavaScript to use new API endpoints

## Step 6: Update Frontend JavaScript
- [ ] Update auth.js for new login/register endpoints
- [ ] Update wallet.js for new wallet endpoints
- [ ] Update transaction.js for new transaction endpoints
- [ ] Update verification.js for simplified verification flow

## Step 7: Testing
- [ ] Test authentication endpoints
- [ ] Test wallet operations
- [ ] Test transaction history
- [ ] Test simplified verification flow
- [ ] Test admin approval process

## Step 8: Deployment Preparation
- [ ] Update production environment configuration
- [ ] Ensure proper CORS and session settings
- [ ] Create deployment documentation
