<?php
/**
 * Family Tree Plugin - Dashboard Page
 * Main dashboard with navigation links
 */

if (!is_user_logged_in()) {
    wp_redirect('/family-login');
    exit;
}

$page_title = '🏠 Family Tree Dashboard';
$page_actions = '<a href="/add-member" class="btn btn-primary btn-sm">➕ Add Member</a>
                 <a href="/add-clan" class="btn btn-outline btn-sm">🏛️ Add Clan</a>';

ob_start();
?>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <div class="card-header">
            <h3>👥 Members</h3>
        </div>
        <div class="card-body">
            <p>Manage family members, their details, and relationships.</p>
            <div class="card-actions">
                <a href="/add-member" class="btn btn-primary">➕ Add Member</a>
                <a href="/browse-members" class="btn btn-outline">📋 Browse Members</a>
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="card-header">
            <h3>🏛️ Clans</h3>
        </div>
        <div class="card-body">
            <p>Manage family clans, locations, and surnames.</p>
            <div class="card-actions">
                <a href="/add-clan" class="btn btn-primary">➕ Add Clan</a>
                <a href="/browse-clans" class="btn btn-outline">📋 Browse Clans</a>
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="card-header">
            <h3>🌳 Family Tree</h3>
        </div>
        <div class="card-body">
            <p>View the interactive family tree visualization.</p>
            <div class="card-actions">
                <a href="/family-tree" class="btn btn-primary">🌳 View Tree</a>
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="card-header">
            <h3>💒 Marriages</h3>
        </div>
        <div class="card-body">
            <p>Manage marriage records and relationships.</p>
            <div class="card-actions">
                <span class="text-muted">Coming Soon</span>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.dashboard-card {
    background: var(--card-bg, #fff);
    border: 1px solid var(--border-color, #e1e5e9);
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: box-shadow 0.2s ease;
}

.dashboard-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.card-header {
    background: var(--header-bg, #f8f9fa);
    padding: 1rem;
    border-bottom: 1px solid var(--border-color, #e1e5e9);
}

.card-header h3 {
    margin: 0;
    color: var(--text-primary, #2c3e50);
    font-size: 1.25rem;
}

.card-body {
    padding: 1.5rem;
}

.card-body p {
    margin: 0 0 1rem 0;
    color: var(--text-secondary, #6c757d);
    line-height: 1.5;
}

.card-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.card-actions .btn {
    flex: 1;
    min-width: 120px;
}
</style>

<?php
$page_content = ob_get_clean();
include FAMILY_TREE_PATH . 'templates/components/page-layout.php';
?>