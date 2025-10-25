# 🧬 Family Tree Plugin for WordPress

A complete genealogy and clan management plugin for WordPress.
It enables you to create **family trees**, manage **members**, organize them into **clans**, and visualize relationships interactively.

**Version:** 2.5.0
**Author:** Amit Vengsarkar
**License:** GPL-2.0+

---

## 📦 Features

### 🔹 Core Modules

- **Members Management**
  - Add, edit, browse, and view individual family members
  - Link members to their parents for tree generation
  - Store detailed bio, gender, birth/death dates, location, and more
  - Soft delete and restore functionality
  - Gender-based filtering for parent selection

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

- **Tree Visualization**
  - Interactive D3.js tree view
  - Color-coded clans
  - Zoom, pan, and filter by clan
  - Tooltips showing member and clan information

- **User Roles & Permissions**
  - Family Super Admin (full access)
  - Family Admin (manage members and users)
  - Family Editor (edit members only)
  - Family Viewer (read-only access)
  - Role-based CRUD access for clans and members

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
   - Create database tables (`wp_family_members`, `wp_family_clans`, etc.)
   - Set up custom user roles and permissions
   - Grant super admin access to the site administrator

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
5. Enter mother's name (text field)
6. Add location and biography (optional)
7. Save

**Edit a Member:**
1. Navigate to `/edit-member?id=X`
2. Modify any field
3. Click "Update Member"
4. Changes are saved with audit trail

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

- **[CHANGELOG.md](CHANGELOG.md)** - Version history and release notes
- **[README.md](README.md)** - This file (features, installation, usage)

### For Developers

- **[CLAUDE.md](CLAUDE.md)** - Project overview, development guide, architecture
- **[docs/architecture/refactoring-summary.md](docs/architecture/refactoring-summary.md)** - MVC refactoring details
- **[docs/features/clan-update-fix.md](docs/features/clan-update-fix.md)** - Smart update strategy
- **[docs/features/member-form-improvements.md](docs/features/member-form-improvements.md)** - Form UX improvements

### Quick Links

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

### Upcoming Features

- [ ] Advanced search and filtering
- [ ] Export to GEDCOM format
- [ ] Import from CSV/Excel
- [ ] Photo gallery for members
- [ ] Timeline view of family events
- [ ] Mobile app integration
- [ ] Multi-language support
- [ ] Advanced reporting and statistics

### Under Consideration

- [ ] DNA match integration
- [ ] Family stories and memories module
- [ ] Document attachment system
- [ ] Collaborative editing
- [ ] Public family tree sharing

---

**Made with ❤️ for preserving family history**
