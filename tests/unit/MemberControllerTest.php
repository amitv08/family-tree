<?php
/**
 * Unit tests for MemberController
 */

use PHPUnit\Framework\TestCase;
use Controllers\MemberController;

class MemberControllerTest extends TestCase
{
    private $memberController;
    private $mockRepository;

    protected function setUp(): void
    {
        // Mock the repository
        $this->mockRepository = $this->createMock(\Repositories\MemberRepository::class);

        // Create controller instance with mocked dependencies
        $this->memberController = new MemberController($this->mockRepository);
    }

    /**
     * Test successful member creation
     */
    public function testCreateMemberSuccess()
    {
        $memberData = [
            'first_name' => 'John',
            'gender' => 'Male',
            'clan_id' => 1,
            'parent1_id' => 2
        ];

        $this->mockRepository
            ->expects($this->once())
            ->method('create')
            ->with($memberData)
            ->willReturn(1);

        $result = $this->memberController->create($memberData);

        $this->assertEquals(1, $result);
    }

    /**
     * Test member creation with missing required fields
     */
    public function testCreateMemberMissingRequiredFields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Gender is required');

        $memberData = [
            'first_name' => 'John',
            'clan_id' => 1
            // Missing gender
        ];

        $this->memberController->create($memberData);
    }

    /**
     * Test member creation with invalid gender
     */
    public function testCreateMemberInvalidGender()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid gender value');

        $memberData = [
            'first_name' => 'John',
            'gender' => 'Invalid',
            'clan_id' => 1
        ];

        $this->memberController->create($memberData);
    }

    /**
     * Test member retrieval
     */
    public function testGetMemberById()
    {
        $memberId = 1;
        $expectedMember = [
            'id' => 1,
            'first_name' => 'John',
            'gender' => 'Male'
        ];

        $this->mockRepository
            ->expects($this->once())
            ->method('findById')
            ->with($memberId)
            ->willReturn($expectedMember);

        $result = $this->memberController->getById($memberId);

        $this->assertEquals($expectedMember, $result);
    }

    /**
     * Test member not found
     */
    public function testGetMemberByIdNotFound()
    {
        $memberId = 999;

        $this->mockRepository
            ->expects($this->once())
            ->method('findById')
            ->with($memberId)
            ->willReturn(null);

        $result = $this->memberController->getById($memberId);

        $this->assertNull($result);
    }
}