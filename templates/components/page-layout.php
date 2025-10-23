<?php
/**
 * Master page layout wrapper
 * Props: $page_title, $page_actions (optional), $breadcrumbs (optional)
 */

if (!is_user_logged_in()) {
    wp_redirect('/family-login');
    exit;
}
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Professional Family Tree Management">
    <title><?php echo esc_html($page_title ?? 'Family Tree'); ?> - <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>

<body <?php body_class('page-wrapper'); ?>>
    <?php wp_body_open(); ?>

    <!-- Header Navigation -->
    <?php include FAMILY_TREE_PATH . 'templates/components/header.php'; ?>

    <!-- Breadcrumbs (optional) -->
    <?php if (!empty($breadcrumbs)): ?>
        <div class="page-content" style="padding-bottom: 0;">
            <nav class="breadcrumb">
                <?php foreach ($breadcrumbs as $i => $crumb): ?>
                    <div class="breadcrumb-item">
                        <?php if (isset($crumb['url'])): ?>
                            <a href="<?php echo esc_url($crumb['url']); ?>">
                                <?php echo esc_html($crumb['label']); ?>
                            </a>
                        <?php else: ?>
                            <span class="active">
                                <?php echo esc_html($crumb['label']); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php if ($i < count($breadcrumbs) - 1): ?>
                        <div class="breadcrumb-separator">/</div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
        </div>
    <?php endif; ?>

    <!-- Main Content Area -->
    <div class="page-content">
        <!-- Page Header -->
        <?php if (!empty($page_title)): ?>
            <div class="page-header">
                <h1><?php echo esc_html($page_title); ?></h1>
                <?php if (!empty($page_actions)): ?>
                    <div class="page-header-actions">
                        <?php echo $page_actions; // phpcs:ignore ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Page Content Slot -->
        <div id="page-body">
            <?php echo $page_content ?? ''; // phpcs:ignore ?>
        </div>
    </div>

    <?php wp_footer(); ?>
</body>
</html>