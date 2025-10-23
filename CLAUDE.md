# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a **Family Tree WordPress Plugin** - a complete genealogy and clan management system for WordPress. The plugin enables creating family trees, managing members and clans, and visualizing relationships with an interactive D3.js tree view.

**Current Version:** 2.3.2
**Author:** Amit Vengsarkar

## Development Environment

- **Platform:** WordPress 6.x+
- **PHP:** 8.x+
- **Database:** MySQL (via WordPress $wpdb)
- **Local Development:** LocalWP (Local by Flywheel)
  - DB Name: `local`
  - DB User: `root`
  - DB Password: `root`
  - DB Host: `localhost`

### Debug Settings
The site runs with debug mode enabled in `wp-config.php`:
- `WP_DEBUG = true`
- `WP_DEBUG_LOG = true` (logs to `wp-content/debug.log`)
- `WP_DEBUG_DISPLAY = true`
- `SCRIPT_DEBUG = true`

## Plugin Architecture

### Core Structure
```
family-tree/
├── family-tree.php          # Main plugin file, bootstraps everything
├── includes/                # PHP business logic
│   ├── database.php         # Members database operations
│   ├── clans-database.php   # Clans database operations
│   ├── roles.php            # WordPress role/capability setup
│   └── shortcodes.php       # WordPress shortcode handlers
├── templates/               # PHP templates for frontend
│   ├── components/          # Reusable UI components (header, page-layout)
│   ├── members/             # CRUD pages for members
│   ├── clans/               # CRUD pages for clans
│   ├── dashboard.php        # Main family dashboard
│   ├── login.php            # Custom login page
│   └── tree-view.php        # D3.js tree visualization
└── assets/
    ├── css/                 # Modular CSS (variables, base, layout, forms, components, responsive)
    └── js/                  # JavaScript modules
        ├── script.js        # General utilities
        ├── clans.js         # Clan CRUD logic
        ├── members.js       # Member CRUD logic
        ├── family-tree.js   # Legacy/utility functions
        └── tree.js          # D3.js tree rendering

```

### Database Schema

**Main Tables** (all prefixed with `wp_`):
- `family_members` - Core member data with parent relationships
  - Includes soft delete via `is_deleted` flag
  - Audit fields: `created_by`, `updated_by`, `created_at`, `updated_at`, `user_id`
  - Address fields: `address`, `city`, `state`, `country`, `postal_code`
  - Links to: `clan_id`, `clan_location_id`, `clan_surname_id`, `parent1_id`, `parent2_id`

- `family_clans` - Clan/family group definitions
  - Fields: `clan_name`, `description`, `origin_year`
  - Audit fields: `created_by`, `updated_by`, `created_at`, `updated_at`

- `clan_locations` - Multiple locations per clan
  - Fields: `clan_id`, `location_name`, `is_primary`

- `clan_surnames` - Multiple surnames per clan
  - Fields: `clan_id`, `last_name`, `is_primary`

### Routing System

The plugin uses **custom URL routing** (not WordPress pages/posts):
- Routing handled in `FamilyTreePlugin::handle_routes()` via `template_redirect` action
- Routes map URI patterns to template files directly
- Homepage automatically redirects to `/family-dashboard`

**Available Routes:**
- `/family-dashboard` → Dashboard (members grid view)
- `/family-login` → Custom login page
- `/add-member`, `/edit-member?id=X`, `/browse-members`, `/view-member?id=X`
- `/add-clan`, `/edit-clan?id=X`, `/browse-clans`, `/view-clan?id=X`
- `/family-tree` → D3.js tree visualization

### User Roles & Permissions

The plugin creates 4 custom WordPress roles:

1. **family_super_admin** - Full access (manage_clans, manage_family, manage_family_users, edit_family_members, delete_family_members)
2. **family_admin** - Can manage members and users (not create clans)
3. **family_editor** - Can edit members only
4. **family_viewer** - Read-only access

Role setup happens in `includes/roles.php` → `FamilyTreeRoles::setup_roles()`

### AJAX Architecture

All AJAX calls are handled through WordPress admin-ajax.php:
- **Security:** AJAX actions use `wp_nonce` verification (`family_tree_nonce`)
- **JavaScript:** `family_tree.ajax_url` and `family_tree.nonce` localized via `wp_localize_script()`
- **Actions registered in main plugin class:**
  - Clans: `add_clan`, `update_clan`, `delete_clan`, `get_clan`, `get_clan_details`, `get_all_clans_simple`
  - Members: `add_family_member`, `update_family_member`, `delete_family_member`, `soft_delete_member`, `restore_member`, `search_members_select2`

### Frontend Dependencies

**External Libraries (loaded via CDN):**
- jQuery (WordPress bundled)
- Select2 4.0.13 - For searchable dropdowns
- D3.js - For tree visualization (loaded in tree-view.php)

**CSS Organization:**
The `assets/css/` folder uses a modular approach:
- `variables.css` - CSS custom properties (colors, spacing, typography)
- `base.css` - Reset and base styles
- `layout.css` - Grid, containers, page structure
- `forms.css` - Form controls, inputs, buttons
- `components.css` - Cards, modals, tables, badges
- `responsive.css` - Media queries
- `style.css` - Main import file

### Key Features & Behaviors

