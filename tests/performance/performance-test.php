<?php
/**
 * Performance tests for Family Tree operations
 */

class PerformanceTest
{
    private $startTime;
    private $results = [];

    public function __construct()
    {
        $this->startTime = microtime(true);
    }

    private function log($message)
    {
        $elapsed = microtime(true) - $this->startTime;
        echo sprintf("[%.4f] %s\n", $elapsed, $message);
    }

    private function measure($operation, callable $callback, $iterations = 1)
    {
        $this->log("Starting: $operation");

        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $callback();
        }
        $end = microtime(true);

        $totalTime = $end - $start;
        $avgTime = $totalTime / $iterations;

        $this->results[$operation] = [
            'total_time' => $totalTime,
            'avg_time' => $avgTime,
            'iterations' => $iterations
        ];

        $this->log(sprintf(
            "Completed: $operation - Total: %.4fs, Avg: %.4fs per iteration",
            $totalTime,
            $avgTime
        ));

        return $avgTime;
    }

    public function runDatabasePerformanceTest()
    {
        global $wpdb;

        $this->log("=== Database Performance Test ===");

        // Test simple select
        $this->measure('Simple SELECT query', function() use ($wpdb) {
            $wpdb->get_var("SELECT 1");
        }, 100);

        // Test member insertion
        $this->measure('Member insertion', function() use ($wpdb) {
            $wpdb->insert(
                $wpdb->prefix . 'family_members',
                [
                    'first_name' => 'PerfTest_' . rand(1000, 9999),
                    'gender' => 'Male',
                    'clan_id' => 1,
                    'is_deleted' => 1 // Mark as test data
                ]
            );
        }, 50);

        // Test member selection with joins
        $this->measure('Complex member query', function() use ($wpdb) {
            $wpdb->get_results("
                SELECT m.*, c.name as clan_name, p1.first_name as father_name
                FROM {$wpdb->prefix}family_members m
                LEFT JOIN {$wpdb->prefix}family_clans c ON m.clan_id = c.id
                LEFT JOIN {$wpdb->prefix}family_members p1 ON m.parent1_id = p1.id
                WHERE m.is_deleted = 0
                LIMIT 10
            ");
        }, 20);

        // Clean up test data
        $wpdb->query("DELETE FROM {$wpdb->prefix}family_members WHERE is_deleted = 1");
    }

    public function runMemoryPerformanceTest()
    {
        $this->log("=== Memory Performance Test ===");

        $initialMemory = memory_get_usage();

        $this->measure('Memory usage test', function() {
            $members = [];
            for ($i = 0; $i < 1000; $i++) {
                $members[] = [
                    'id' => $i,
                    'first_name' => 'MemoryTest_' . $i,
                    'gender' => 'Male',
                    'clan_id' => rand(1, 10),
                    'parent1_id' => rand(1, 100),
                    'parent2_id' => rand(1, 100)
                ];
            }
            // Process members (simulate business logic)
            foreach ($members as $member) {
                $fullName = $member['first_name'] . ' ' . ($member['last_name'] ?? '');
                $hasParents = !empty($member['parent1_id']) || !empty($member['parent2_id']);
            }
            unset($members);
        }, 5);

        $finalMemory = memory_get_usage();
        $memoryUsed = $finalMemory - $initialMemory;

        $this->log(sprintf("Memory usage: %.2f MB", $memoryUsed / 1024 / 1024));
    }

    public function runAPITest()
    {
        $this->log("=== API Performance Test ===");

        $urls = [
            '/wp-json/wp/v2/users',
            '/wp-admin/admin-ajax.php?action=heartbeat',
            '/wp-content/plugins/family-tree/css/style.css'
        ];

        foreach ($urls as $url) {
            $this->measure("HTTP request to $url", function() use ($url) {
                $response = wp_remote_get(home_url($url));
                if (is_wp_error($response)) {
                    throw new Exception("Request failed: " . $response->get_error_message());
                }
            }, 10);
        }
    }

    public function generateReport()
    {
        $this->log("=== Performance Test Report ===");

        echo "\nPerformance Results:\n";
        echo str_repeat("=", 50) . "\n";

        foreach ($this->results as $operation => $data) {
            echo sprintf(
                "%-30s | Total: %6.4fs | Avg: %6.4fs | Iterations: %d\n",
                substr($operation, 0, 30),
                $data['total_time'],
                $data['avg_time'],
                $data['iterations']
            );
        }

        echo str_repeat("=", 50) . "\n";

        // Performance thresholds
        $thresholds = [
            'Simple SELECT query' => 0.01, // Should be < 10ms
            'Member insertion' => 0.05,    // Should be < 50ms
            'Complex member query' => 0.1  // Should be < 100ms
        ];

        $passed = 0;
        $failed = 0;

        foreach ($thresholds as $operation => $threshold) {
            if (isset($this->results[$operation])) {
                if ($this->results[$operation]['avg_time'] <= $threshold) {
                    $this->log("✓ $operation PASSED (under {$threshold}s threshold)");
                    $passed++;
                } else {
                    $this->log("✗ $operation FAILED (over {$threshold}s threshold)");
                    $failed++;
                }
            }
        }

        $this->log("Performance test summary: $passed passed, $failed failed");
    }

    public function runAllTests()
    {
        $this->runDatabasePerformanceTest();
        $this->runMemoryPerformanceTest();
        $this->runAPITest();
        $this->generateReport();
    }
}

// Run performance tests
if (defined('WP_CLI') && WP_CLI) {
    $perfTest = new PerformanceTest();
    $perfTest->runAllTests();
} else {
    echo "This script should be run via WP-CLI\n";
    echo "Usage: wp eval-file tests/performance/performance-test.php\n";
}