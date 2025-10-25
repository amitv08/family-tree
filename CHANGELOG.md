# Changelog

All notable changes to the Family Tree WordPress Plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [3.0.0] - 2025-10-25

### Added - Phase 2: Multiple Marriages Support 🎉

**Major Feature Release**: Complete multiple marriages tracking with full relationship management.

#### Database Schema

- **New Table: `wp_family_marriages`**
  - Track unlimited marriages per person (polygamy, remarriage support)
  - Fields: `husband_id`, `wife_id`, `husband_name`, `wife_name`
  - Marriage details: `marriage_date`, `marriage_location`, `marriage_order`
  - Status tracking: `marriage_status` (married/divorced/widowed/annulled)
  - End tracking: `divorce_date`, `end_date`, `end_reason`, `notes`
  - Audit fields: `created_by`, `updated_by`, `created_at`, `updated_at`
  - Foreign keys to `wp_family_members` for data integrity

- **New Column: `parent_marriage_id` in `wp_family_members`**
  - Links children to specific marriages
  - Enables tracking half-siblings from different marriages
  - Foreign key to `wp_family_marriages` table
  - Nullable (allows children without marriage link)

#### Backend Architecture (MVC)

- **MarriageRepository** (`includes/Repositories/MarriageRepository.php`)
  - `add()` - Create new marriage
  - `update()` - Update marriage details
  - `delete()` - Remove marriage
  - `get_marriages_for_member()` - Get all marriages for a person
  - `get_children_for_marriage()` - Get children from specific marriage
  - `get_all_marriages()` - List all marriages with spouse details

- **MarriageController** (`includes/Controllers/MarriageController.php`)
  - `add()` - AJAX handler for adding marriages
  - `update()` - AJAX handler for updating marriages
  - `delete()` - AJAX handler for deleting marriages (prevents deletion if children exist)
  - `get_details()` - Fetch single marriage details
  - `get_marriages_for_member()` - Fetch all marriages for member
  - `get_children_for_marriage()` - Fetch children for marriage

- **Config Constants** (6 new AJAX actions)
  - `AJAX_ADD_MARRIAGE`
  - `AJAX_UPDATE_MARRIAGE`
  - `AJAX_DELETE_MARRIAGE`
  - `AJAX_GET_MARRIAGE_DETAILS`
  - `AJAX_GET_MARRIAGES_FOR_MEMBER`
  - `AJAX_GET_CHILDREN_FOR_MARRIAGE`

#### Frontend

- **Enhanced View Member Page** (`templates/members/view-member.php`)
  - New "Marriages" section displaying all marriages
  - Each marriage shows:
    - Spouse name (clickable if in system)
    - Marriage date and location
    - Status badge (married/divorced/widowed)
    - Divorce/end date if applicable
    - Children grouped by marriage with birth years
    - Notes if present
  - Add/Edit/Delete buttons (permission-gated)
  - Empty state with "Add Marriage" button

- **JavaScript** (`assets/js/marriages.js`)
  - Add marriage via prompts (temporary - can be enhanced to modal)
  - Edit marriage with pre-filled data
  - Delete marriage with confirmation
  - Auto-determines husband/wife based on member gender
  - Full AJAX integration with error handling

#### Data Migration

- **Automatic Migration**: `migrate_existing_marriages()`
  - Converts old single `marriage_date` to marriages table
  - Runs automatically on plugin activation
  - One-time migration with safety checks
  - Preserves all existing data
  - Creates one marriage record per person with marriage_date set
  - Determines husband/wife based on gender

#### Legacy Support

- **Backward Compatible Methods** in `FamilyTreeDatabase`:
  - All existing methods remain functional
  - New static methods for marriages CRUD
  - Old `marriage_date` field kept for now (will be deprecated in future)

### Changed

- **View Member Page**: Removed single marriage date from "Life Events" section
- **Marriage Display**: Now shows comprehensive marriage history instead of single date
- **Plugin Version**: Bumped to 3.0.0 (major release)
- **Nonce Security**: Added `AJAX_DELETE_MARRIAGE` to sensitive operations list

