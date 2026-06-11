<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Policies\AdminPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminPolicyTest extends TestCase
{
    use RefreshDatabase;

    private AdminPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new AdminPolicy;
    }

    public function test_student_cannot_access_admin(): void
    {
        $user = User::factory()->create(['role' => UserRole::Student]);

        $this->assertFalse($this->policy->access($user));
    }

    public function test_librarian_can_access_admin(): void
    {
        $user = User::factory()->create(['role' => UserRole::Librarian]);

        $this->assertTrue($this->policy->access($user));
    }

    public function test_admin_can_access_admin(): void
    {
        $user = User::factory()->create(['role' => UserRole::Admin]);

        $this->assertTrue($this->policy->access($user));
    }
}
