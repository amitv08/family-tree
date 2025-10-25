# Phase 2 Planning - Advanced Genealogy Features

**Date Created:** October 24, 2025
**Current Version:** 2.5.0
**Target Version:** 2.6.0 or 3.0.0 (depending on scope)
**Status:** 📋 Planning

---

## ✅ Phase 1 Recap - What's Complete

### Features Implemented (v2.5.0):
1. ✅ **Adoption Status** - Simple checkbox, no biological parent tracking
2. ✅ **Nickname Field** - Common names and nicknames
3. ✅ **Maiden Name** - Birth surname before marriage
4. ✅ **Flexible Mother Input** - Text OR dropdown (toggle between existing member and manual entry)
5. ✅ **Form Reorganization** - Clan/Location/Surname in single row, Gender/Adoption in Clan section
6. ✅ **Father Gender Filter** - Only shows male members

### Current Limitations:
- ❌ Can only track ONE marriage date per person (single `marriage_date` field)
- ❌ Cannot track multiple wives/husbands over time
- ❌ Cannot link children to specific marriages (half-sibling relationships are implicit)
- ❌ Cannot track name changes over multiple marriages
- ❌ No middle name field
- ❌ No spouse relationship tracking (marriage is just a date, not a relationship)

---

## 🎯 Phase 2 Goals - Multiple Marriages & Advanced Relationships

### Primary Objectives:
1. **Support multiple marriages per person** (polygamy, remarriage after divorce/death)
2. **Track which children belong to which marriage** (explicit half-sibling relationships)
3. **Track complete name history for women** (multiple surname changes over multiple marriages)
4. **Link spouses properly** (marriage as a relationship between two people, not just a date)

---

## 📊 Proposed Database Schema Changes

### Option A: Marriages Table (Recommended)

**New Table: `wp_family_marriages`**
```sql
CREATE TABLE wp_family_marriages (
  id INT PRIMARY KEY AUTO_INCREMENT,

  -- Spouses
  husband_id INT NULL,                     -- FK to wp_family_members
  husband_name VARCHAR(200) NULL,          -- If husband not in system
  wife_id INT NULL,                        -- FK to wp_family_members
  wife_name VARCHAR(200) NULL,             -- If wife not in system

  -- Marriage details
  marriage_date DATE NULL,
  marriage_location VARCHAR(200) NULL,
  marriage_order TINYINT DEFAULT 1,        -- 1st marriage, 2nd marriage, etc. (for this person)

  -- Marriage status
  marriage_status ENUM('married', 'divorced', 'widowed', 'annulled') DEFAULT 'married',
  divorce_date DATE NULL,
  end_date DATE NULL,                      -- When marriage ended (divorce or death)
  end_reason VARCHAR(100) NULL,            -- 'divorce', 'death of spouse', etc.

  -- Notes
  notes TEXT NULL,

  -- Audit fields
  created_by INT NULL,
  updated_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  -- Foreign keys
  FOREIGN KEY (husband_id) REFERENCES wp_family_members(id) ON DELETE SET NULL,
  FOREIGN KEY (wife_id) REFERENCES wp_family_members(id) ON DELETE SET NULL
);
```

**Update: `wp_family_members` table**
```sql
ALTER TABLE wp_family_members
ADD COLUMN parent_marriage_id INT NULL COMMENT 'Which marriage produced this child',
ADD CONSTRAINT fk_parent_marriage
  FOREIGN KEY (parent_marriage_id) REFERENCES wp_family_marriages(id) ON DELETE SET NULL;
```

