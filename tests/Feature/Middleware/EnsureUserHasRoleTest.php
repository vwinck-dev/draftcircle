<?php

declare(strict_types=1);

namespace Tests\Feature\Middleware;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EnsureUserHasRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_allows_librarian_to_access_admin_panel(): void
    {
        $librarian = User::factory()->create(['role' => UserRole::Librarian]);

        $this->actingAs($librarian)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_allows_admin_to_access_admin_panel(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_denies_student_access_to_admin_panel(): void
    {
        $student = User::factory()->create(['role' => UserRole::Student]);

        $this->actingAs($student)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_denies_unauthenticated_user_from_admin_panel(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_denies_librarian_from_admin_only_settings(): void
    {
        $librarian = User::factory()->create(['role' => UserRole::Librarian]);

        $this->actingAs($librarian)->get(route('admin.settings.index'))->assertForbidden();
    }

    public function test_allows_admin_to_access_admin_only_settings(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->get(route('admin.settings.index'))->assertOk();
    }
}
