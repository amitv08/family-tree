<?php
/**
 * Unit tests for MemberController
 */

use PHPUnit\Framework\TestCase;
use FamilyTree\Controllers\MemberController;
use FamilyTree\Repositories\MemberRepository;
use FamilyTree\Repositories\MarriageRepository;

class MemberControllerTest extends TestCase
{
    private $memberController;
    private $mockMemberRepository;
    private $mockMarriageRepository;

    protected function setUp(): void
    {
        // Mock the repositories
        $this->mockMemberRepository = $this->createMock(MemberRepository::class);
        $this->mockMarriageRepository = $this->createMock(MarriageRepository::class);

        // Create controller instance with mocked dependencies
        $this->memberController = new MemberController($this->mockMemberRepository, $this->mockMarriageRepository);
    }

    /**
     * Test successful member creation
     */
    public function testAddMemberSuccess()
    {
        $memberData = [
            'first_name' => 'John',
            'gender' => 'Male',
            'clan_id' => 1,
            'parent1_id' => 2
        ];

        // Mock the repository add method
        $this->mockMemberRepository
            ->expects($this->once())
            ->method('add')
            ->with($memberData)
            ->willReturn(1);

        // Create a partial mock of the controller
        $controller = $this->getMockBuilder(MemberController::class)
            ->setConstructorArgs([$this->mockMemberRepository, $this->mockMarriageRepository])
            ->onlyMethods(['verify_nonce', 'verify_capability', 'success', 'error'])
            ->getMock();

        $controller->expects($this->once())
            ->method('verify_nonce');

        $controller->expects($this->once())
            ->method('verify_capability')
            ->with('edit_family_members');

        $controller->expects($this->once())
            ->method('success')
            ->with(['message' => 'Member added successfully', 'member_id' => 1]);

        // Set up POST data
        $_POST = $memberData;

        $controller->add();
    }

    /**
     * Test member creation with missing required fields
     */
    public function testAddMemberMissingRequiredFields()
    {
        $memberData = [
            'first_name' => 'John'
            // Missing required fields like gender
        ];

        // Create a partial mock of the controller
        $controller = $this->getMockBuilder(MemberController::class)
            ->setConstructorArgs([$this->mockMemberRepository, $this->mockMarriageRepository])
            ->onlyMethods(['verify_nonce', 'verify_capability', 'error'])
            ->getMock();

        $controller->expects($this->once())
            ->method('verify_nonce');

        $controller->expects($this->once())
            ->method('verify_capability')
            ->with('edit_family_members');

        $controller->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Gender is required'));

        // Set up POST data
        $_POST = $memberData;

        $controller->add();
    }

    /**
     * Test member creation with invalid gender
     */
    public function testAddMemberInvalidGender()
    {
        $memberData = [
            'first_name' => 'John',
            'gender' => 'InvalidGender',
            'clan_id' => 1
        ];

        // Create a partial mock of the controller
        $controller = $this->getMockBuilder(MemberController::class)
            ->setConstructorArgs([$this->mockMemberRepository, $this->mockMarriageRepository])
            ->onlyMethods(['verify_nonce', 'verify_capability', 'error'])
            ->getMock();

        $controller->expects($this->once())
            ->method('verify_nonce');

        $controller->expects($this->once())
            ->method('verify_capability')
            ->with('edit_family_members');

        $controller->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Invalid gender'));

        // Set up POST data
        $_POST = $memberData;

        $controller->add();
    }
}