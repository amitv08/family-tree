<?php
/**
 * Reusable header component
 * Used on all pages
 */

$current_user = wp_get_current_user();
$current_page = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
?>

<header class="site-header">
  <div class="header-container">
    <!-- Logo/Branding -->
    <a href="/family-dashboard" class="site-logo">
      <span class="site-logo-icon">🌳</span>
      <span>Family Tree</span>
    </a>

    <!-- Main Navigation -->
    <nav class="site-nav">
      <a href="/family-dashboard" class="nav-item <?php echo strpos($current_page, 'family-dashboard') !== false ? 'active' : ''; ?>">
        <span>📊</span>
        <span>Dashboard</span>
      </a>
      <a href="/browse-members" class="nav-item <?php echo strpos($current_page, 'browse-members') !== false || strpos($current_page, 'add-member') !== false || strpos($current_page, 'edit-member') !== false || strpos($current_page, 'view-member') !== false ? 'active' : ''; ?>">
        <span>👥</span>
        <span>Members</span>
      </a>
      <a href="/browse-clans" class="nav-item <?php echo strpos($current_page, 'browse-clans') !== false || strpos($current_page, 'add-clan') !== false || strpos($current_page, 'edit-clan') !== false || strpos($current_page, 'view-clan') !== false ? 'active' : ''; ?>">
        <span>🏰</span>
        <span>Clans</span>
      </a>
      <a href="/family-tree" class="nav-item <?php echo strpos($current_page, 'family-tree') !== false ? 'active' : ''; ?>">
        <span>🌲</span>
        <span>Tree View</span>
      </a>
    </nav>

    <!-- User Section -->
    <div class="user-section">
      <div class="user-info">
        <strong><?php echo esc_html($current_user->display_name); ?></strong>
        <small><?php echo esc_html(implode(', ', array_map(function($r) { return ucfirst(str_replace('family_', '', $r)); }, $current_user->roles))); ?></small>
      </div>
      <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-outline btn-sm">
        Logout
      </a>
    </div>
  </div>
</header>