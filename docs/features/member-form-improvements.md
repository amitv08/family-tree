# Member Form Improvements

**Date:** October 24, 2025
**Version:** 2.4.1
**Status:** ✅ COMPLETE

---

## Changes Made

### 1. ✅ Gender Field: Dropdown → Radio Buttons

**Before:**
- Large dropdown select with 4 options
- Takes up unnecessary vertical space

**After:**
- Horizontal radio buttons (♂️ Male, ♀️ Female, ⚧️ Other)
- More compact and user-friendly
- Easier to select with one click

**Files Modified:**
- `templates/members/add-member.php` (lines 97-123)
- `templates/members/edit-member.php` (lines 114-141)

---

### 2. ✅ Parent 1 (Father): Filter by Gender

**Before:**
- Showed ALL members in dropdown
- Could accidentally select females as father

**After:**
- Only shows **Male** members in dropdown
- Prevents data entry errors
- Help text updated: "Only male members shown"

**Files Modified:**
- `templates/members/add-member.php` (lines 132-145)
- `templates/members/edit-member.php` (lines 150-163)

**Logic:**
```php
<?php if ($m->gender === 'Male'): ?>
    <option value="<?php echo intval($m->id); ?>">
        <?php echo esc_html($m->first_name . ' ' . $m->last_name); ?>
    </option>
<?php endif; ?>
```

---

### 3. ✅ Parent 2 (Mother): Dropdown → Text Field

**Before:**
- Dropdown to select from existing members
- Limited to members already in database

**After:**
- Simple text input field
- Allows entering mother's name even if not in system
- Placeholder: "e.g., Mary Smith"
- More flexible for partial family data

**Files Modified:**
- `templates/members/add-member.php` (lines 147-156)
- `templates/members/edit-member.php` (lines 165-175)
- `templates/members/view-member.php` (lines 162-180) - Display logic
- `includes/database.php` - Added `parent2_name` field support

**Database Changes:**
- New column: `parent2_name VARCHAR(200) DEFAULT NULL`
- Stored alongside existing `parent2_id` (for future migration)

**Display Logic (view-member.php):**
```php
// Priority: parent2_name > parent2_id > "Not recorded"
if (!empty($member->parent2_name)) {
    echo esc_html($member->parent2_name);  // Show text name
} elseif ($member->parent2_id) {
    // Show linked member with clickable link
} else {
    echo "Not recorded";
}
```

---

### 4. ✅ Biography Field: Fixed Null Warnings

**Before:**
- PHP Deprecated warning when biography is NULL:
  ```
  htmlspecialchars(): Passing null to parameter #1 ($string)
  of type string is deprecated
  ```

**After:**
- Proper null checking with `??` operator
- No warnings in edit or view pages

**Files Modified:**
- `templates/members/edit-member.php` (line 249)
  - Changed: `<?php echo esc_textarea($member->biography); ?>`
  - To: `<?php echo esc_textarea($member->biography ?? ''); ?>`

- `templates/members/view-member.php` (line 186)
  - Changed: `<?php if ($member->biography): ?>`
  - To: `<?php if (!empty($member->biography)): ?>`

---

## Database Schema Update

### New Column Added

```sql
ALTER TABLE wp_family_members
ADD COLUMN parent2_name VARCHAR(200) DEFAULT NULL;
```

**Purpose:** Store mother's name as free text

**Migration Strategy:**
- `parent2_id` field remains (backward compatible)
- `parent2_name` is new alternative field
- Both can coexist (future: migrate parent2_id to parent2_name)

---

## Testing Checklist

### Add Member Form
- [x] Gender displays as radio buttons
- [x] Father dropdown only shows males
- [x] Mother field is text input
- [x] Can submit form successfully
- [x] Data saves to database correctly

### Edit Member Form
- [x] Gender radio buttons show selected value
- [x] Father dropdown only shows males
- [x] Mother text field shows saved value
- [x] Biography doesn't show warnings
- [x] Can update successfully

### View Member Page
- [x] Gender displays correctly
- [x] Father shows as clickable link
- [x] Mother shows as text (if parent2_name) or link (if parent2_id)
- [x] Biography displays without warnings

---

## User Instructions

### To Apply Database Changes

**You must reactivate the plugin to add the new database column:**

1. Go to WordPress Admin → Plugins
2. **Deactivate** "Family Tree" plugin
3. **Activate** "Family Tree" plugin
4. The `parent2_name` column will be automatically added

### Using the New Features

**Adding a Member:**
1. **Gender**: Click the radio button (Male/Female/Other)
2. **Father**: Select from dropdown (only males shown)
3. **Mother**: Type the mother's name directly (e.g., "Mary Johnson")
4. Save!

**Why Text Input for Mother?**
- More flexible for incomplete family data
- Don't need to create member record for mother first
- Useful when you only have a name, not full details
- Can upgrade to full member record later

---

## Future Enhancements (Optional)

### 1. Auto-Complete for Mother Name
- Show suggestions as you type
- Based on existing `parent2_name` values
- Prevents duplicate spellings

### 2. "Upgrade to Full Member" Button
- On view page, show button if only parent2_name exists
- Click to create full member record from name
- Auto-link relationships

### 3. Gender-Based Parent Labels
- If member is Female, show "Mother" label instead of "Parent 1"
- Dynamic labeling based on gender

### 4. Parent 2 Options
- Radio button: "Select existing member" vs "Enter name"
- Gives users choice between linking or text entry

---

## Benefits

1. **Better UX** ✅
   - Gender selection faster and clearer
   - Father field prevents mistakes
   - Mother field more flexible

2. **Data Quality** ✅
   - Gender filtering ensures accuracy
   - Can capture partial information

3. **No Warnings** ✅
   - Fixed all PHP 8.1 deprecation warnings
   - Clean error logs

4. **Backward Compatible** ✅
   - Existing data unaffected
   - Can still use parent2_id if preferred

---

## Files Changed

**Templates:**
- `templates/members/add-member.php` - Gender radio, Father filter, Mother text
- `templates/members/edit-member.php` - Same changes + null fixes
- `templates/members/view-member.php` - Display logic + null fixes

**Database:**
- `includes/database.php` - Schema update, add_member(), update_member()

**Total Lines Changed:** ~120 lines across 4 files

---

**Author:** Claude (Anthropic)
**Requested by:** Amit Vengsarkar
**Date:** October 24, 2025
