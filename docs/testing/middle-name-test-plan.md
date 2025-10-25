# Middle Name Field - Test Plan

**Version:** 2.6.0
**Date:** October 24, 2025
**Feature:** Middle name field for complete name tracking

---

## Pre-Test Verification

### Code Changes Summary
- ✅ Database schema updated (`includes/database.php:116`)
- ✅ MemberRepository updated (add & update methods)
- ✅ Legacy database methods updated
- ✅ Add member form updated (3-column layout)
- ✅ Edit member form updated (with pre-fill)
- ✅ View member page updated (full name display)
- ✅ Version bumped to 2.6.0

---

## Testing Steps

### Step 1: Plugin Activation (Database Migration)

**Action:**
1. Navigate to: `http://family-tree.local/wp-admin/plugins.php`
2. Find "Family Tree" plugin
3. Click **"Deactivate"**
4. Wait for confirmation
5. Click **"Activate"**

**Expected Results:**
- ✅ Plugin activates successfully (no errors)
- ✅ Green success message appears
- ✅ No PHP warnings in browser
- ✅ Check `wp-content/debug.log` for errors (should be clean)

**Database Verification:**
```sql
-- Run this in Adminer/phpMyAdmin to verify column was added
SHOW COLUMNS FROM wp_family_members LIKE 'middle_name';

-- Expected output:
-- Field: middle_name
-- Type: varchar(100)
-- Null: YES
-- Default: NULL
```

---

### Step 2: Test Add Member Form

**Action:**
1. Navigate to: `http://family-tree.local/add-member`
2. Verify form layout

**Expected Results:**
- ✅ "Personal Information" section shows **3-column layout**:
  - Column 1: First Name (required)
  - Column 2: Middle Name (optional) - **NEW**
  - Column 3: Last Name (required)
- ✅ Middle name field has placeholder: "e.g., William"
- ✅ Middle name field has help text: "Middle name or initial (optional)"
- ✅ Form is visually balanced (3 equal columns)

**Test Data:**
Fill in the form with:
- First Name: `John`
- Middle Name: `William` ← **NEW FIELD**
- Last Name: `Smith`
- Clan: Select any clan
- Gender: Male
- Other fields: Optional

**Action:**
- Click "Add Member" button

**Expected Results:**
- ✅ Success message: "Member added successfully"
- ✅ No JavaScript errors in console (F12 → Console tab)
- ✅ Redirected to view page or member list

---

### Step 3: Test View Member Page

**Action:**
1. Navigate to the member you just created
2. Or go to: `http://family-tree.local/browse-members` and click on "John William Smith"

**Expected Results:**

**Header Section:**
- ✅ Page title shows: `👤 John William Smith` (full name with middle)
- ✅ Breadcrumb shows: `John William Smith`

**Card Header (Blue gradient section):**
- ✅ Name displayed as: `👤 John William Smith`

**Personal Details Card:**
- ✅ "Full Name:" field shows: `John William Smith`

**All References:**
- ✅ Every place that shows the name includes the middle name
- ✅ Name formatting is consistent throughout

---

### Step 4: Test Edit Member Form

**Action:**
1. From the view page, click **"✏️ Edit"** button
2. Or navigate to: `http://family-tree.local/edit-member?id=X` (replace X with member ID)

**Expected Results:**

**Form Layout:**
- ✅ 3-column layout for name fields (First | Middle | Last)
- ✅ Middle name field is **pre-filled** with `William`
- ✅ Field is editable

**Test Edit - Change Middle Name:**
- Change middle name from `William` to `W.` (initial only)
- Click "Update Member"

**Expected Results:**
- ✅ Success message: "Member updated successfully"
- ✅ View page now shows: `John W. Smith`
- ✅ All references updated

---

### Step 5: Test Empty Middle Name (Optional Field)

**Action:**
1. Click **"Add Member"** again
2. Fill in:
   - First Name: `Jane`
   - Middle Name: **(leave blank)** ← Test optional field
   - Last Name: `Doe`
   - Fill in required fields (clan, gender)
3. Click "Add Member"

**Expected Results:**
- ✅ Member saves successfully (middle name is optional)
- ✅ View page shows: `Jane Doe` (no extra space)
- ✅ Database has `middle_name = NULL` for this member
- ✅ Edit form shows empty middle name field (not "null" or "NULL")

---

### Step 6: Test Existing Members (Backward Compatibility)

**Action:**
1. Navigate to: `http://family-tree.local/browse-members`
2. Find a member that existed **before** this update
3. Click to view them

**Expected Results:**
- ✅ View page displays correctly (no errors)
- ✅ Name shows as: `First Last` (no middle name, no extra space)
- ✅ Edit form shows empty middle name field
- ✅ Can add a middle name via edit and save successfully

---

### Step 7: Test Form Validation

**Action:**
1. Go to add member form
2. Fill in:
   - First Name: `Test`
   - Middle Name: (some very long text, 150+ characters)
   - Last Name: `User`

