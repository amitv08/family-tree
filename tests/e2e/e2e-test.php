<?php
/**
 * End-to-End tests for Family Tree plugin
 * Tests the complete user journey through the web interface
 */

class E2ETest
{
    private $baseUrl;
    private $results = ['passed' => 0, 'failed' => 0];

    public function __construct()
    {
        $this->baseUrl = home_url();
    }

    private function log($message, $type = 'info')
    {
        $timestamp = date('H:i:s');
        $prefix = "[$timestamp] ";

        switch ($type) {
            case 'pass':
                echo $prefix . "✓ PASS: $message\n";
                $this->results['passed']++;
                break;
            case 'fail':
                echo $prefix . "✗ FAIL: $message\n";
                $this->results['failed']++;
                break;
            default:
                echo $prefix . "ℹ INFO: $message\n";
        }
    }

    /**
     * Make HTTP request and check response
     */
    private function makeRequest($url, $method = 'GET', $data = [], $expectedCode = 200)
    {
        $args = [
            'method' => $method,
            'timeout' => 30,
            'redirection' => 5,
            'httpversion' => '1.1',
            'blocking' => true,
            'headers' => [],
            'body' => $data,
            'cookies' => []
        ];

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            $this->log("Request failed: " . $response->get_error_message(), 'fail');
            return false;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code !== $expectedCode) {
            $this->log("Unexpected response code: $code (expected $expectedCode) for $url", 'fail');
            return false;
        }

