<?php
/**
 * Feature Flags Configuration
 * Enable/disable features for gradual rollouts and A/B testing
 */

namespace FamilyTree\Config;

class FeatureFlags {
    /**
     * Feature flag definitions
     * Format: feature_name => default_value
     */
    private const FEATURES = [
        // Core features
        'family_tree_visualization' => true,
        'advanced_search' => true,
        'bulk_operations' => false,

        // Experimental features
        'ai_relationship_detection' => false,
        'social_sharing' => false,
        'export_formats' => ['pdf', 'csv'], // Can be array for multi-value flags

        // Performance features
        'lazy_loading' => true,
        'caching_optimization' => true,

        // Security features
        'enhanced_audit_logging' => true,
        'two_factor_auth' => false,
    ];

    /**
     * Environment-specific overrides
     */
    private const ENVIRONMENT_OVERRIDES = [
        'development' => [
            'ai_relationship_detection' => true,
            'two_factor_auth' => false,
        ],
        'staging' => [
            'social_sharing' => true,
            'two_factor_auth' => false,
        ],
        'production' => [
            'ai_relationship_detection' => false,
            'two_factor_auth' => true,
        ],
    ];

    /**
     * User-specific overrides (for A/B testing)
     */
    private static $userOverrides = [];

    /**
     * Check if a feature is enabled
     *
     * @param string $feature Feature name
     * @param int|null $userId User ID for user-specific flags
     * @return mixed Feature value or false if not found
     */
    public static function isEnabled(string $feature, ?int $userId = null) {
        // Check user-specific overrides first
        if ($userId && isset(self::$userOverrides[$userId][$feature])) {
            return self::$userOverrides[$userId][$feature];
        }

        // Check environment overrides
        $environment = self::getEnvironment();
        if (isset(self::ENVIRONMENT_OVERRIDES[$environment][$feature])) {
            return self::ENVIRONMENT_OVERRIDES[$environment][$feature];
        }

        // Return default value
        return self::FEATURES[$feature] ?? false;
    }

    /**
     * Set user-specific feature override
     *
     * @param int $userId User ID
     * @param string $feature Feature name
     * @param mixed $value Feature value
     */
    public static function setUserOverride(int $userId, string $feature, $value): void {
        if (!isset(self::$userOverrides[$userId])) {
            self::$userOverrides[$userId] = [];
        }
        self::$userOverrides[$userId][$feature] = $value;
    }

    /**
     * Get all enabled features for a user
     *
     * @param int|null $userId User ID
     * @return array Enabled features
     */
    public static function getEnabledFeatures(?int $userId = null): array {
        $enabled = [];
        foreach (self::FEATURES as $feature => $default) {
            if (self::isEnabled($feature, $userId)) {
                $enabled[$feature] = self::isEnabled($feature, $userId);
            }
        }
        return $enabled;
    }

    /**
     * Get current environment
     *
     * @return string Environment name
     */
    private static function getEnvironment(): string {
        if (defined('WP_ENV') && WP_ENV) {
            return WP_ENV;
        }

        // Fallback detection
        if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
            if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
                return 'development';
            }
            if (strpos($host, 'staging') !== false) {
                return 'staging';
            }
        }

        return 'production';
    }

    /**
     * Get feature flag value (for non-boolean flags)
     *
     * @param string $feature Feature name
     * @param int|null $userId User ID
     * @return mixed Feature value
     */
    public static function getValue(string $feature, ?int $userId = null) {
        return self::isEnabled($feature, $userId);
    }
}

// Usage examples:
/*
// Check if feature is enabled
if (FeatureFlags::isEnabled('ai_relationship_detection')) {
    // Show AI features
}

// Check with user-specific override
if (FeatureFlags::isEnabled('social_sharing', get_current_user_id())) {
    // Show social sharing buttons
}

// Get feature value (for non-boolean flags)
$exportFormats = FeatureFlags::getValue('export_formats');
if (in_array('pdf', $exportFormats)) {
    // Enable PDF export
}
*/