### Technical Details

#### Files Modified

- `includes/database.php` - Added marriages table schema, CRUD methods, migration
- `includes/Config.php` - Added 6 marriage AJAX action constants
- `includes/Plugin.php` - Registered marriage controller and AJAX hooks
- `includes/Repositories/MarriageRepository.php` - **NEW** Database operations layer
- `includes/Controllers/MarriageController.php` - **NEW** AJAX request handlers
- `assets/js/marriages.js` - **NEW** Frontend JavaScript
- `templates/members/view-member.php` - Added marriages section, removed old marriage date
- `family-tree.php` - Version bump to 3.0.0

#### Database Changes

```sql
-- New marriages table
CREATE TABLE wp_family_marriages (
  id MEDIUMINT(9) PRIMARY KEY AUTO_INCREMENT,
  husband_id MEDIUMINT(9) NULL,
  husband_name VARCHAR(200) NULL,
  wife_id MEDIUMINT(9) NULL,
  wife_name VARCHAR(200) NULL,
  marriage_date DATE NULL,
  marriage_location VARCHAR(200) NULL,
  marriage_order TINYINT DEFAULT 1,
  marriage_status VARCHAR(20) DEFAULT 'married',
  divorce_date DATE NULL,
  end_date DATE NULL,
  end_reason VARCHAR(100) NULL,
  notes TEXT NULL,
  created_by MEDIUMINT(9) NULL,
  updated_by MEDIUMINT(9) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (husband_id) REFERENCES wp_family_members(id),
  FOREIGN KEY (wife_id) REFERENCES wp_family_members(id)
);

-- New column in members table
ALTER TABLE wp_family_members
ADD COLUMN parent_marriage_id MEDIUMINT(9) NULL,
ADD CONSTRAINT fk_members_parent_marriage
  FOREIGN KEY (parent_marriage_id) REFERENCES wp_family_marriages(id);
```

### Migration Instructions

**⚠️ IMPORTANT: Backup database before upgrading to 3.0.0**

1. **Backup**: Export database via phpMyAdmin or command line
2. **Deactivate**: Go to WordPress Admin → Plugins → Deactivate "Family Tree"
3. **Activate**: Click "Activate" on the Family Tree plugin
4. **Verify**: Check `wp-content/debug.log` for migration success messages
5. **Test**: Navigate to a member's view page and verify marriages display

**What Happens During Migration:**
- `wp_family_marriages` table is created
- `parent_marriage_id` column added to `wp_family_members`
- All existing `marriage_date` values converted to marriage records
- Each person with a marriage_date gets one marriage record
- Husband/wife determined by gender field
- Original `marriage_date` field kept for backward compatibility (will be removed in future)

### Known Limitations

- **Add Marriage UI**: Currently uses browser prompts (temporary solution)
  - Will be enhanced to modal form in future update
  - All core functionality works correctly
- **Old marriage_date Field**: Still exists in database for backward compatibility
  - Will be deprecated and removed in v4.0.0
  - Not displayed in UI anymore

### Upgrade Notes

**From 2.6.0 to 3.0.0:**
- No breaking changes for end users
- All existing data preserved
- Marriage information enhanced, not lost
- UI automatically shows new marriages section

**For Developers:**
- New MVC classes available: `MarriageRepository`, `MarriageController`
- New database methods in `FamilyTreeDatabase` for marriage CRUD
- JavaScript API: Use `marriages.js` for frontend interactions
- AJAX endpoints registered and ready to use

### Future Enhancements

Planned for future versions:
- Modal-based forms for add/edit marriage (replacing prompts)
- Link existing members as spouses (dropdown instead of text input)
- Marriage timeline visualization
- Surname change tracking for women across multiple marriages
- Marriage certificate upload
- Remove deprecated `marriage_date` column (v4.0.0)

---

## [2.6.0] - 2025-10-24

### Added - Phase 2 Quick Win