        return $response;
    }

    /**
     * Test plugin activation
     */
    public function testPluginActivation()
    {
        $this->log("Testing plugin activation");

        // Check if plugin is active
        if (is_plugin_active('family-tree/family-tree.php')) {
            $this->log("Plugin is active", 'pass');
        } else {
            $this->log("Plugin is not active", 'fail');
        }
    }

    /**
     * Test main plugin page loads
     */
    public function testMainPage()
    {
        $this->log("Testing main plugin page");

        $response = $this->makeRequest($this->baseUrl . '/family-tree');
        if ($response) {
            $body = wp_remote_retrieve_body($response);
            if (strpos($body, 'Family Tree') !== false) {
                $this->log("Main page loads correctly", 'pass');
            } else {
                $this->log("Main page content incorrect", 'fail');
            }
        }
    }

    /**
     * Test member creation form
     */
    public function testMemberCreationForm()
    {
        $this->log("Testing member creation form");

        $response = $this->makeRequest($this->baseUrl . '/add-member');
        if ($response) {
            $body = wp_remote_retrieve_body($response);

            // Check for required form elements
            $checks = [
                'first_name' => 'First Name field',
                'gender' => 'Gender field',
                'clan_id' => 'Clan selection',
                'submit' => 'Submit button'
            ];

            $allPassed = true;
            foreach ($checks as $field => $description) {
                if (strpos($body, $field) !== false) {
                    $this->log("$description present", 'pass');
                } else {
                    $this->log("$description missing", 'fail');
                    $allPassed = false;
                }
            }

            if ($allPassed) {
                $this->log("Member creation form complete", 'pass');
            }
        }
    }

    /**
     * Test member creation submission
     */
    public function testMemberCreation()
    {
        $this->log("Testing member creation submission");

        // Create test member data
        $memberData = [
            'first_name' => 'E2E Test User ' . time(),
            'gender' => 'Male',
            'clan_id' => 1,
            'action' => 'create_member',
            'nonce' => wp_create_nonce('create_member')
        ];

        $response = $this->makeRequest(
            $this->baseUrl . '/wp-admin/admin-ajax.php',
            'POST',
            $memberData,
            200
        );

        if ($response) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (isset($data['success']) && $data['success']) {
                $this->log("Member creation successful", 'pass');

                // Store member ID for cleanup
                if (isset($data['member_id'])) {
                    $this->testMemberId = $data['member_id'];
                }
            } else {
                $this->log("Member creation failed: " . ($data['message'] ?? 'Unknown error'), 'fail');
            }
        }
    }

    /**
     * Test member listing
     */
    public function testMemberListing()
    {
        $this->log("Testing member listing");

        $response = $this->makeRequest($this->baseUrl . '/browse-members');
        if ($response) {
            $body = wp_remote_retrieve_body($response);

            if (strpos($body, 'member') !== false || strpos($body, 'Member') !== false) {
                $this->log("Member listing page loads", 'pass');
            } else {
                $this->log("Member listing content incorrect", 'fail');
            }
        }
    }

    /**
     * Test AJAX endpoints
     */
    public function testAjaxEndpoints()
    {
        $this->log("Testing AJAX endpoints");

        $endpoints = [
            'get_clans' => ['action' => 'get_clans'],
            'get_members' => ['action' => 'get_members'],
            'heartbeat' => ['action' => 'heartbeat']
        ];

        foreach ($endpoints as $name => $data) {
            $data['nonce'] = wp_create_nonce($name);

            $response = $this->makeRequest(
                $this->baseUrl . '/wp-admin/admin-ajax.php',
                'POST',
                $data
            );

            if ($response) {
                $body = wp_remote_retrieve_body($response);
                $json = json_decode($body, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $this->log("$name endpoint returns valid JSON", 'pass');
                } else {
                    $this->log("$name endpoint returns invalid JSON", 'fail');
                }
            }
        }
    }

    /**
     * Test static assets loading
     */
    public function testStaticAssets()
    {
        $this->log("Testing static assets");

        $assets = [
            '/wp-content/plugins/family-tree/css/style.css',
            '/wp-content/plugins/family-tree/js/family-tree.js',
            '/wp-content/plugins/family-tree/js/members.js'
        ];

        foreach ($assets as $asset) {
            $response = $this->makeRequest($this->baseUrl . $asset);
            if ($response) {
                $contentType = wp_remote_retrieve_header($response, 'content-type');
                if (strpos($contentType, 'text/css') !== false ||
                    strpos($contentType, 'application/javascript') !== false) {
                    $this->log("Asset loads correctly: $asset", 'pass');
                } else {
                    $this->log("Asset has wrong content type: $asset", 'fail');
                }
            }
        }
    }

    /**
     * Test error handling
     */
    public function testErrorHandling()
    {
        $this->log("Testing error handling");

        // Test invalid member ID
        $response = $this->makeRequest($this->baseUrl . '/edit-member?id=999999', 'GET', [], 404);
        if ($response) {
            $this->log("Invalid member ID handled correctly", 'pass');
        }

        // Test invalid form submission
        $invalidData = [
            'first_name' => '', // Empty required field
            'gender' => 'Invalid',
            'action' => 'create_member'
        ];

        $response = $this->makeRequest(
            $this->baseUrl . '/wp-admin/admin-ajax.php',
            'POST',
            $invalidData
        );

        if ($response) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (isset($data['success']) && !$data['success']) {
                $this->log("Invalid form data handled correctly", 'pass');
            } else {
                $this->log("Invalid form data not properly validated", 'fail');
            }
        }
    }

    /**
     * Test performance
     */
    public function testPerformance()
    {
        $this->log("Testing page load performance");

        $pages = [
            '/family-tree',
            '/add-member',
            '/browse-members'
        ];

        foreach ($pages as $page) {
            $start = microtime(true);
            $response = $this->makeRequest($this->baseUrl . $page);
            $end = microtime(true);

            $loadTime = $end - $start;

            if ($loadTime < 2.0) { // 2 second threshold
                $this->log(sprintf("Page %s loads in %.2fs", $page, $loadTime), 'pass');
            } else {
                $this->log(sprintf("Page %s loads slowly: %.2fs", $page, $loadTime), 'fail');
            }
        }
    }

    /**
     * Clean up test data
     */
    public function cleanup()
    {
        $this->log("Cleaning up test data");

        if (isset($this->testMemberId)) {
            global $wpdb;
            $wpdb->delete(
                $wpdb->prefix . 'family_members',
                ['id' => $this->testMemberId],
                ['%d']
            );
            $this->log("Test member cleaned up", 'pass');
        }
    }

    public function generateReport()
    {
        $this->log("=== E2E Test Report ===");
        echo "\nE2E Test Results:\n";
        echo str_repeat("=", 30) . "\n";
        echo "Passed: {$this->results['passed']}\n";
        echo "Failed: {$this->results['failed']}\n";
        echo str_repeat("=", 30) . "\n";

        $total = $this->results['passed'] + $this->results['failed'];
        $successRate = $total > 0 ? ($this->results['passed'] / $total) * 100 : 0;

        if ($this->results['failed'] === 0) {
            $this->log("All E2E tests passed!", 'pass');
        } else {
            $this->log(sprintf("%.1f%% success rate (%d/%d tests passed)",
                $successRate, $this->results['passed'], $total), 'fail');
        }
    }

    public function runAllTests()
    {
        $this->log("Starting Family Tree E2E Tests");

        try {
            $this->testPluginActivation();
            $this->testMainPage();
            $this->testMemberCreationForm();
            $this->testMemberCreation();
            $this->testMemberListing();
            $this->testAjaxEndpoints();
            $this->testStaticAssets();
            $this->testErrorHandling();
            $this->testPerformance();

            $this->cleanup();
        } catch (Exception $e) {
            $this->log("Test execution failed: " . $e->getMessage(), 'fail');
        }

        $this->generateReport();
    }
}

// Run E2E tests
if (defined('WP_CLI') && WP_CLI) {
    $e2eTest = new E2ETest();
    $e2eTest->runAllTests();
} else {
    echo "This script should be run via WP-CLI\n";
    echo "Usage: wp eval-file tests/e2e/e2e-test.php\n";
}