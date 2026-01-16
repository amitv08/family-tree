<?php
/**
 * Integration tests for database operations
 */

use PHPUnit\Framework\TestCase;

class DatabaseIntegrationTest extends TestCase
{
    private $wpdb;

    protected function setUp(): void
    {
        global $wpdb;
        $this->wpdb = $wpdb;

        // Create test tables if they don't exist
        $this->createTestTables();
    }

    protected function tearDown(): void
    {
        // Clean up test data
        $this->cleanTestData();
    }

    private function createTestTables()
    {
        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}family_members_test (
            id int(11) NOT NULL AUTO_INCREMENT,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) DEFAULT NULL,
            middle_name varchar(100) DEFAULT NULL,
            gender enum('Male','Female','Other') NOT NULL,
            clan_id int(11) DEFAULT NULL,
            parent1_id int(11) DEFAULT NULL,
            parent2_id int(11) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private function cleanTestData()
    {
        $this->wpdb->query("DROP TABLE IF EXISTS {$this->wpdb->prefix}family_members_test");
    }

    /**
     * Test member insertion
     */
    public function testInsertMember()
    {
        $memberData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'middle_name' => 'Smith',
            'gender' => 'Male',
            'clan_id' => 1,
            'parent1_id' => 2
        ];

        $result = $this->wpdb->insert(
            "{$this->wpdb->prefix}family_members_test",
            $memberData,
            ['%s', '%s', '%s', '%s', '%d', '%d']
        );

        $this->assertEquals(1, $result);
        $this->assertGreaterThan(0, $this->wpdb->insert_id);
    }

    /**
     * Test member selection
     */
    public function testSelectMember()
    {
        // Insert test data
        $this->wpdb->insert(
            "{$this->wpdb->prefix}family_members_test",
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'gender' => 'Female',
                'clan_id' => 1
            ],
            ['%s', '%s', '%s', '%d']
        );

        $memberId = $this->wpdb->insert_id;

        // Select the member
        $member = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->wpdb->prefix}family_members_test WHERE id = %d",
                $memberId
            ),
            ARRAY_A
        );

        $this->assertNotNull($member);
        $this->assertEquals('Jane', $member['first_name']);
        $this->assertEquals('Smith', $member['last_name']);
        $this->assertEquals('Female', $member['gender']);
    }

    /**
     * Test member update
     */
    public function testUpdateMember()
    {
        // Insert test data
        $this->wpdb->insert(
            "{$this->wpdb->prefix}family_members_test",
            [
                'first_name' => 'Bob',
                'gender' => 'Male'
            ],
            ['%s', '%s']
        );

        $memberId = $this->wpdb->insert_id;

        // Update the member
        $result = $this->wpdb->update(
            "{$this->wpdb->prefix}family_members_test",
            ['first_name' => 'Robert'],
            ['id' => $memberId],
            ['%s'],
            ['%d']
        );

        $this->assertEquals(1, $result);

        // Verify update
        $member = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT first_name FROM {$this->wpdb->prefix}family_members_test WHERE id = %d",
                $memberId
            ),
            ARRAY_A
        );

        $this->assertEquals('Robert', $member['first_name']);
    }

    /**
     * Test member deletion
     */
    public function testDeleteMember()
    {
        // Insert test data
        $this->wpdb->insert(
            "{$this->wpdb->prefix}family_members_test",
            [
                'first_name' => 'Delete',
                'gender' => 'Male'
            ],
            ['%s', '%s']
        );

        $memberId = $this->wpdb->insert_id;

        // Delete the member
        $result = $this->wpdb->delete(
            "{$this->wpdb->prefix}family_members_test",
            ['id' => $memberId],
            ['%d']
        );

        $this->assertEquals(1, $result);

        // Verify deletion
        $member = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->wpdb->prefix}family_members_test WHERE id = %d",
                $memberId
            )
        );

        $this->assertNull($member);
    }

    /**
     * Test database connection
     */
    public function testDatabaseConnection()
    {
        $this->assertTrue($this->wpdb->check_connection());
    }

    /**
     * Test SQL injection prevention
     */
    public function testSqlInjectionPrevention()
    {
        $maliciousInput = "'; DROP TABLE {$this->wpdb->prefix}family_members_test; --";

        // This should not execute the DROP statement
        $result = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->wpdb->prefix}family_members_test WHERE first_name = %s",
                $maliciousInput
            )
        );

        // Table should still exist
        $tableExists = $this->wpdb->get_var(
            "SHOW TABLES LIKE '{$this->wpdb->prefix}family_members_test'"
        );

        $this->assertNotNull($tableExists);
    }
}