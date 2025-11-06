# Fix Authentication Issue in Verification Upload

## Problem
The verification document upload fails with 401 Unauthorized error because the session cookie is not being maintained between registration and verification upload requests.

## Steps to Fix
1. [x] Update `register-verification.js` - Add `credentials: 'include'` to fetch requests
2. [x] Update CORS headers in `verification.php` - Added dynamic origin handling and `Access-Control-Allow-Credentials: true`
3. [ ] Test the complete registration flow

## Files Modified
- public/js/register-verification.js - Added `credentials: 'include'` to fetch requests
- public/api/verification.php - Updated CORS headers to support credentials

## Testing
- Test registration with verification document upload
- Verify session is maintained between requests
- Check that 401 errors are resolved
