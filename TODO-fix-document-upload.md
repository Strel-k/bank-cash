# Fix Document Upload Issues - TODO List

## Issues Identified:
1. CORS policy conflict with wildcard origin when credentials are included
2. Missing verification_id in document upload request
3. Session management issues between registration and verification

## Files to Fix:
- [x] public/api/verification.php (CORS handling)
- [x] public/js/register-verification.js (request payload)
- [x] app/controllers/VerificationController.php (verification ID handling)

## Steps:
1. Fix CORS policy in verification.php
2. Update register-verification.js to include verification_id
3. Modify VerificationController to handle missing verification_id
4. Test the fixes

## Progress:
- [x] Step 1: Fix CORS in verification.php
- [x] Step 2: Update register-verification.js
- [x] Step 3: Update VerificationController
- [x] Step 4: Testing - CORS issue resolved
