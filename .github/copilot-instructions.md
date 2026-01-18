# GitHub Copilot Instructions for Family Tree WordPress Plugin

## Project Overview
This is a **WordPress plugin** for genealogy and clan management with MVC architecture, custom routing, and interactive D3.js tree visualization.

**Key Architecture:**
- **MVC Pattern**: Controllers handle AJAX, Repositories manage DB, Validators check input
- **PSR-4 Autoloading**: `FamilyTree\` namespace in `includes/` directory
- **Custom Routing**: URL routing via `Router` class, not WordPress pages/posts
- **Database**: Custom tables (`wp_family_members`, `wp_family_clans`, `wp_family_marriages`) with audit fields

## Essential Patterns

### AJAX Controller Pattern
```php
// In Controllers/MemberController.php
public function add() {
    $this->verify_nonce();
    $this->verify_capability(Config::CAP_EDIT_FAMILY_MEMBERS);

    $data = $this->validator->validate($_POST);
    if ($data === false) {
        $this->error('Validation failed', $this->validator->get_errors());
    }

    $result = $this->repository->add($data);
    $this->success('Member added successfully', $result);
}
```

**JavaScript call:**
```javascript
$.post(family_tree.ajax_url, {
    action: 'add_family_member',
    nonce: family_tree.nonce,
    // form data
}, function(response) {
    if (response.success) {
        showToast('Member added successfully', 'success');
    } else {
        showToast('Error: ' + response.data, 'error');
    }
});
```

**Always include:**
- `$this->verify_nonce()` for security
- `$this->verify_capability()` for permissions
- Validation via dedicated Validator classes
- Return via `$this->success()` or `$this->error()`

### Repository Pattern
```php
// In Repositories/MemberRepository.php
public function get_members(int $limit = null, int $offset = 0): array {
    global $wpdb;
    $table = Config::get_table_name(Config::TABLE_MEMBERS);

    $sql = $wpdb->prepare(
        "SELECT * FROM {$table} WHERE is_deleted = 0 ORDER BY first_name LIMIT %d OFFSET %d",
        $limit ?? PHP_INT_MAX,
        $offset
    );

    return $wpdb->get_results($sql, ARRAY_A);
}
```

**Always use:**
- `Config::get_table_name()` for table names
- `$wpdb->prepare()` for all queries
- `ARRAY_A` for result format consistency

### Custom Routing
```php
// Routes defined in Config::ROUTES
public const ROUTES = [
    '/family-dashboard' => 'dashboard.php',
    '/add-member' => 'members/add-member.php',
    // ...
];
```

**Route files go in `templates/` directory matching the route structure.**

### Database Schema Patterns
- **Audit fields**: `created_by`, `updated_by`, `created_at`, `updated_at`
- **Soft deletes**: `is_deleted` flag instead of hard deletes
- **Foreign keys**: Nullable for flexibility (e.g., `parent1_id`, `parent2_id`)
- **Multiple relationships**: Clans have multiple locations/surnames via separate tables

### Audit Fields Pattern
```php
// Always include audit fields in database operations
$data = [
    'first_name' => sanitize_text_field($input['first_name']),
    'created_by' => $this->get_current_user_id(),  // From BaseRepository
    'created_at' => current_time('mysql'),
    'updated_by' => $this->get_current_user_id(),
    'updated_at' => current_time('mysql'),
];
```

**Always set:**
- `created_by` and `updated_by` to `get_current_user_id() ?: 0`
- `created_at` and `updated_at` to `current_time('mysql')`

### Select2 Dependent Dropdowns
```javascript
// Clan selection triggers dependent dropdowns
$('#clan_id').on('change', function() {
    loadClanDetails($(this).val());
});

function loadClanDetails(clanId) {
    $.post(family_tree.ajax_url, {
        action: 'get_clan_details',
        nonce: family_tree.nonce,
        clan_id: clanId
    }, function(response) {
        if (response.success) {
            // Populate location and surname dropdowns
            populateDropdown('#clan_location_id', response.data.locations);
            populateDropdown('#clan_surname_id', response.data.surnames);
        }
    });
}
```

### Toast Notifications
```javascript
// Use showToast for user feedback (defined in script.js)
showToast('Member added successfully', 'success');
showToast('Error: Invalid input', 'error');
showToast('Warning: Field required', 'warning');

// Types: 'success', 'error', 'warning', 'info'
```

### Soft Delete Pattern
```php
// Instead of hard delete, set flag
$sql = $wpdb->prepare(
    "UPDATE {$table} SET is_deleted = 1, updated_by = %d, updated_at = %s WHERE id = %d",
    get_current_user_id(),
    current_time('mysql'),
    $member_id
);

// Restore by clearing flag
$sql = $wpdb->prepare(
    "UPDATE {$table} SET is_deleted = 0, updated_by = %d, updated_at = %s WHERE id = %d",
    get_current_user_id(),
    current_time('mysql'),
    $member_id
);
```

**Filter queries:** Always add `WHERE is_deleted = 0` to exclude deleted records.

### Select2 Dependent Dropdowns
```javascript
// Clan selection triggers dependent dropdowns
$('#clan_id').on('change', function() {
    loadClanDetails($(this).val());
});

function loadClanDetails(clanId) {
    $.post(family_tree.ajax_url, {
        action: 'get_clan_details',
        nonce: family_tree.nonce,
        clan_id: clanId
    }, function(response) {
        if (response.success) {
            // Populate location and surname dropdowns
            populateDropdown('#clan_location_id', response.data.locations);
            populateDropdown('#clan_surname_id', response.data.surnames);
        }
    });
}
```

### Toast Notifications
```javascript
// Use showToast for user feedback (defined in script.js)
showToast('Member added successfully', 'success');
showToast('Error: Invalid input', 'error');
showToast('Warning: Field required', 'warning');

