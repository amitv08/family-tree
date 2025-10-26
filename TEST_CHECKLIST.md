# Testing Checklist - Family Tree Implementation

## ✅ Quick Verification Steps

### Step 1: Login to WordPress
1. Open browser: http://family-tree.local/wp-admin
2. Login with your credentials

### Step 2: Test Add Member Page

**URL:** http://family-tree.local/add-member

**Visual Checks:**
- [ ] Page loads without errors
- [ ] "Gender" has red asterisk (required field)
- [ ] NO visible "Middle Name" input field
- [ ] NO visible "Last Name" input field
- [ ] Father's Name dropdown exists in "Personal Information" section
- [ ] Mother's Name has a searchable dropdown (not just radio buttons)
- [ ] "Maiden Name" field is HIDDEN by default
- [ ] "Marriages" section exists with "Add Marriage" button
- [ ] Page styling looks correct (no broken layout)

**Functionality Tests:**
1. **Gender Required:**
   - Try to submit without selecting gender
   - Should show error: "Gender is required"

2. **Auto-Populate Middle Name:**
   - Open browser DevTools (F12)
   - Go to Console tab
   - Select any father from dropdown
   - Should see console log: "Middle name auto-populated: [name]"
   - Check Elements tab → find `<input type="hidden" id="middle_name">`
   - Verify value is populated

3. **Auto-Populate Last Name:**
   - Select a clan
   - Select a clan surname
   - Should see console log: "Last name auto-populated: [surname]"
   - Check Elements tab → find `<input type="hidden" id="last_name">`
   - Verify value is populated

4. **Smart Mother Selection:**
   - Select a father who has marriages
   - Should see toast: "Mother auto-selected from father's marriage"
   - Mother dropdown should populate with his wife/wives
   - Try typing a new name → should allow it (Select2 tags)

5. **Maiden Name (Gender-Aware):**
   - Select gender = "Female"
   - Maiden Name field should appear in Marriages section
   - Select gender = "Male"
   - Maiden Name field should hide

6. **Multiple Marriages:**
   - Click "Add Marriage" button
   - Marriage card should appear
   - Fill in spouse name, date, location
   - Click "Add Marriage" again
   - Should see "Marriage #1" and "Marriage #2"
   - Change status to "Divorced"
   - Divorce date field should appear
   - Click × on a marriage
   - Should confirm and remove it

7. **Form Submission:**
   - Fill all required fields (first name, gender, clan)
   - Optionally add marriages
   - Click "Add Member"
   - Should show success message
   - Should redirect to browse members


### Step 3: Test Edit Member Page

**URL:** http://family-tree.local/edit-member?id=1 (use any existing member ID)

**Visual Checks:**
- [ ] Page loads with existing member data
- [ ] Hidden fields contain middle_name and last_name values
- [ ] If member has marriages, they appear as cards
- [ ] Existing marriages show "(Existing)" label
- [ ] Maiden name shows if member is female

**Functionality Tests:**
1. **Existing Marriages Load:**
   - Verify existing marriages display correctly
   - Check all fields are populated

2. **Edit Marriage:**
   - Change spouse name
   - Update marriage date
   - Save form
   - Reload page → verify changes saved

3. **Add New Marriage:**
   - Click "Add Marriage"
   - New card shows "(New)" label
   - Fill details
   - Save form
   - Reload → verify new marriage saved

4. **Delete Marriage:**
   - Click × on existing marriage
   - Should warn: "This will permanently delete..."
   - Confirm deletion
   - Should show success toast
   - Reload page → verify marriage deleted from database


### Step 4: Database Verification

**Using phpMyAdmin or Adminer:**

1. **Check family_members table:**
```sql
SELECT id, first_name, middle_name, last_name, maiden_name, parent1_id, clan_surname_id
FROM wp_family_members
WHERE is_deleted = 0
ORDER BY id DESC
LIMIT 10;
```
- Verify middle_name and last_name are populated for new members