**Benefits:**
- ✅ Unlimited marriages per person
- ✅ Track both husband and wife (or just one if spouse not in system)
- ✅ Track divorce dates, widowed status
- ✅ Link children to specific marriages
- ✅ Marriage is stored ONCE (not duplicated on each spouse's record)
- ✅ Can query all marriages for a person
- ✅ Can query all children from a specific marriage

---

### Option B: Name History Table (Optional - For Multiple Surname Changes)

**New Table: `wp_family_name_history`**
```sql
CREATE TABLE wp_family_name_history (
  id INT PRIMARY KEY AUTO_INCREMENT,
  member_id INT NOT NULL,

  -- Name details
  name_type ENUM('birth', 'married', 'adopted', 'legal_change', 'other') DEFAULT 'birth',
  first_name VARCHAR(100),
  middle_name VARCHAR(100) NULL,
  last_name VARCHAR(100),

  -- Time period
  effective_date DATE NULL,                -- When this name became active
  end_date DATE NULL,                      -- When this name ended (NULL if current)
  is_current TINYINT(1) DEFAULT 0,         -- Is this the current name?

  -- Context
  related_marriage_id INT NULL,            -- FK to marriages table (if name change due to marriage)
  reason VARCHAR(100) NULL,                -- 'marriage', 'divorce', 'adoption', 'legal change'
  notes TEXT NULL,

  -- Audit
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (member_id) REFERENCES wp_family_members(id) ON DELETE CASCADE,
  FOREIGN KEY (related_marriage_id) REFERENCES wp_family_marriages(id) ON DELETE SET NULL
);
```

**Benefits:**
- ✅ Complete historical record of all names
- ✅ Can track: birth name, 1st married name, 2nd married name, etc.
- ✅ Can query "What was this person's name in 1995?"
- ✅ Can search by any historical name

**Complexity:**
- ⚠️ More complex to implement
- ⚠️ Need to keep `wp_family_members.first_name` and `last_name` in sync with "current" name
- ⚠️ UI needs to show name timeline

---

### Option C: Simple Middle Name Addition (Easy Quick Win)

```sql
ALTER TABLE wp_family_members
ADD COLUMN middle_name VARCHAR(100) NULL COMMENT 'Middle name or initial';
```

**Benefits:**
- ✅ Very simple
- ✅ Covers common need for full names
- ✅ No schema complexity

---

## 🎨 UI/UX Changes Needed

### 1. Member View Page - Marriages Section

**New section to display:**
```
📍 Marriages

Marriage 1: (1995-2005)
  Spouse: Jane Doe (link)
  Married: Jan 15, 1995
  Status: Divorced (2005)
  Children: 2 children
    - Sarah Smith (b. 1996)
    - David Smith (b. 1998)

Marriage 2: (2007-present)
  Spouse: Mary Johnson (link)
  Married: Jun 20, 2007
  Status: Married
  Children: 1 child
    - Emily Smith (b. 2009)
```

### 2. Add/Edit Member Form - Marriage Management

**Option A: Modal/Popup Approach**
- Remove single `marriage_date` field
- Add "Marriages" section with "Add Marriage" button
- Clicking opens modal to add marriage details
- List existing marriages with edit/delete options

**Option B: Inline Expandable Sections**
- Show marriages as expandable sections
- "Add Marriage" adds new section inline
- Each section has: spouse, date, status, children

### 3. Link Children to Marriages

**When adding a child:**
```
Father: [John Smith ▼]
Mother: [Select... ▼] or [Enter name manually]

Select Marriage:
  ○ Marriage to Jane Doe (1995-2005)
  ○ Marriage to Mary Johnson (2007-present)
  ○ Not from a marriage / Unknown
```

Auto-suggest marriage based on mother if she's in system.

### 4. Name History Timeline (Optional)

**For women with multiple marriages:**
```
Name History:
  1980-1995: Mary Johnson (birth name)
  1995-2005: Mary Smith (married John Smith)
  2007-present: Mary Wilson (married Bob Wilson)
```

---

## 🚦 Implementation Approach

### Stage 1: Marriages Table (Core Functionality)

**Files to Modify:**
1. `includes/database.php`
   - Add `wp_family_marriages` table to schema
   - Add `parent_marriage_id` to members table
   - Create `add_marriage()`, `update_marriage()`, `delete_marriage()` methods
   - Create `get_marriages_for_member($member_id)` method

2. `templates/members/view-member.php`
   - Add "Marriages" section displaying all marriages
   - Show children grouped by marriage

3. `templates/members/add-member.php` & `edit-member.php`
   - Remove single `marriage_date` field
   - Add "Marriages" management section
   - Add "Add Marriage" button with modal/inline form
   - Add `parent_marriage_id` dropdown when selecting parents

4. Create new templates:
   - `templates/marriages/add-marriage-modal.php` (optional)
   - Or handle via AJAX

5. Create new AJAX handlers:
   - `ajax_add_marriage()`
   - `ajax_update_marriage()`
   - `ajax_delete_marriage()`
   - `ajax_get_marriages()`

6. JavaScript:
   - `assets/js/marriages.js` - Handle marriage CRUD operations

**Migration Strategy:**
- Existing `marriage_date` values → Create initial marriage records
- Migration function: `migrate_existing_marriages()`
- Run on plugin activation if `wp_family_marriages` table is new

### Stage 2: Name History (Optional Advanced Feature)

**Only if needed for multiple name changes.**

### Stage 3: Middle Name (Quick Addition)

**Can be done independently at any time.**

---

## 📝 Data Migration Plan

### Existing Data Preservation:

**Problem:** Current system has `marriage_date` on member record.
**Solution:** Auto-migrate to marriages table.

```php
function migrate_existing_marriages() {
    global $wpdb;
    $members = $wpdb->get_results("SELECT id, marriage_date FROM wp_family_members WHERE marriage_date IS NOT NULL");

    foreach ($members as $member) {
        // Create marriage record
        $wpdb->insert('wp_family_marriages', [
            'husband_id' => $member->gender === 'Male' ? $member->id : null,
            'wife_id' => $member->gender === 'Female' ? $member->id : null,
            'marriage_date' => $member->marriage_date,
            'marriage_order' => 1,
            'marriage_status' => 'married'
        ]);

        // Don't delete old field yet - keep for backward compatibility
    }
}
```

---

## 🤔 Open Questions & Decisions Needed

### 1. **Gender-Neutral Marriage Support?**
Current design assumes husband/wife.
**Options:**
- A) Keep `husband_id`/`wife_id` (simpler, traditional)
- B) Use `spouse1_id`/`spouse2_id` (gender-neutral, more complex)

