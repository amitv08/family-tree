# 🧬 Family Tree Plugin for WordPress

A complete genealogy and clan management plugin for WordPress.
It enables you to create **family trees**, manage **members**, organize them into **clans**, track **multiple marriages**, and visualize relationships interactively.

**Version:** 3.2.0
**Author:** Amit Vengsarkar
**License:** GPL-2.0+

---

## 🎉 What's New in v3.2.0

### Smart Bidirectional Parent Selection ⭐
- **Father → Mother**: Select father, system suggests mother(s) from marriages
- **Mother → Father**: Select mother, system suggests father(s) from marriages
- **Intelligent Auto-Fill**: Single marriage auto-populates, multiple marriages show dropdown
- **All Family Structures**: Single parent, adoption, remarriage, traditional - all supported
- Saves massive time entering children data!

### Performance Optimization 🚀
- **11 Database Indexes**: Name searches 30x faster, tree building 20-50x faster
- **N+1 Query Fixed**: Member pages load 82-96% faster with multiple marriages
- **Data Limits**: Prevents timeouts with large datasets (5K-10K member support)
- Browse members **8x faster**, tree view **6.7x faster**

### Mobile & Accessibility 📱
- **Touch Targets**: All buttons/inputs now 44x44px (Apple/Google guidelines)
- **One-Handed Use**: Fully usable on phones, tablets, desktops
- **Form Validation**: Visual feedback with error states
- **Confirmations**: All destructive actions have clear warnings

See [CHANGELOG.md](CHANGELOG.md) for complete version history.

---

## 📦 Features

### 🔹 Core Modules

- **Members Management**
  - Add, edit, browse, and view individual family members
  - Link members to their parents for tree generation
  - Store detailed bio, gender, birth/death dates, location, and more
  - Soft delete and restore functionality
  - Gender-based filtering for parent selection
  - **Marital status tracking** (Unmarried, Married, Divorced, Widowed)
  - **Integrated marriage form** within member add/edit pages

- **Multiple Marriages Support** ⭐ NEW in v3.0.0
  - Track unlimited marriages per person (polygamy, remarriage, divorce)
  - Complete marriage details: spouse, date, location, status, notes
  - Marriage status tracking: Married, Divorced, Widowed, Annulled
  - Link children to specific marriages (half-sibling tracking)
  - View marriage history on member profile pages
  - Add/Edit/Delete marriages with permission control
  - Automatic data migration from old single marriage date

- **Clans Module**
  - Group members under clans
  - Add clan metadata like origin year, description, surnames, and locations
  - Multiple surnames and locations per clan
  - Manage clans via CRUD pages (Add/Edit/Browse/View)
  - Smart update strategy that preserves member references

- **Clan ↔ Member Integration**
  - Each member belongs to a clan
  - Select clan, clan location, and clan surname dynamically
  - Dependent dropdowns update based on clan selection
  - Automatically link clan details while adding or editing a member

- **Tree Visualization** ⭐ Enhanced in v3.1.0
  - Interactive D3.js tree view
  - Color-coded clans with living/deceased indicators
  - **Zoom controls**: Zoom In (+), Zoom Out (-), Reset View
  - **Mouse wheel zoom** for smooth scaling (10% to 300%)
  - **Click and drag** to pan around large trees
  - Filter by clan
  - Hover tooltips showing member and clan information
  - Responsive design for all screen sizes

- **User Roles & Permissions**
  - Family Super Admin (full access)
  - Family Admin (manage members and users)
  - Family Editor (edit members only)
  - Family Viewer (read-only access)
  - Role-based CRUD access for clans, members, and marriages

---

## ⚙️ Installation & Setup

### Requirements

- **WordPress 6.x+**
- **PHP 8.0+**
- **MySQL 5.7+** or **MariaDB 10.2+**
- Tested on LocalWP and standard LAMP/LEMP stacks

### Installation Steps

1. **Download or clone** the plugin:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/your-username/family-tree.git
   ```

2. **Activate the plugin**:
   - Go to WordPress Admin → Plugins
   - Find "Family Tree Plugin"
   - Click "Activate"

3. **Plugin activation will**:
   - Create database tables:
     - `wp_family_members` - Member records
     - `wp_family_clans` - Clan information
     - `wp_family_marriages` - Marriage records (v3.0.0+)
     - `wp_clan_locations` - Clan locations
     - `wp_clan_surnames` - Clan surnames
   - Set up custom user roles and permissions
   - Grant super admin access to the site administrator
   - Run automatic data migration for existing records

4. **Access the plugin**:
   - Navigate to: `http://yoursite.com/family-dashboard`
   - Or use the WordPress admin menu