2. **Check family_marriages table:**
```sql
SELECT m.id, m.husband_id, m.wife_id, m.husband_name, m.wife_name,
       m.marriage_date, m.marriage_status
FROM wp_family_marriages m
ORDER BY m.id DESC
LIMIT 10;
```
- Verify multiple marriages are being saved correctly


### Step 5: Migration Test

**Via WordPress Code Snippets or Functions.php:**

Add this temporarily:
```php
add_action('init', function() {
    if (isset($_GET['run_migration']) && current_user_can('manage_options')) {
        $stats = FamilyTreeDatabase::migrate_member_names();
        echo '<pre>';
        print_r($stats);
        echo '</pre>';
        die();
    }
});
```

Then visit: http://family-tree.local/?run_migration=1

Expected output:
```
Array
(
    [middle_name_updated] => X
    [last_name_updated] => Y
    [errors] => Array()
)
```


### Step 6: Browser Console Checks

**Open DevTools (F12) → Console Tab**

Expected console logs when testing:
- ✅ "Edit Member form initialized for member ID: X" (on edit page)
- ✅ "Add Member form initialized" (on add page)
- ✅ "Middle name auto-populated: [name]"
- ✅ "Last name auto-populated: [surname]"
- ✅ "Full name preview: FirstName MiddleName LastName"

**NO errors should appear:**
- ❌ No "Uncaught TypeError"
- ❌ No "Uncaught ReferenceError"
- ❌ No 404 errors for JavaScript files
- ❌ No AJAX errors


### Step 7: Network Tab Checks (Advanced)

**DevTools → Network Tab**

1. **After selecting father:**
   - Look for AJAX call to: `admin-ajax.php?action=get_marriages_for_member`
   - Status should be 200
   - Response should contain marriage data

2. **After submitting form:**
   - Look for: `admin-ajax.php?action=add_family_member`
   - Then multiple: `admin-ajax.php?action=add_marriage`
   - All should return status 200


## Common Issues & Solutions

### Issue: Page shows "Access Denied"
**Solution:** You need to login to WordPress first at: http://family-tree.local/wp-admin

### Issue: JavaScript not working
**Solution:** Hard refresh browser (Ctrl+Shift+R) to clear cache

### Issue: AJAX errors in console
**Solution:** Check wp-content/debug.log for PHP errors

### Issue: Marriages not saving
**Solution:** Verify wp_family_marriages table exists in database

### Issue: Auto-population not working
**Solution:**
1. Check browser console for JavaScript errors
2. Verify clan surname has data-lastname attribute
3. Verify father dropdown has data-firstname attribute


## Success Criteria

✅ **ALL of these should be true:**
1. Add Member page loads without errors
2. Gender is required (validation works)
3. Middle name auto-populates from father
4. Last name auto-populates from clan surname
5. Mother dropdown populates from father's marriages
6. Can type new mother name (tags feature)
7. Maiden name shows/hides based on gender
8. Can add multiple marriages
9. Can remove marriages
10. Form submission saves member + marriages
11. Edit page loads existing marriages
12. Can edit/delete existing marriages
13. Database contains correct data
14. No JavaScript errors in console
15. Migration script works

If ALL checks pass → **Implementation is successful! 🎉**


## Quick Start Testing Guide

**Fastest way to verify everything:**

1. Login: http://family-tree.local/wp-admin
2. Go to: http://family-tree.local/add-member
3. Press F12 (open DevTools)
4. Click Console tab
5. Fill the form:
   - Select gender = Female
   - Select a father
   - Select a clan and surname
   - Click "Add Marriage"
   - Fill spouse details
   - Click "Add Marriage" again
6. Watch Console logs
7. Submit form
8. Check Network tab for AJAX calls
9. Verify success!


## Contact & Support

If you encounter issues:
1. Check browser console for errors
2. Check wp-content/debug.log
3. Verify all files were saved correctly
4. Clear browser cache
5. Restart LocalWP if needed

---

**Implementation Date:** 2025-10-26
**Version:** 3.2.0
**Status:** Ready for Testing