**Expected Results:**
- ✅ Member saves successfully (database allows VARCHAR(100))
- ✅ Middle name is truncated to 100 characters
- ✅ No database errors

**Action:**
2. Test with special characters:
   - Middle Name: `O'Brien-Smith`

**Expected Results:**
- ✅ Saves correctly
- ✅ Displays correctly (no HTML escaping issues)
- ✅ Special characters preserved

---

### Step 8: Browser Console Check

**Action:**
1. Open browser DevTools (F12)
2. Go to **Console** tab
3. Navigate to add/edit/view member pages

**Expected Results:**
- ✅ No JavaScript errors
- ✅ No 404 errors for CSS/JS files
- ✅ No AJAX errors

---

### Step 9: Database Verification

**Action:**
Run these SQL queries in Adminer/phpMyAdmin:

```sql
-- Check column exists
SHOW COLUMNS FROM wp_family_members LIKE 'middle_name';

-- Check data for new member (John William Smith)
SELECT id, first_name, middle_name, last_name
FROM wp_family_members
WHERE first_name = 'John' AND last_name = 'Smith';

-- Check data for member without middle name (Jane Doe)
SELECT id, first_name, middle_name, last_name
FROM wp_family_members
WHERE first_name = 'Jane' AND last_name = 'Doe';

-- Check existing members have NULL middle_name
SELECT COUNT(*) as members_without_middle_name
FROM wp_family_members
WHERE middle_name IS NULL;
```

**Expected Results:**
- ✅ `middle_name` column exists
- ✅ John William Smith has `middle_name = 'William'`
- ✅ Jane Doe has `middle_name = NULL`
- ✅ Existing members have `middle_name = NULL`

---

### Step 10: WordPress Debug Log Check

**Action:**
```bash
# View the last 50 lines of debug log
tail -n 50 "C:\Users\Amit\Local Sites\family-tree\app\public\wp-content\debug.log"
```

**Expected Results:**
- ✅ No PHP errors related to middle_name
- ✅ No database errors
- ✅ No undefined property warnings

---

## Edge Cases to Test

### Test 1: Very Long Middle Name
- Input: `Bartholomew-Christopher-Alexander`
- Expected: Saves and displays correctly

### Test 2: Single Initial
- Input: `J.`
- Expected: Displays as "John J. Smith"

### Test 3: Multiple Words
- Input: `Maria Elena`
- Expected: Displays as "John Maria Elena Smith"

### Test 4: Unicode Characters
- Input: `José` or `李` (Chinese character)
- Expected: Saves and displays correctly

### Test 5: HTML/Script Injection (Security)
- Input: `<script>alert('test')</script>`
- Expected: Sanitized and escaped (displays as plain text, no script execution)

---

## Success Criteria

### Must Pass ✅
- [ ] Plugin activates without errors
- [ ] Database column `middle_name` is created
- [ ] Add form shows 3-column layout
- [ ] Can save member with middle name
- [ ] Can save member without middle name (optional)
- [ ] View page displays full name correctly
- [ ] Edit form pre-fills middle name
- [ ] Can update middle name
- [ ] Existing members work without errors
- [ ] No JavaScript console errors
- [ ] No PHP errors in debug.log

### Nice to Have ✅
- [ ] Form layout looks visually balanced
- [ ] Help text is clear
- [ ] Special characters handled correctly
- [ ] Unicode support works

---

## Rollback Plan (If Tests Fail)

If any critical tests fail:

1. **Restore from Git:**
```bash
cd "C:\Users\Amit\Local Sites\family-tree\app\public\wp-content\plugins\family-tree"
git checkout .
```

2. **Reactivate Plugin:**
- Deactivate and reactivate in WordPress admin

3. **Report Issue:**
- Note which test failed
- Check debug.log for errors
- Take screenshot if UI issue

---

## Post-Test Actions

### If All Tests Pass:
1. ✅ Mark feature as complete
2. ✅ Commit changes to git
3. ✅ Proceed to Phase 2 main feature (marriages table)

### Git Commit (After Successful Testing):
```bash
cd "C:\Users\Amit\Local Sites\family-tree\app\public\wp-content\plugins\family-tree"
git add .
git commit -m "Add middle_name field for complete name tracking (v2.6.0)

- Add middle_name VARCHAR(100) column to members table
- Update add/edit forms with 3-column name layout
- Display full name with middle name on view pages
- Fix type declarations in MemberRepository and BaseController
- Update version to 2.6.0"
git push
```

---

## Known Issues / Limitations

### Current Implementation:
- ✅ Middle name is a single field (doesn't support multiple middle names separately)
- ✅ No validation for max length in frontend (handled by backend)
- ✅ No auto-capitalization (user must enter correctly)

### Future Enhancements:
- Add frontend validation for max 100 characters
- Add option for "middle initial only" checkbox
- Auto-capitalize middle name

---

**Test Status:** 🟡 Ready for Testing
**Estimated Time:** 15-20 minutes
**Priority:** High (blocking Phase 2 main feature)