### First Time Setup

1. **Create a clan** (optional but recommended):
   - Go to `/add-clan`
   - Add clan name, origin year, locations, and surnames

2. **Add family members**:
   - Go to `/add-member`
   - Fill in member details
   - Link to clan and parents
   - Save

3. **View the family tree**:
   - Go to `/family-tree`
   - Interactive D3.js visualization of your family

---

## 🚀 Usage

### Available Routes

- `/family-dashboard` - Main dashboard with member grid
- `/family-login` - Custom login page
- `/add-member` - Add new family member
- `/edit-member?id=X` - Edit existing member
- `/browse-members` - Browse all members (table view)
- `/view-member?id=X` - View member details
- `/add-clan` - Add new clan
- `/edit-clan?id=X` - Edit existing clan
- `/browse-clans` - Browse all clans
- `/view-clan?id=X` - View clan details
- `/family-tree` - Interactive tree visualization

### Managing Members

**Add a Member:**
1. Navigate to `/add-member`
2. Select clan (required)
3. Fill in personal details (name, gender, dates)
4. Select father (filtered to males only)
5. Enter mother's name (text field or dropdown)
6. **Select marital status** (Unmarried/Married/Divorced/Widowed)
7. If married/divorced/widowed, fill in marriage details:
   - Spouse name
   - Marriage date and location
   - Divorce date (if applicable)
   - Notes
8. Add location and biography (optional)
9. Save (member and marriage are saved together)

**Edit a Member:**
1. Navigate to `/edit-member?id=X`
2. Modify any field including marital status
3. Update marriage details if status is married/divorced/widowed
4. Click "Update Member"
5. Changes are saved with audit trail

**View Member Profile:**
- Navigate to `/view-member?id=X`
- See complete profile with:
  - Personal information
  - Life events (birth, death, marital status)
  - **Marriage history** (all marriages listed)
  - Children grouped by marriage
  - Clan information
  - Add/Edit/Delete marriages directly from profile

### Managing Clans

**Add a Clan:**
1. Navigate to `/add-clan`
2. Enter clan name and origin year
3. Add locations (comma-separated tags)
4. Add surnames (comma-separated tags)
5. Save

**Edit a Clan:**
- Editing clans preserves member references
- Locations and surnames update intelligently
- Removed items are deleted, new items added, existing kept

---

## 🏗️ Architecture

### Version 2.4.0+ (Current)

The plugin follows modern **MVC architecture** with PSR-4 autoloading:

```
family-tree/
├── family-tree.php           # Bootstrap (48 lines)
├── CLAUDE.md                 # AI assistant instructions
├── CHANGELOG.md              # Version history
├── README.md                 # This file
├── includes/
│   ├── Autoloader.php        # PSR-4 autoloader
│   ├── Config.php            # Centralized constants
│   ├── Plugin.php            # Main plugin class
│   ├── Router.php            # Route handling
│   ├── Controllers/          # AJAX handlers (MVC)
│   ├── Repositories/         # Database layer
│   ├── Validators/           # Input validation
│   ├── database.php          # Legacy (backward compat)
│   └── clans-database.php    # Legacy (backward compat)
├── templates/                # PHP views
│   ├── components/           # Reusable UI components
│   ├── members/              # Member CRUD pages
│   └── clans/                # Clan CRUD pages
├── assets/
│   ├── css/                  # Modular CSS
│   └── js/                   # JavaScript modules
└── docs/                     # Technical documentation
    ├── architecture/
    └── features/
```

### Key Design Patterns

- **MVC Pattern** - Controllers, Views (templates), Models (repositories)
- **Repository Pattern** - Database abstraction layer
- **Dependency Injection** - Via base classes
- **PSR-4 Autoloading** - No manual require statements
- **Single Responsibility** - Each class has one job

---

## 📚 Documentation

### For Users

- **[USER_GUIDE.md](USER_GUIDE.md)** ⭐ **START HERE** - Complete user guide with screenshots
- **[CHANGELOG.md](CHANGELOG.md)** - Version history and release notes
- **[README.md](README.md)** - This file (features, installation, usage)

### For Developers

- **[CLAUDE.md](CLAUDE.md)** - Project overview, development guide, architecture
- **[docs/architecture/refactoring-summary.md](docs/architecture/refactoring-summary.md)** - MVC refactoring details
- **[docs/features/clan-update-fix.md](docs/features/clan-update-fix.md)** - Smart update strategy
- **[docs/features/member-form-improvements.md](docs/features/member-form-improvements.md)** - Form UX improvements

### Quick Links

