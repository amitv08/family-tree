# Changelog

All notable changes to the Family Tree WordPress Plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