// Types: 'success', 'error', 'warning', 'info'
```

### Soft Delete Pattern
```php
// Instead of hard delete, set flag
$sql = $wpdb->prepare(
    "UPDATE {$table} SET is_deleted = 1, updated_by = %d, updated_at = %s WHERE id = %d",
    get_current_user_id(),
    current_time('mysql'),
    $member_id
);

// Restore by clearing flag
$sql = $wpdb->prepare(
    "UPDATE {$table} SET is_deleted = 0, updated_by = %d, updated_at = %s WHERE id = %d",
    get_current_user_id(),
    current_time('mysql'),
    $member_id
);
```

**Filter queries:** Always add `WHERE is_deleted = 0` to exclude deleted records.

## Development Workflow

### Adding New Features
1. **Database changes**: Add to `apply_schema_updates()` in `includes/database.php`
2. **AJAX endpoint**: Add constant to `Config`, method to Controller, register in `Plugin::register_hooks()`
3. **Route**: Add to `Config::ROUTES`, create template in `templates/`
4. **Frontend**: Update JavaScript in `assets/js/`, CSS in `assets/css/`

### Testing Commands
```bash
# Run comprehensive tests
./run-tests.sh

# Check PHP syntax
find . -name "*.php" -exec php -l {} \;

# Test AJAX endpoints
# Use browser dev tools Network tab to verify responses
```

### CSS Organization
- `variables.css`: Colors, spacing, typography
- `base.css`: Resets and base styles
- `forms.css`: Form controls and validation
- `components.css`: Cards, tables, modals
- `layout.css`: Grid and page structure
- `responsive.css`: Media queries

**Import order in `style.css` matters for cascade.**

## Key Files to Reference

- **`includes/Config.php`**: All constants, routes, table names
- **`includes/Plugin.php`**: Hook registration and initialization
- **`templates/components/page-layout.php`**: Common page wrapper
- **`assets/css/variables.css`**: Design tokens
- **`assets/js/script.js`**: Toast notifications and global utilities
- **`assets/js/members.js`**: Member-specific JavaScript functionality
- **`assets/js/tree.js`**: D3.js tree visualization logic
- **`CLAUDE.md`**: Detailed architecture documentation

## Common Gotchas

- **Routing**: Use custom routes, not WordPress pages
- **Database**: Always use `$wpdb->prepare()`, never direct queries
- **Security**: Always verify nonces and capabilities in AJAX
- **Roles**: Use custom family roles, not default WordPress roles
- **Dependencies**: Load external libraries (D3.js, Select2) via CDN in templates
- **Cache busting**: Bump version numbers in `enqueue_scripts()` for CSS/JS changes
- **Audit fields**: Always update `updated_by` and `updated_at` on modifications
- **Soft deletes**: Use `is_deleted = 1` instead of hard deletes, filter queries accordingly
- **Multiple marriages**: Link children to specific marriages via `parent_marriage_id`
- **Select2**: Initialize after DOM ready, use AJAX for large datasets
- **Toast notifications**: Use consistent messaging for user feedback
- **Form validation**: Validate client-side before AJAX, server-side in validators

## WordPress Integration

- **Activation**: `Plugin::activate()` creates tables and roles
- **Roles**: Custom family roles created in `includes/roles.php`
- **Shortcodes**: Available in `includes/shortcodes.php`
- **AJAX**: All endpoints go through `admin-ajax.php`
- **Assets**: Enqueued in `Plugin::enqueue_scripts()`

## Additional Critical Patterns

### Asset Loading Pattern
```php
// In Plugin::enqueue_scripts()
wp_enqueue_style(
    'select2-style',
    'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css'
);
wp_enqueue_script(
    'select2',
    'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js',
    ['jquery']
);

// Localize script for AJAX
wp_localize_script('family-tree-members', 'family_tree', [
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce(Config::NONCE_NAME),
]);
```

**CDN libraries:** Load external libraries (D3.js, Select2) via CDN with proper dependencies.

### Multiple Marriages Pattern
```php
// Link children to specific marriages
$data = [
    'parent1_id' => $husband_id,
    'parent2_id' => $wife_id,
    'parent_marriage_id' => $marriage_id,  // Links to wp_family_marriages
];

// Marriage status tracking
$marriage_data = [
    'husband_id' => $husband_id,
    'wife_id' => $wife_id,
    'marriage_status' => 'married', // married, divorced, widowed, annulled
    'divorce_date' => null,
    'end_reason' => null,
];
```

### D3.js Tree Visualization
```javascript
// Initialize tree with zoom and pan
const tree = new FamilyTreeVisualization('tree-container', memberData);

// D3.js setup with zoom controls
this.zoom = d3.zoom()
    .scaleExtent([0.1, 2])
    .on('zoom', (event) => {
        this.svg.attr('transform', event.transform);
    });
```

### Form Validation Pattern
```javascript
// Client-side validation before AJAX
$('#memberForm').on('submit', function(e) {
    e.preventDefault();
    
    if (!$('#first_name').val().trim()) {
        showToast('First name is required', 'error');
        return;
    }
    
    // Submit via AJAX
    $.post(family_tree.ajax_url, {
        action: 'add_family_member',
        nonce: family_tree.nonce,
        // form data
    }, handleResponse);
});
```