**Recommendation:** Keep A for now (user confirmed no same-sex couples needed).

### 2. **Keep Old `marriage_date` Field?**
**Options:**
- A) Keep it for backward compatibility (safe)
- B) Remove it after migration (cleaner)

**Recommendation:** Keep it for now, deprecate in future version.

### 3. **Children Without Marriage?**
How to handle children born outside marriage?

**Solution:** `parent_marriage_id` is nullable. NULL = not from a recorded marriage.

### 4. **Concurrent Marriages?**
Should system allow overlapping marriage dates (polygamy)?

**Current Answer from User:** Yes, need to support multiple wives.

### 5. **Marriage Order Auto-Calculation?**
Should `marriage_order` be auto-calculated based on date?

**Recommendation:** Auto-calculate but allow manual override.

---

## 🎯 Success Criteria

### Phase 2 Complete When:
- ✅ Can add multiple marriages per person
- ✅ Can track which children belong to which marriage
- ✅ Half-siblings are explicitly linked to different marriages
- ✅ Marriage is stored once (not duplicated)
- ✅ Can track divorce dates and widowed status
- ✅ View page shows marriage history with children grouped
- ✅ Existing data migrated without loss
- ✅ Backward compatible (old code still works)

---

## 📊 Estimated Scope

### Complexity: **Medium to High**

**Estimated Effort:**
- Schema design & migration: 2-3 hours
- Backend methods (CRUD for marriages): 3-4 hours
- Frontend UI (forms, view page): 4-6 hours
- JavaScript/AJAX: 2-3 hours
- Testing & debugging: 3-4 hours
- Documentation: 1-2 hours

**Total: ~15-22 hours**

### Can Be Broken Into Smaller Steps:
1. ✅ Add marriages table (schema only)
2. ✅ Add backend CRUD methods
3. ✅ Add view page display
4. ✅ Add marriage management UI
5. ✅ Link children to marriages
6. ✅ Migration & testing

---

## 🚀 Alternative: Minimal Phase 2

### If Full Implementation Too Complex:

**Quick Additions Without Marriages Table:**
1. ✅ Add `middle_name` field (simple)
2. ✅ Add `marriage_date_2`, `marriage_date_3` fields (hacky but works)
3. ✅ Add `spouse_1_name`, `spouse_2_name` text fields

**Pros:** Very quick to implement
**Cons:** Not scalable, not normalized, hard to query

---

## 📚 Related User Requirements

### From Earlier Discussion:

**User Said:**
1. ✅ "A person can have more than one wife and hence different marriage dates"
2. ✅ "Once father's name is selected mother's name can either be selected from dropdown or can be accepted as input"
3. ✅ "A female child in a clan has 2 sets of names i.e name before marriage and after marriage"

**What This Means for Phase 2:**
1. → Need marriages table (Requirement #1)
2. → Already solved in Phase 1 (Requirement #2)
3. → Partially solved with `maiden_name`, full solution needs name history table (Requirement #3)

---

## 📋 Next Steps When Resuming

### When You Come Back:

1. **Review this document**
2. **Decide on approach:**
   - Full marriages table implementation?
   - Simple middle name only?
   - Phased approach (marriages first, name history later)?

3. **Confirm requirements:**
   - Do you need full marriage tracking?
   - How important is name history?
   - Any new requirements?

4. **Start implementation:**
   - Begin with Stage 1 (Marriages Table)
   - Or start with quick win (Middle Name)

---

## 🔗 References

- **Phase 1 Summary**: See `CHANGELOG.md` v2.5.0
- **Current Schema**: See `includes/database.php`
- **User Discussion**: Documented in this file

---

**Status:** 📋 Ready for Phase 2 Implementation
**Last Updated:** October 24, 2025
**Next Review:** When resuming Phase 2 work