- **Middle Name Field**: New `middle_name` field for complete name tracking
  - Optional field stored between first name and last name
  - Displayed as "First Middle Last" throughout the system
  - Added to add/edit member forms in 3-column layout
  - Automatically included in full name display on view pages
  - Database column: `middle_name VARCHAR(100) DEFAULT NULL`

### Changed

- **Name Display**: Member names now shown as "First Middle Last" when middle name is present
- **Form Layout**: First name, middle name, and last name fields now in single 3-column row for better UX
- **Type Safety**: Fixed missing type declarations in `MemberRepository::add()` and `BaseController::get_post()`
- **Router**: Added null coalescing operator for `$_SERVER['REQUEST_URI']` to prevent undefined key warnings

### Technical Details

- Updated `FamilyTreeDatabase::add_member()` to handle middle_name
- Updated `FamilyTreeDatabase::update_member()` to handle middle_name
- Updated `MemberRepository::add()` to handle middle_name
- Updated `MemberRepository::update()` to handle middle_name
- Updated `templates/members/add-member.php` with middle_name input
- Updated `templates/members/edit-member.php` with middle_name input
- Updated `templates/members/view-member.php` to display middle_name in full name
- Schema migration via `apply_schema_updates()` adds column automatically on plugin reactivation

### Migration Notes

**To apply these changes:**
1. Deactivate the plugin in WordPress Admin → Plugins
2. Activate the plugin again
3. The `middle_name` column will be automatically added to the database
4. Existing members will have NULL middle_name (optional field)

---

## [2.5.0] - 2025-10-24

### Added - Phase 1 Genealogy Features

- **Adoption Status**: New `is_adopted` checkbox field to mark adopted family members
  - Displayed as badge on view member page
  - No biological parent tracking (as per requirements)

- **Nickname Field**: New `nickname` field for common names or nicknames
  - Displayed in parentheses after full name: "Robert (Bob) Smith"
  - Optional field for all members

- **Maiden Name Field**: New `maiden_name` field to track birth surname before marriage
  - For women: stores surname at birth, before marriage
  - Current `last_name` field stores married/current surname
  - Displayed as "Mary Smith (née Johnson)" on view page

- **Mother Input Toggle**: Flexible mother selection in Family Relationships section
  - Radio button toggle between:
    - "Enter name manually" (text input for `parent2_name`) - DEFAULT
    - "Select existing member" (dropdown filtered to female members for `parent2_id`)
  - Allows tracking mothers who aren't in the system yet
  - Enables distinguishing half-siblings by different mothers

### Changed - Form Reorganization

- **Clan Information Section Layout**:
  - Clan, Location, and Surname now displayed in single row (3 columns)
  - More compact and efficient use of space

- **Field Order Restructure**:
  - Gender radio buttons moved from Personal Information to Clan Information section
  - Adoption checkbox moved from Personal Information to Clan Information section
  - Both now appear immediately after clan/location/surname selection
  - Improves logical flow: clan details → personal attributes → family relationships

- **Label Clarifications**:
  - "Maiden Name" → "Maiden Name (Birth Surname)"
  - Help text updated: "For women: surname at birth, before marriage"

### Removed

- **Birth Order Fields** (removed based on user feedback):
  - Removed `birth_order` field (was: order among siblings)
  - Removed `is_multiple_birth` field (was: twin/triplet indicator)
  - Simplified form to focus on core genealogy needs

### Database Schema

**New columns added to `wp_family_members`:**
- `is_adopted` TINYINT(1) DEFAULT 0 - Adoption status checkbox
- `maiden_name` VARCHAR(100) DEFAULT NULL - Birth surname before marriage
- `nickname` VARCHAR(100) DEFAULT NULL - Common name or nickname

**Note**: Birth order fields were initially added but removed in final version per user requirements.

### Technical Details

- Updated `add_member()` method to handle new fields
- Updated `update_member()` method to handle new fields
- Updated `apply_schema_updates()` to add new columns on plugin reactivation
- JavaScript toggle function for mother input type switching
- Smart field detection in edit form (shows text or dropdown based on existing data)

### UI/UX Improvements

