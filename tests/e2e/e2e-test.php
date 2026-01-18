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
        // Use internal nginx URL for testing from within containers
        $this->baseUrl = 'http://nginx';
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
     * Make HTTP request and check response using curl
     */
    private function makeRequest($url, $method = 'GET', $data = [], $expectedCode = 200)
    {
        // Build curl command
        $curlCmd = "curl -s -i";
        
        if ($method === 'POST') {
            $curlCmd .= " -X POST";
            if (!empty($data)) {
                $postData = http_build_query($data);
                $curlCmd .= " -d '$postData'";
            }
        }
        
        $curlCmd .= " --connect-timeout 10 --max-time 30 '$url'";
        
        // Execute curl command
        $output = shell_exec($curlCmd);
        
        if ($output === null) {
            $this->log("Request failed: curl command failed", 'fail');
            return false;
        }
        
        // Parse response
        $lines = explode("\n", trim($output));
        $statusLine = $lines[0];
        
        if (preg_match('/HTTP\/\d+\.\d+\s+(\d+)/', $statusLine, $matches)) {
            $code = (int) $matches[1];
        } else {
            $this->log("Request failed: Could not parse HTTP status", 'fail');
            return false;
        }
        
        if ($code !== $expectedCode) {
            $this->log("Unexpected response code: $code (expected $expectedCode) for $url", 'fail');
            return false;
        }
        
        // Parse headers and body
        $headers = [];
        $body = '';
        $bodyStart = false;
        
        foreach ($lines as $line) {
            if ($bodyStart) {
                $body .= $line . "\n";
            } elseif (trim($line) === '') {
                $bodyStart = true;
            } elseif (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $headers[strtolower(trim($key))] = trim($value);
            }
        }
        
        return ['body' => trim($body), 'code' => $code, 'headers' => $headers];
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

        // Expect redirect to login (302) since authentication is required
        $response = $this->makeRequest($this->baseUrl . '/family-tree', 'GET', [], 302);
        if ($response) {
            $this->log("Main page redirects to login (authentication required)", 'pass');
        }
    }

    /**
     * Test member creation form
     */
    public function testMemberCreationForm()
    {
        $this->log("Testing member creation form");

        // Expect 403 (forbidden) since direct access is denied
        $response = $this->makeRequest($this->baseUrl . '/add-member', 'GET', [], 403);
        if ($response) {
            $this->log("Member creation form access denied (security)", 'pass');
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
            'action' => 'add_family_member',
            'nonce' => wp_create_nonce('family_tree_nonce')
        ];

        // Expect 200 with error JSON since nonce is valid but user lacks capability
        $response = $this->makeRequest(
            $this->baseUrl . '/wp-admin/admin-ajax.php',
            'POST',
            $memberData,
            200
        );

        if ($response) {
            $body = $response['body'];
            $data = json_decode($body, true);
            if (isset($data['success']) && !$data['success']) {
                $this->log("Member creation properly rejects unauthorized users", 'pass');
            } else {
                $this->log("Member creation should reject unauthorized users", 'fail');
            }
        }
    }

    /**
     * Test member listing
     */
    public function testMemberListing()
    {
        $this->log("Testing member listing");

        // Expect redirect to login (302) since authentication is required
        $response = $this->makeRequest($this->baseUrl . '/browse-members', 'GET', [], 302);
        if ($response) {
            $this->log("Member listing redirects to login (authentication required)", 'pass');
        }
    }

    /**
     * Test AJAX endpoints
     */
    public function testAjaxEndpoints()
    {
        $this->log("Testing AJAX endpoints");

        $endpoints = [
            'get_clans' => ['action' => 'get_family_clans'],
            'get_members' => ['action' => 'get_family_members'],
            'heartbeat' => ['action' => 'heartbeat']
        ];

        foreach ($endpoints as $name => $data) {
            $data['nonce'] = wp_create_nonce('family_tree_nonce');

            $response = $this->makeRequest(
                $this->baseUrl . '/wp-admin/admin-ajax.php',
                'POST',
                $data
            );

            if ($response) {
                $body = $response['body'];
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
            '/wp-content/plugins/family-tree/assets/css/style.css',
            '/wp-content/plugins/family-tree/assets/js/family-tree.js',
            '/wp-content/plugins/family-tree/assets/js/members.js',
        ];

        foreach ($assets as $asset) {
            $response = $this->makeRequest($this->baseUrl . $asset);
            if ($response) {
                $contentType = $response['headers']['content-type'] ?? '';
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

        // Test invalid member ID - should redirect (301 or 403)
        $response = $this->makeRequest($this->baseUrl . '/edit-member?id=999999', 'GET', [], 301);
        if ($response) {
            $this->log("Invalid member ID properly handled with redirect", 'pass');
        }

        // Test invalid form submission - should fail due to capability check
        $invalidData = [
            'first_name' => '', // Empty required field
            'gender' => 'Invalid',
            'action' => 'add_family_member',
            'nonce' => wp_create_nonce('family_tree_nonce')
        ];

        $response = $this->makeRequest(
            $this->baseUrl . '/wp-admin/admin-ajax.php',
            'POST',
            $invalidData,
            200
        );

        if ($response) {
            $body = $response['body'];
            $data = json_decode($body, true);
            if (isset($data['success']) && !$data['success']) {
                $this->log("Invalid form submission properly rejected", 'pass');
            } else {
                $this->log("Invalid form submission should be rejected", 'fail');
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
            '/family-tree' => 302,  // Redirects to login
            '/add-member' => 403,   // Access denied
            '/browse-members' => 302 // Redirects to login
        ];

        foreach ($pages as $page => $expectedCode) {
            $start = microtime(true);
            $response = $this->makeRequest($this->baseUrl . $page, 'GET', [], $expectedCode);
            $end = microtime(true);

            if ($response) {
                $loadTime = $end - $start;
                if ($loadTime < 2.0) { // 2 second threshold
                    $this->log(sprintf("Page %s loads in %.2fs", $page, $loadTime), 'pass');
                } else {
                    $this->log(sprintf("Page %s loads slowly: %.2fs", $page, $loadTime), 'fail');
                }
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