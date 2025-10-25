# 📖 Family Tree Plugin - User Guide

**Version:** 3.2.0
**Last Updated:** October 25, 2025

Welcome to the Family Tree Plugin! This guide will help you get started and make the most of all features.

---

## 📑 Table of Contents

1. [Getting Started](#getting-started)
2. [Dashboard Overview](#dashboard-overview)
3. [Managing Family Members](#managing-family-members)
4. [Managing Marriages](#managing-marriages)
5. [Managing Clans](#managing-clans)
6. [Viewing the Family Tree](#viewing-the-family-tree)
7. [Tips & Best Practices](#tips--best-practices)
8. [Troubleshooting](#troubleshooting)

---

## 🚀 Getting Started

### First Time Setup

After your administrator activates the plugin:

1. **Log in** to your WordPress site
2. Navigate to: `http://yoursite.com/family-dashboard`
3. You'll see the main dashboard with your access level

### User Roles

Your access level determines what you can do:

| Role | What You Can Do |
|------|----------------|
| **Super Admin** | Everything - manage users, clans, members, marriages |
| **Admin** | Manage members, marriages, view tree |
| **Editor** | Add/edit members and marriages only |
| **Viewer** | View tree and member details only (read-only) |

---

## 🏠 Dashboard Overview

The dashboard shows:

- **Quick Stats**: Total members, your role, clan count
- **Tree Health**: Profile completion percentage, birth dates, photos
- **Quick Actions**: Direct links to add members, view tree, browse members
- **Gender Breakdown**: Male/female/other member counts

**Navigation Menu:**
- 🌲 Tree View - Interactive family tree
- 👥 Members - Browse all family members
- 🏰 Clans - Manage family groups
- ⚙️ Admin - User management (admins only)

---

## 👤 Managing Family Members

### Adding a New Member

1. Click **"Add Member"** from dashboard or navigation
2. Fill in the form (required fields marked with *):

#### **Basic Information**
- **First Name** * (required)
- **Middle Name** (optional)
- **Last Name** * (required)
- **Nickname** (e.g., "Johnny" for John)
- **Gender** (Male/Female/Other)

#### **Life Events**
- **Birth Date** (YYYY-MM-DD format)
- **Death Date** (leave empty if living)
- **Marital Status** * (Unmarried/Married/Divorced/Widowed)

#### **Marriage Details** (appears if married/divorced/widowed)
- **Spouse Name**
- **Marriage Date**
- **Marriage Location**
- **Divorce Date** (only for divorced status)
- **Notes** (any additional info)

#### **Parents & Family**
- **Father** (select from dropdown)
- **Mother** (text input OR select from dropdown)

✨ **Smart Feature**: When you select a father, the system automatically suggests the mother from his marriages! Same works in reverse - select mother to see father suggestions.

#### **Clan Information**
- **Clan** * (required - family group)
- **Clan Location** (auto-populated from clan)
- **Clan Surname** (auto-populated from clan)

#### **Address** (optional)
- Address, City, State, Country, Postal Code

#### **Biography** (optional)
- Tell their story, achievements, memories

3. Click **"Add Member"** button
4. You'll see a success message and member will appear in lists

---

### Editing a Member

1. Go to **Browse Members** or **View Member**
2. Click **"Edit"** button (✏️ icon)
3. Modify any fields
4. Click **"Update Member"**

**Note:** If you change the father/mother, the system will ask if you want to see suggestions from the new parent's marriages.

---

### Viewing a Member

1. Go to **Browse Members**
2. Click **"View"** on any member (👁️ icon)

You'll see:
- **Personal Information**: Name, birth/death dates, gender, clan
- **Life Events**: Age, marital status badges
- **Marriages Section**: All marriages with:
  - Spouse name (clickable if in system)
  - Marriage date and location
  - Status badge (Married/Divorced/Widowed)
  - Children grouped by marriage
  - Add/Edit/Delete buttons (if you have permission)
- **Family Relationships**: Parents, siblings
- **Biography**: Their life story
- **Address**: Location information

---

### Deleting & Restoring Members

**Soft Delete** (Recommended):
1. Click **Delete** button (🗑️ icon)
2. Confirm the action
3. Member is hidden but can be restored later
4. Find deleted members in Browse Members → Filter by "Deleted"
5. Click **Restore** to bring them back

**Why Soft Delete?**
- Can undo mistakes
- Preserves data integrity
- Children relationships maintained
- Can restore anytime

---

## 💍 Managing Marriages

### Adding a Marriage (Two Ways)

#### Method 1: During Member Creation
1. When adding a member, select marital status: Married/Divorced/Widowed
2. Marriage Details section appears automatically
3. Fill in spouse, date, location
4. Save - marriage is created automatically!

#### Method 2: From Member Profile
1. View an existing member
2. Scroll to **Marriages** section
3. Click **"➕ Add Marriage"**
4. Fill in the modal form:
   - Spouse name (or select from dropdown if in system)
   - Marriage date
   - Marriage location
   - Marriage status
   - Marriage order (1st, 2nd, 3rd marriage)
   - Divorce date (if applicable)
   - Notes
5. Click **"Save Marriage"**

---

### Editing a Marriage

1. View member profile
2. Find the marriage in **Marriages** section
3. Click **"Edit"** (✏️ icon)
4. Modal opens with pre-filled data
5. Modify fields
6. Click **"Save Marriage"**

---

### Deleting a Marriage

1. View member profile
2. Find the marriage in **Marriages** section
3. Click **"Delete"** (🗑️ icon)
4. Confirm (note: this action cannot be undone)

**⚠️ Important:** You cannot delete a marriage that has children linked to it. You must first unlink the children or assign them to a different marriage.

---

### Multiple Marriages Support

The plugin fully supports:
- **Remarriage** - Same person marries multiple times (after divorce/death)
- **Polygamy** - Multiple simultaneous marriages (if culturally relevant)
- **Half-Siblings** - Children tracked by specific marriage
- **Marriage Order** - 1st marriage, 2nd marriage, etc.

**Example:**
- John marries Mary (1st marriage) → has 2 children
- John and Mary divorce
- John marries Sarah (2nd marriage) → has 1 child
- Result: All 3 children tracked correctly with their biological mothers

---

## 🏰 Managing Clans

### What is a Clan?

A clan is a family group - it could be:
- A surname family (Smith family)
- A geographical group (Mumbai branch)
- An ancestral lineage (Patel clan)
- Any logical grouping you choose

---

### Adding a Clan

1. Click **"Add Clan"** from navigation
2. Fill in:
   - **Clan Name** * (required) - e.g., "Smith Family"
   - **Origin Year** - when the clan started
   - **Description** - history, origin story
   - **Locations** - Add multiple locations (comma-separated tags)
   - **Surnames** - Add multiple surnames (comma-separated tags)
3. Click **"Add Clan"**

**Tip:** You can mark one location as "primary" and one surname as "primary"

---

### Clan Locations & Surnames

**Why separate?**
- Some family members might be in different locations
- Some might have married and changed surnames
- Flexible to track real family diversity

**Example:**
```
Clan: Patel Family
Locations: Mumbai, Pune, Ahmedabad (Mumbai = primary)
Surnames: Patel, Shah, Mehta (Patel = primary)

Member 1: Raj Patel (Mumbai) - Primary location, primary surname
Member 2: Priya Shah (Pune) - Secondary location, married surname
```

---

### Editing & Deleting Clans

**Edit:**
1. Go to **Browse Clans**
2. Click **Edit** on a clan
3. Modify fields
4. Click **"Update Clan"**

**Delete:**
1. Go to **Browse Clans**
2. Click **Delete** on a clan
3. Confirm the action

**⚠️ Warning:** Deleting a clan will NOT delete members, but they'll lose clan references. Consider editing instead.

---

## 🌳 Viewing the Family Tree

### Interactive Tree View

1. Click **"Tree View"** from navigation
2. You'll see an interactive D3.js visualization

**Features:**
- **Color-coded** - Different colors for different clans
- **Living/Deceased** - Visual distinction
- **Zoom In/Out** - Use 🔍 +/− buttons or mouse wheel
- **Pan** - Click and drag to move around
- **Reset View** - Return to default position
- **Filter by Clan** - Dropdown to show specific clans only
- **Hover** - See member details in tooltip

---

### Tree Controls

**Zoom Controls:**
- **🔍 +** - Zoom in (make nodes bigger)
- **🔍 −** - Zoom out (see more tree)
- **🔄 Reset** - Return to original view
- **Mouse Wheel** - Smooth zoom (10% to 300%)

**Pan (Move):**
- Click and drag anywhere on the tree
- Touch and drag on tablets/phones

**Filter:**
- Select a clan from dropdown
- Tree shows only that clan's members
- Select "All Clans" to see everyone

---

### Understanding the Tree

**Node Colors:**
- 🔵 Blue circle = Living Male
- 🔴 Red circle = Living Female
- 🟢 Green circle = Living Other gender
- ⚫ Dark circle = Deceased (any gender)

**Lines:**
- Connect parents to children
- Flow from top (older generation) to bottom (younger)

**Tips:**
- Tree shows **descendants** (children, grandchildren, etc.)
- Start from oldest ancestors at top
- Branch out to all descendants below

---

## 💡 Tips & Best Practices

### Data Entry Tips

**1. Start with Oldest Ancestors**
- Add grandparents/great-grandparents first
- Then work down to children, grandchildren
- This builds the tree naturally

**2. Use Smart Parent Selection**
- Add marriages for parents first
- When adding children, select father → mother auto-fills!
- Or select mother → father auto-fills!
- Saves tons of time and reduces errors

**3. Consistency is Key**
- Use same date format (YYYY-MM-DD)
- Be consistent with names (John vs. Johnny)
- Use nicknames field for variants

**4. Add Photos**
- Upload to WordPress Media Library
- Copy URL and paste in Photo URL field
- Makes tree more engaging

**5. Write Biographies**
- Record stories before they're lost
- Include achievements, personality, memories
- Future generations will thank you!

---

### Handling Special Cases

**Adopted Children:**
1. Check "This person is adopted" checkbox
2. Enter adoptive parents
3. Notes field for biological parent info if known

**Single Parents:**
1. Select the known parent (father OR mother)
2. Leave other parent empty
3. System handles it gracefully

**Unknown Parents:**
1. Leave both parents empty
2. Add what you know in biography/notes
3. Can fill in later when discovered

**Step-Children:**
1. Use marriage system to track biological parents
2. Children linked to specific marriage
3. Half-siblings tracked automatically

**Same-Sex Parents:**
1. Parent1 and Parent2 are gender-neutral
2. Assign as needed
3. System is flexible

---

### Mobile Usage

**On Phones/Tablets:**
- ✅ All buttons are touch-friendly (44x44px)
- ✅ Forms scale to fit screen
- ✅ Tree zoom with pinch gestures
- ✅ One-handed use possible
- ✅ Tables scroll horizontally

**Best Practices:**
- Use landscape mode for forms
- Portrait mode fine for viewing
- Tree view best in landscape

---

## 🔧 Troubleshooting

### Common Issues

**Q: I can't see the "Add Member" button**
- **A:** Check your role. Viewers cannot add members. Contact admin for permission upgrade.

**Q: Mother doesn't auto-fill when I select father**
- **A:** The father must have a marriage record first. Add his marriage from his profile, then try again.

**Q: Can't delete a marriage**
- **A:** Marriage has children linked to it. Unlink children first or assign to different marriage.

**Q: Tree is too small/big**
- **A:** Use 🔍 +/− buttons or mouse wheel to zoom. Click 🔄 Reset to return to default.

**Q: Member doesn't appear in tree**
- **A:** Check:
  1. Is member deleted? (can restore)
  2. Are parents linked correctly?
  3. Is clan assigned?
  4. Try clan filter to isolate

**Q: Form won't submit**
- **A:** Check for:
  1. Required fields (marked with *)
  2. Valid date format (YYYY-MM-DD)
  3. Browser console for errors
  4. Try refreshing page

**Q: Changes don't appear**
- **A:**
  1. Hard refresh (Ctrl+Shift+R / Cmd+Shift+R)
  2. Check if success message appeared
  3. Verify you clicked "Save" not just "Cancel"

---

### Performance Tips

**Large Families (1000+ members):**
- Use clan filter to view subsets
- Browse members page is paginated
- Search instead of scrolling
- Tree view limited to 10,000 nodes automatically

**Slow Loading:**
- Clear browser cache
- Check internet connection
- Contact admin - database might need optimization

---

### Getting Help

**Need Help?**
1. Check this guide first
2. Ask your administrator
3. Check WordPress debug log (admins)
4. Visit plugin support forum
5. Email developer: [support email]

**Reporting Bugs:**
Include:
- What you were trying to do
- What happened instead
- Your role (Admin/Editor/Viewer)
- Browser (Chrome/Firefox/Safari)
- Device (Phone/Tablet/Desktop)
- Screenshots if possible

---

## 🎓 Quick Reference

### Keyboard Shortcuts (Tree View)

- **Scroll** - Pan tree
- **Ctrl + Scroll** - Zoom in/out
- **+** - Zoom in
- **−** - Zoom out
- **R** - Reset view (if implemented)

### Common Actions

| Action | Location | Permission |
|--------|----------|----------|
| Add Member | Dashboard → Add Member | Editor+ |
| Edit Member | Browse → Edit button | Editor+ |
| Delete Member | Browse → Delete button | Admin+ |
| Add Marriage | Member View → Add Marriage | Editor+ |
| View Tree | Navigation → Tree View | All |
| Add Clan | Navigation → Add Clan | Super Admin |

---

## 📞 Support & Resources

- **Documentation**: See README.md and CHANGELOG.md
- **Technical Docs**: See CLAUDE.md (for developers)
- **Version**: 3.2.0
- **Release Date**: October 25, 2025

---

**Made with ❤️ for preserving family history**

*Last updated: October 25, 2025*