- Clan/Location/Surname in compact 3-column layout
- Mother input radio toggle for flexible data entry
- Logical grouping: Clan details → Gender/Adoption → Personal details
- Clearer field labels and help text
- Adopted and nickname badges on view page

### Migration Notes

**To apply these changes:**
1. Deactivate the plugin in WordPress Admin → Plugins
2. Activate the plugin again
3. The 3 new database columns will be automatically added
4. Existing data is preserved - new fields are optional

### Backward Compatibility

- ✅ All existing member data preserved
- ✅ Optional fields - no required data entry
- ✅ Both `parent2_id` and `parent2_name` supported (user can choose)
- ✅ No breaking changes to existing functionality

---

## [2.4.1] - 2025-10-24

### Added
- New `parent2_name` field to store mother's name as free text
- Smart update strategy for clan locations and surnames to preserve member references

### Changed
- **Gender field**: Converted from dropdown to horizontal radio buttons (♂️ Male, ♀️ Female, ⚧️ Other)
- **Father field (Parent 1)**: Now filtered to show only Male members
- **Mother field (Parent 2)**: Converted from member dropdown to text input field
- Member form now more user-friendly and prevents data entry errors

### Fixed
- **Critical**: Fixed clan update behavior that was breaking member references when editing clans
- **Critical**: Fixed all PHP 8.1 null deprecation warnings in member edit form
  - Fixed biography, photo_url, parent2_name, birth_date, death_date, marriage_date
  - Fixed address, city, state, country, postal_code fields
- **Database**: Fixed incorrect DATE value errors in clan view statistics
  - Removed invalid empty string comparisons for DATE columns
  - Living members query now uses `death_date IS NULL` only
  - Deceased members query now uses `death_date IS NOT NULL` only
- **UI**: Fixed clan edit page not displaying location and surname details
- **Data Type**: Fixed stdClass to string conversion errors in templates

### Database Schema
- Added `parent2_name VARCHAR(200) DEFAULT NULL` column to `wp_family_members` table
- Preserves backward compatibility with existing `parent2_id` field

### Documentation
- Added MEMBER-FORM-IMPROVEMENTS.md with detailed change documentation
- Added CLAN-UPDATE-FIX.md explaining smart update algorithm
- Reorganized documentation into structured docs/ folder

---

## [2.4.0] - 2025-10-24

### Major Refactoring - MVC Architecture

This version represents a complete architectural overhaul from monolithic to modern MVC pattern while maintaining 100% backward compatibility.

### Added
- **PSR-4 Autoloading**: `includes/Autoloader.php` for automatic class loading
- **Configuration Class**: `includes/Config.php` - centralized constants for tables, capabilities, actions, routes
- **Main Plugin Class**: `includes/Plugin.php` - slim, focused plugin bootstrap (150 lines)
- **Router**: `includes/Router.php` - centralized route handling with middleware support
- **Controllers** (MVC):
  - `BaseController.php` - Shared controller logic with AJAX helpers
  - `ClanController.php` - 5 clan AJAX handlers (add, update, delete, get, get_details, get_all_simple)
  - `MemberController.php` - 6 member AJAX handlers (add, update, delete, soft_delete, restore, search)
  - `UserController.php` - 3 user management handlers
- **Repositories** (Data Layer):
  - `BaseRepository.php` - Common CRUD operations
  - `MemberRepository.php` - Member database operations
  - `ClanRepository.php` - Clan database operations
- **Validators**:
  - `MemberValidator.php` - Extracted validation logic from database layer