**Clan ↔ Member Integration:**
- When a clan is selected, dependent dropdowns for clan locations and clan surnames populate dynamically
- Members can select primary or secondary locations/surnames per clan
- AJAX endpoint `get_clan_details` returns locations and surnames for a given clan_id

**Soft Delete Pattern:**
- Members have `is_deleted` flag for soft deletion
- `soft_delete_member` AJAX action sets flag
- `restore_member` AJAX action clears flag
- Browse pages filter out deleted members by default

**Parent Relationships:**
- Each member has `parent1_id` and `parent2_id` (nullable)
- Tree visualization uses these links to build hierarchical D3 tree
- No gender enforcement on parent slots (flexible for diverse family structures)

**Validation:**
- `FamilyTreeDatabase::validate_member_data()` validates member data before save
- Errors returned to frontend via `wp_send_json_error()`

## Common Development Commands

### Running the WordPress Site
```bash
# Start LocalWP application (GUI-based)
# Site runs at: http://family-tree.local
# Admin: http://family-tree.local/wp-admin
```

### Database Access
```bash
# Via LocalWP GUI: Database tab → Adminer/phpMyAdmin
# Or connect directly:
mysql -u root -proot local
```

### Viewing Logs
```bash
# WordPress debug log
tail -f wp-content/debug.log

# PHP error log (LocalWP)
tail -f /path/to/LocalWP/logs/php/error.log
```

### Plugin Development Workflow
```bash
# Edit plugin files directly in:
cd wp-content/plugins/family-tree

# After PHP changes, refresh the page
# After CSS/JS changes, hard refresh (Ctrl+Shift+R) or bump version number in enqueue_scripts()

# To test activation hook:
# Deactivate and reactivate plugin via wp-admin/plugins.php
```

### Git Commands
```bash
cd wp-content/plugins/family-tree
git status
git add .
git commit -m "Description of changes"
git push
```

## Testing

### Manual Testing Workflow
1. **Members CRUD**: Test add/edit/browse/view pages
2. **Clans CRUD**: Test add/edit/browse/view pages
3. **Dependent Dropdowns**: Select clan → verify location/surname dropdowns populate
4. **Soft Delete**: Delete a member → verify it disappears from browse page → restore it
5. **Tree View**: Add parent relationships → visit `/family-tree` → verify tree renders
6. **Permissions**: Test with different user roles (viewer, editor, admin, super_admin)
7. **AJAX**: Open browser console → verify no JS errors → check Network tab for AJAX responses

### Common Testing URLs
- Main Dashboard: `http://family-tree.local/family-dashboard`
- Add Member: `http://family-tree.local/add-member`
- Browse Members: `http://family-tree.local/browse-members`
- Tree View: `http://family-tree.local/family-tree`
- WordPress Admin: `http://family-tree.local/wp-admin`

## Important Implementation Notes

### Database Operations
- Always use `$wpdb->prepare()` for queries with user input
- Use `FamilyTreeDatabase` static methods for members operations
- Use `FamilyTreeClanDatabase` static methods for clans operations
- Schema updates are managed via `apply_schema_updates()` in database.php

### Activation & Migration
- `FamilyTreePlugin::activate()` runs on plugin activation
- Creates/updates all tables via `setup_tables()` and `apply_schema_updates()`
- Adds default roles and grants super admin to site admin
- Always call `flush_rewrite_rules()` after route changes

### Adding New Routes
1. Add route pattern in `handle_routes()` method
2. Create corresponding template in `templates/` folder
3. Reactivate plugin to flush rewrite rules

### Adding New AJAX Actions
1. Create public method in `FamilyTreePlugin` class (e.g., `ajax_my_action()`)
2. Register in constructor: `add_action('wp_ajax_my_action', [$this, 'ajax_my_action'])`
3. Use `check_ajax_referer('family_tree_nonce', 'nonce')` for security
4. Return JSON via `wp_send_json_success()` or `wp_send_json_error()`
5. Call from JavaScript via `family_tree.ajax_url` with `nonce: family_tree.nonce`

### WordPress Coding Standards
- Use WordPress functions over native PHP where available (e.g., `wp_redirect()`, `esc_html()`, `sanitize_text_field()`)
- Escape all output: `esc_html()`, `esc_attr()`, `esc_url()`
- Sanitize all input: `sanitize_text_field()`, `intval()`, etc.
- Use `ABSPATH` check at top of PHP files: `if (!defined('ABSPATH')) exit;`

## Known Issues & Technical Debt

- Some duplicate `require_once` calls (safe but redundant)
- Mixed routing approach (custom routes + shortcodes defined but unused)
- No automated tests
- CSS could be further consolidated
- Tree visualization only shows descendants (no ancestors view)

## File References for Common Tasks

**Add a new database column:**
- Edit `includes/database.php` → `apply_schema_updates()` method

**Modify member form:**
- Edit `templates/members/add-member.php` or `edit-member.php`

**Change member AJAX logic:**
- Edit `family-tree.php` → `ajax_add_family_member()` or `ajax_update_family_member()`
- Edit `includes/database.php` → `add_member()` or `update_member()`

**Style changes:**
- Edit appropriate CSS file in `assets/css/`
- Bump version in `enqueue_scripts()` to bust cache

**Tree visualization changes:**
- Edit `assets/js/tree.js` (D3.js rendering)
- Edit `templates/tree-view.php` (HTML structure and data fetching)
