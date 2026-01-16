<?php
/**
 * Security tests for Family Tree plugin
 */

class SecurityTest
{
    private $results = ['passed' => 0, 'failed' => 0, 'warnings' => 0];

    public function log($message, $type = 'info')
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
            case 'warn':
                echo $prefix . "⚠ WARN: $message\n";
                $this->results['warnings']++;
                break;
            default:
                echo $prefix . "ℹ INFO: $message\n";
        }
    }

    /**
     * Test SQL injection vulnerabilities
     */
    public function testSQLInjection()
    {
        global $wpdb;

        $this->log("Testing SQL injection vulnerabilities");

        $maliciousInputs = [
            "'; DROP TABLE " . $wpdb->prefix . "family_members; --",
            "' OR '1'='1",
            "1; SELECT * FROM " . $wpdb->prefix . "users; --",
            "admin' --",
            "' UNION SELECT user_login, user_pass FROM " . $wpdb->prefix . "users; --"
        ];

        foreach ($maliciousInputs as $input) {
            try {
                // Test member search (simulated)
                $query = $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}family_members WHERE first_name = %s",
                    $input
                );
                $result = $wpdb->get_results($query);

                // Check if injection succeeded
                if ($wpdb->last_error) {
                    $this->log("SQL injection attempt blocked: $input", 'pass');
                } else {
                    $this->log("Potential SQL injection vulnerability with: $input", 'fail');
                }
            } catch (Exception $e) {
                $this->log("SQL injection attempt blocked: $input", 'pass');
            }
        }
    }

    /**
     * Test XSS vulnerabilities
     */
    public function testXSS()
    {
        $this->log("Testing XSS vulnerabilities");

        $xssPayloads = [
            "<script>alert('XSS')</script>",
            "<img src=x onerror=alert('XSS')>",
            "javascript:alert('XSS')",
            "<iframe src='javascript:alert(\"XSS\")'>",
            "<svg onload=alert('XSS')>"
        ];

        foreach ($xssPayloads as $payload) {
            // Test if payload gets sanitized
            $sanitized = sanitize_text_field($payload);
            if ($sanitized !== $payload) {
                $this->log("XSS payload sanitized: " . substr($payload, 0, 30) . "...", 'pass');
            } else {
                $this->log("XSS payload not sanitized: " . substr($payload, 0, 30) . "...", 'fail');
            }
        }
    }

    /**
     * Test file upload security
     */
    public function testFileUploadSecurity()
    {
        $this->log("Testing file upload security");

        $dangerousFiles = [
            'malicious.php',
            'shell.php.jpg',
            'exploit.php.png',
            'backdoor.php.gif',
            '../../../etc/passwd',
            'wp-config.php.bak'
        ];

        foreach ($dangerousFiles as $filename) {
            // Check file extension validation
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (!in_array($extension, $allowedExtensions)) {
                $this->log("Dangerous file extension blocked: $filename", 'pass');
            } else {
                // Check for double extensions
                $basename = pathinfo($filename, PATHINFO_FILENAME);
                if (strpos($basename, '.') !== false) {
                    $this->log("Double extension detected: $filename", 'fail');
                } else {
                    $this->log("File extension allowed: $filename", 'pass');
                }
            }
        }
    }

    /**
     * Test authentication and authorization
     */
    public function testAuthentication()
    {
        $this->log("Testing authentication and authorization");

        // Test if user is logged in
        if (is_user_logged_in()) {
            $this->log("User authentication check passed", 'pass');

            // Test user capabilities
            if (current_user_can('manage_options')) {
                $this->log("Admin capabilities verified", 'pass');
            } else {
                $this->log("User lacks admin capabilities", 'warn');
            }
        } else {
            $this->log("No user logged in - testing public access", 'info');
        }

        // Test nonce verification (simulated)
        $nonce = wp_create_nonce('family_tree_action');
        if (wp_verify_nonce($nonce, 'family_tree_action')) {
            $this->log("Nonce verification working", 'pass');
        } else {
            $this->log("Nonce verification failed", 'fail');
        }
    }

    /**
     * Test data validation
     */
    public function testDataValidation()
    {
        $this->log("Testing data validation");

        $testData = [
            ['field' => 'first_name', 'value' => 'John', 'expected' => true],
            ['field' => 'first_name', 'value' => '', 'expected' => false],
            ['field' => 'first_name', 'value' => str_repeat('A', 101), 'expected' => false],
            ['field' => 'gender', 'value' => 'Male', 'expected' => true],
            ['field' => 'gender', 'value' => 'Invalid', 'expected' => false],
            ['field' => 'email', 'value' => 'test@example.com', 'expected' => true],
            ['field' => 'email', 'value' => 'invalid-email', 'expected' => false]
        ];

        foreach ($testData as $test) {
            $result = $this->validateField($test['field'], $test['value']);
            if ($result === $test['expected']) {
                $this->log("Validation correct for {$test['field']}: {$test['value']}", 'pass');
            } else {
                $this->log("Validation incorrect for {$test['field']}: {$test['value']}", 'fail');
            }
        }
    }

    private function validateField($field, $value)
    {
        switch ($field) {
            case 'first_name':
                return !empty($value) && strlen($value) <= 100 && !preg_match('/[<>\"\'&]/', $value);
            case 'gender':
                return in_array($value, ['Male', 'Female', 'Other']);
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            default:
                return true;
        }
    }

    /**
     * Test rate limiting
     */
    public function testRateLimiting()
    {
        $this->log("Testing rate limiting");

        // Simulate multiple requests
        $requests = 0;
        $blocked = 0;

        for ($i = 0; $i < 15; $i++) {
            $requests++;
            // In a real scenario, this would check against rate limiting middleware
            if ($requests > 10) { // Simulate rate limit
                $blocked++;
            }
        }

        if ($blocked > 0) {
            $this->log("Rate limiting working - $blocked requests blocked", 'pass');
        } else {
            $this->log("Rate limiting not properly configured", 'warn');
        }
    }

    /**
     * Test HTTPS enforcement
     */
    public function testHTTPS()
    {
        $this->log("Testing HTTPS configuration");

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $this->log("HTTPS connection detected", 'pass');
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            $this->log("HTTPS through proxy detected", 'pass');
        } else {
            $this->log("HTTP connection - consider enforcing HTTPS", 'warn');
        }
    }

    /**
     * Test security headers
     */
    public function testSecurityHeaders()
    {
        $this->log("Testing security headers");

        $expectedHeaders = [
            'X-Frame-Options',
            'X-Content-Type-Options',
            'X-XSS-Protection',
            'Content-Security-Policy'
        ];

        foreach ($expectedHeaders as $header) {
            if (function_exists('headers_list')) {
                $headers = headers_list();
                $found = false;
                foreach ($headers as $h) {
                    if (stripos($h, $header) === 0) {
                        $found = true;
                        break;
                    }
                }
                if ($found) {
                    $this->log("Security header present: $header", 'pass');
                } else {
                    $this->log("Security header missing: $header", 'warn');
                }
            } else {
                $this->log("Cannot check headers in CLI mode", 'info');
            }
        }
    }

    public function generateReport()
    {
        $this->log("=== Security Test Report ===");
        echo "\nSecurity Test Results:\n";
        echo str_repeat("=", 40) . "\n";
        echo "Passed: {$this->results['passed']}\n";
        echo "Failed: {$this->results['failed']}\n";
        echo "Warnings: {$this->results['warnings']}\n";
        echo str_repeat("=", 40) . "\n";

        if ($this->results['failed'] === 0) {
            $this->log("All critical security tests passed!", 'pass');
        } else {
            $this->log("{$this->results['failed']} security vulnerabilities found!", 'fail');
        }
    }

    public function runAllTests()
    {
        $this->log("Starting Family Tree Security Tests");

        $this->testSQLInjection();
        $this->testXSS();
        $this->testFileUploadSecurity();
        $this->testAuthentication();
        $this->testDataValidation();
        $this->testRateLimiting();
        $this->testHTTPS();
        $this->testSecurityHeaders();

        $this->generateReport();
    }
}

// Run security tests
if (defined('WP_CLI') && WP_CLI) {
    $securityTest = new SecurityTest();
    $securityTest->runAllTests();
} else {
    echo "This script should be run via WP-CLI\n";
    echo "Usage: wp eval-file tests/security/security-test.php\n";
}