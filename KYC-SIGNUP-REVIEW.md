# KYC Integration in Creator Sign-Up - Review & Fix

**Issue Date:** December 2024  
**Issue:** Missing KYC (Know Your Customer) ID verification in creator sign-up flow

---

## Problem Analysis

### Current Implementation

1. **Sign-Up Form** (`auth/auth-signup.php`):
   - ✅ Has account type selection (User/Creator)
   - ✅ Has basic fields (name, email, password, DOB, address, country, city, zip)
   - ❌ **MISSING:** KYC/ID verification fields
   - ❌ **MISSING:** ID document upload
   - ❌ **MISSING:** Camera capture functionality

2. **KYC Modal** (`template-parts/become-creator-modal.php`):
   - ✅ Has complete KYC form with all required fields
   - ✅ Has ID document upload
   - ✅ Has camera capture with instruction: "Take a photo with your document close to your face, so that both the document and your face are visible"
   - ✅ Has "Use Camera" button
   - ⚠️ **ISSUE:** Only accessible AFTER sign-up via "Become Creator" button

### What's Missing

When a user selects **"Creator"** during sign-up, they should be prompted to:
1. Complete KYC verification immediately
2. Upload ID document OR capture photo with camera
3. Provide ID type and ID number
4. See the camera capture instruction

Currently, users who select "Creator" during sign-up:
- Complete basic registration
- Must later click "Become Creator" button
- Then complete KYC in a separate modal

This creates a **disconnected user experience** and may cause users to skip KYC verification.

---

## Solution

### Option 1: Conditional KYC Fields in Sign-Up Form (Recommended)

Add KYC fields that appear when "Creator" is selected:
- Show/hide KYC section based on account type selection
- Include all KYC fields inline in the sign-up form
- Include camera capture functionality
- Make KYC fields required when account_type = "creator"

**Pros:**
- Single-step registration for creators
- Better user experience
- Immediate KYC collection
- No separate modal needed

**Cons:**
- Longer sign-up form
- More complex form validation

### Option 2: Redirect to KYC After Sign-Up

Keep current flow but redirect creators to KYC immediately after registration.

**Pros:**
- Simpler sign-up form
- Clear separation of concerns

**Cons:**
- Two-step process
- Users might skip KYC step

---

## Recommended Implementation

**Implement Option 1** - Add conditional KYC fields to sign-up form.

### Changes Required

1. **Modify `auth/auth-signup.php`:**
   - Add conditional KYC section that shows when "Creator" is selected
   - Include ID type, ID number fields
   - Include ID document upload
   - Include camera capture functionality
   - Add camera instruction text

2. **Modify `auth/auth-scripts.php`:**
   - Add JavaScript to show/hide KYC section based on account type
   - Integrate camera capture functionality
   - Add form validation for KYC fields when creator is selected

3. **Modify `functions.php` (registration handler):**
   - Validate KYC fields when account_type = "creator"
   - Save KYC data during registration
   - Set KYC status to "pending" for new creators

4. **Update CSS:**
   - Style KYC section in sign-up form
   - Ensure camera preview works correctly

---

## Implementation Plan

### Step 1: Add KYC Section to Sign-Up Form
- Add conditional KYC fields after account type selection
- Include all required KYC fields
- Add camera capture UI

### Step 2: Add JavaScript Functionality
- Show/hide KYC section based on account type
- Integrate camera capture
- Add validation

### Step 3: Update Registration Handler
- Validate KYC data for creators
- Save KYC information
- Set appropriate status

### Step 4: Testing
- Test with "User" account type (should not show KYC)
- Test with "Creator" account type (should show KYC)
- Test camera capture
- Test file upload
- Test form validation
- Test form submission

---

## Code Changes Summary

### Files to Modify:
1. `auth/auth-signup.php` - Add KYC section
2. `auth/auth-scripts.php` - Add show/hide logic and camera functionality
3. `functions.php` - Update registration handler
4. `auth/auth-styles.php` - Add KYC styles (if needed)

### New Features:
- Conditional KYC section in sign-up form
- Camera capture in sign-up flow
- Integrated validation
- Seamless user experience

---

## Testing Checklist

- [ ] KYC section appears when "Creator" is selected
- [ ] KYC section hides when "User" is selected
- [ ] Camera capture works correctly
- [ ] File upload works correctly
- [ ] Form validation works
- [ ] KYC data is saved during registration
- [ ] KYC status is set to "pending"
- [ ] Error messages display correctly
- [ ] Mobile responsive
- [ ] Camera permission requests work

---

**Status:** Ready for Implementation  
**Priority:** HIGH  
**Estimated Time:** 2-3 hours

