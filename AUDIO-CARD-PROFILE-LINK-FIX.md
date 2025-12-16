# Audio Card Profile Link & Image Size Guidelines - Fix

**Issue Date:** December 2024  
**Status:** ✅ Fixed

---

## সমস্যা (Problems Identified)

### Issue 1: Audio Card থেকে Creator Profile Link ভুল
- **Problem:** Audio card (Recent Audio section) এ click করলে creator এর profile দেখাচ্ছিল না
- **Root Cause:** Audio cards `single-audio` page এ redirect করছিল, creator profile page এ নয়
- **Location:** `template-parts/dashboard.php` এবং `template-parts/dashboard-public.php`

### Issue 2: Image Size Guidelines Missing
- **Problem:** Suggestions sidebar এবং creator banner এর জন্য সঠিক image size guideline ছিল না
- **Requirement:** 
  - Banner size guideline creator profile edit এ দেখাতে হবে
  - Suggestions card image size (400x250px) নির্ধারণ করতে হবে

---

## সমাধান (Solutions Implemented)

### 1. Audio Card Link Fix

**Files Modified:**
- `template-parts/dashboard.php`
- `template-parts/dashboard-public.php`
- `js/main.js`

**Changes:**
- ✅ Audio card link এখন creator profile page এ redirect করে
- ✅ `nymia_get_user_profile_url()` function use করে proper profile URL generate করে
- ✅ JavaScript click handler update করা হয়েছে (backward compatibility)

**Before:**
```php
$single_audio_url = add_query_arg('user_id', $creator_id, $single_audio_url);
<a href="<?php echo esc_url($single_audio_url); ?>">
```

**After:**
```php
$profile_url = nymia_get_user_profile_url($creator_id);
<a href="<?php echo esc_url($profile_url); ?>">
```

### 2. Image Size Guidelines

**Files Modified:**
- `page-profile.php` (Banner upload section)
- `functions.php` (Suggestion image comments)

**Banner Size Guidelines:**
- ✅ **Recommended:** 1500x500 pixels (3:1 ratio)
- ✅ Display locations:
  - Profile page
  - Home feed (Recent Audio section)
  - Suggestions sidebar (400x250px cropped from banner)

**Suggestion Card Image Size:**
- ✅ **Display Size:** 400x250 pixels (1.6:1 ratio)
- ✅ Banner images (1500x500px) automatically cropped to fit
- ✅ Placeholder images use 400x250px size

---

## Image Size Specifications

### Creator Banner
- **Recommended Size:** 1500x500 pixels
- **Aspect Ratio:** 3:1 (width:height)
- **Format:** JPG, PNG, GIF, WebP
- **Max File Size:** 5MB
- **Display Locations:**
  1. Profile page (full banner)
  2. Home feed - Recent Audio cards (cropped)
  3. Suggestions sidebar (400x250px cropped)

### Suggestion Card Image
- **Display Size:** 400x250 pixels
- **Aspect Ratio:** 1.6:1 (width:height)
- **Source:** Creator's banner image (cropped automatically)
- **Fallback:** Placeholder images (400x250px)

---

## Testing Checklist

- [x] Audio card click করে creator profile page এ যায়
- [x] Profile URL correctly generated হয়
- [x] Banner size guideline Edit Profile modal এ দেখায়
- [x] Suggestion cards properly display images
- [x] Banner images properly cropped in suggestions

---

## User Flow

### Before Fix:
1. User clicks on audio card → Goes to `/single-audio/` page ❌

### After Fix:
1. User clicks on audio card → Goes to creator's profile page ✅
2. Profile page shows creator's full profile with banner ✅
3. Banner properly sized (1500x500px) ✅

---

## Code Changes Summary

### 1. `template-parts/dashboard.php`
- Changed link from `single-audio` to profile page
- Uses `nymia_get_user_profile_url()` function

### 2. `template-parts/dashboard-public.php`
- Same fix as dashboard.php for public view

### 3. `js/main.js`
- Updated click handler to use profile URL
- Maintains backward compatibility

### 4. `page-profile.php`
- Added detailed banner size guidelines
- Shows where banner will be displayed

### 5. `functions.php`
- Added comments about suggestion image sizes
- Documented 400x250px display size

---

## Image Size Recommendations for Creators

### Banner Upload Guidelines:
1. **Size:** 1500x500 pixels (3:1 ratio)
2. **Format:** JPG, PNG, GIF, or WebP
3. **Max Size:** 5MB
4. **Important:** 
   - Banner will be displayed on profile page
   - Will be cropped to 400x250px for suggestions sidebar
   - Center portion of banner will be visible in suggestions

### Best Practices:
- Use high-quality images
- Keep important content in center (for suggestion crop)
- Use 3:1 aspect ratio for best results
- Optimize file size for faster loading

---

**Fix Complete!** ✅

Audio cards এখন সঠিকভাবে creator profile এ link করে, এবং image size guidelines properly documented হয়েছে।