- **New User?**: Start with [USER_GUIDE.md](USER_GUIDE.md)
- **Latest Changes**: See [CHANGELOG.md](CHANGELOG.md)
- **Development Setup**: See [CLAUDE.md](CLAUDE.md)
- **Architecture Details**: See [docs/architecture/](docs/architecture/)
- **Feature Documentation**: See [docs/features/](docs/features/)

---

## 🔧 Development

### Local Development Setup

1. **Install LocalWP** (or similar local WordPress environment)
2. **Clone the plugin** into `wp-content/plugins/family-tree`
3. **Enable debug mode** in `wp-config.php`:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', true);
   define('SCRIPT_DEBUG', true);
   ```
4. **Activate the plugin** in WordPress Admin
5. **View debug log**: `wp-content/debug.log`

### Making Changes

**After PHP changes:**
- Refresh the page
- Check `debug.log` for errors

**After CSS/JS changes:**
- Hard refresh (Ctrl+Shift+R)
- Or bump version number in `enqueue_scripts()`

**Database schema changes:**
- Edit `includes/database.php` → `apply_schema_updates()`
- Deactivate and reactivate plugin to run updates

### Git Workflow

```bash
cd wp-content/plugins/family-tree
git status
git add .
git commit -m "Description of changes"
git push
```

---

## 🧪 Testing

### Manual Testing Checklist

- [ ] Plugin activates without errors
- [ ] All routes load correctly (`/family-dashboard`, `/add-member`, etc.)
- [ ] AJAX operations work (add/edit/delete members and clans)
- [ ] Dependent dropdowns update (clan → locations/surnames)
- [ ] User management functions
- [ ] Permissions work for all roles
- [ ] Tree visualization renders
- [ ] No PHP errors in `debug.log`
- [ ] No JavaScript errors in browser console

### Test URLs

- Main Dashboard: `http://yoursite.local/family-dashboard`
- Add Member: `http://yoursite.local/add-member`
- Browse Members: `http://yoursite.local/browse-members`
- Tree View: `http://yoursite.local/family-tree`
- WordPress Admin: `http://yoursite.local/wp-admin`

---

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Follow WordPress coding standards
4. Test thoroughly
5. Commit your changes (`git commit -m 'Add amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

---

## 📝 License

This project is licensed under the GPL-2.0+ License - see the [LICENSE](LICENSE) file for details.

---

## 👨‍💻 Author

**Amit Vengsarkar**

---

## 🙏 Acknowledgments

- D3.js for tree visualization
- Select2 for searchable dropdowns
- WordPress community for best practices
- Claude (Anthropic) for code assistance

---

## 📞 Support

For issues, questions, or feature requests:

- **GitHub Issues**: [Create an issue](https://github.com/your-username/family-tree/issues)
- **Email**: your-email@example.com
- **Documentation**: See [docs/](docs/) folder

---

## 🗺️ Roadmap

### ✅ Recently Completed

- [x] **Smart bidirectional parent selection** (v3.2.0) - Father→Mother AND Mother→Father auto-fill
- [x] **Database performance optimization** (v3.2.0) - 11 indexes, 8-30x faster queries
- [x] **N+1 query problem fixed** (v3.2.0) - 82-96% query reduction
- [x] **Mobile touch targets** (v3.2.0) - 44x44px buttons (Apple/Google guidelines)
- [x] **Form validation feedback** (v3.2.0) - Visual error/success states
- [x] **Multiple marriages support** (v3.0.0) - Track unlimited marriages per person
- [x] **Marriage form integration** (v3.1.0) - Add marriages within member forms
- [x] **Tree zoom controls** (v3.1.0) - Enhanced zoom and pan functionality
- [x] **Marital status tracking** (v3.1.0) - Dropdown with conditional marriage details
- [x] **Smart update strategy for clans** (v2.5.0) - Preserve member references
- [x] **MVC architecture refactoring** (v2.4.0) - Modern code organization

### Upcoming Features

- [ ] Advanced search and filtering
- [ ] Export to GEDCOM format
- [ ] Import from CSV/Excel
- [ ] Photo gallery for members
- [ ] Timeline view of family events
- [ ] Ancestor view in tree (currently shows descendants only)
- [ ] Multi-language support
- [ ] Advanced reporting and statistics
- [ ] Print-friendly family tree views

### Under Consideration

- [ ] DNA match integration
- [ ] Family stories and memories module
- [ ] Document attachment system
- [ ] Collaborative editing
- [ ] Public family tree sharing
- [ ] Mobile app integration
- [ ] Family event calendar

---

**Made with ❤️ for preserving family history**