- **Namespace**: All new code uses `FamilyTree\` namespace

### Changed
- **Main plugin file** (`family-tree.php`): Reduced from 605 lines to 48 lines
  - Now only bootstraps autoloader and creates Plugin instance
  - All business logic moved to appropriate classes
- **Architecture**: Migrated from God Object anti-pattern to proper MVC
- **Code Organization**:
  - Before: 1 monolithic class with 605 lines
  - After: 12 focused classes averaging 150 lines each
- **Coupling**: Reduced tight coupling through dependency injection and repository pattern
- **Type Safety**: All new classes use PHP 7.4+ type hints

### Fixed (Pre-Refactoring Bug Fixes)
- **Critical**: `Undefined property: stdClass::$locations` in browse-clans.php
  - Root cause: `get_all_clans()` could return false/null instead of arrays
  - Fix: Added proper `isset()` and `is_array()` checks throughout
- **Critical**: `htmlspecialchars(): Passing null to parameter` (PHP 8.1)
  - Fixed in multiple clan templates with proper `!empty()` checks
- **Critical**: `Object of class stdClass could not be converted to string`
  - Fixed in view-clan.php by handling both object and string formats
  - Added type checking for locations/surnames display

### Database Layer Improvements
- `FamilyTreeClanDatabase::get_all_clans()` - Now ensures arrays are always returned
- `FamilyTreeClanDatabase::get_clan()` - Guaranteed array properties (never false/null)
- Added defensive null checks in all database methods

### Performance
- Zero performance impact expected (lazy autoloading, same query count)
- Similar memory footprint despite more classes (due to lazy loading)

### Backward Compatibility
- ✅ 100% backward compatible - no breaking changes
- ✅ All existing templates work unchanged
- ✅ All AJAX endpoints work unchanged
- ✅ Database schema unchanged (for this version)
- ✅ Legacy classes (`FamilyTreeDatabase`, `FamilyTreeClanDatabase`) still fully supported
- ✅ All routes and permissions unchanged
- ✅ User interface unchanged

### Metrics
- Main plugin file: 605 → 48 lines (-92%)
- Total codebase: 605 → 1,018 lines (+68%, but organized into 12 focused classes)
- Controllers: 0 → 400 lines
- Repositories: 0 → 350 lines
- Router: 0 → 120 lines
- Config: 0 → 100 lines

### Developer Experience
- **Before**: Everything in one 605-line class, hard to maintain
- **After**: 12 focused classes with single responsibilities
- **Testability**: Easy to add PHPUnit tests with new architecture
- **Extensibility**: Can now extend via inheritance or dependency injection

### Documentation
- Created REFACTORING-SUMMARY.md with detailed architecture documentation
- Updated CLAUDE.md with new architecture guidelines
- Created backup of original file (family-tree.php.backup)

### Future Enhancements (Now Possible)
1. Service Layer for business logic
2. DTOs/Models for type-safe data transfer
3. Dependency Injection Container
4. Event System for plugin extensibility
5. Caching Layer in repositories
6. REST API endpoints
7. Full PHPUnit test coverage

### Rollback Plan
If issues occur, restore from `family-tree.php.backup`

---

## [2.3.2] - 2025-10-23

### Added
- Soft delete functionality for members (`is_deleted` flag)
- Restore member functionality
- Member management with clan selection and dependent dropdowns

### Features
- Browse members page with grid view
- Add/Edit member forms with clan integration
- View member page with detailed information
- Clan locations and surnames populate dynamically based on clan selection

### Database
- `wp_family_members` table with audit fields
- `wp_family_clans` table
- `wp_clan_locations` table
- `wp_clan_surnames` table
- Soft delete via `is_deleted` flag

---

## [2.3.0] - 2025-10-22

### Added
- Clans module with full CRUD operations
- Multiple locations per clan
- Multiple surnames per clan
- Clan ↔ Member integration

### Changed
- Updated member forms to include clan selection
- Dynamic dropdowns for clan locations and surnames

---

## [2.0.0] - 2025-10-15

### Added
- Initial release with core member management
- D3.js tree visualization
- User roles and permissions (Super Admin, Admin, Editor, Viewer)
- WordPress custom routing system
- AJAX-based CRUD operations

### Features
- Add, edit, browse, and view family members
- Parent-child relationships
- Interactive tree view with zoom and pan
- Color-coded clans in tree view
- Role-based access control

---

## Legend

- **Added**: New features
- **Changed**: Changes to existing functionality
- **Deprecated**: Soon-to-be removed features
- **Removed**: Removed features
- **Fixed**: Bug fixes
- **Security**: Security fixes
