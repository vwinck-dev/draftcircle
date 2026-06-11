<?php

declare(strict_types=1);

namespace Tests\Feature\Requests;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SettingsRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_settings_with_valid_data(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'group' => 'general',
            'settings' => ['blocked_comment_words' => 'spam,badword'],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('settings', ['key' => 'blocked_comment_words', 'value' => 'spam,badword']);
    }

    public function test_librarian_is_forbidden_from_updating_settings(): void
    {
        $librarian = User::factory()->create(['role' => UserRole::Librarian]);

        $response = $this->actingAs($librarian)->put(route('admin.settings.update'), [
            'group' => 'general',
            'settings' => ['blocked_comment_words' => 'spam'],
        ]);

        $response->assertForbidden();
    }

    public function test_student_is_forbidden_from_updating_settings(): void
    {
        $student = User::factory()->create(['role' => UserRole::Student]);

        $response = $this->actingAs($student)->put(route('admin.settings.update'), [
            'group' => 'general',
            'settings' => ['key' => 'value'],
        ]);

        $response->assertForbidden();
    }

    public function test_group_is_required(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'settings' => ['key' => 'value'],
        ]);

        $response->assertSessionHasErrors('group');
    }

    public function test_group_must_be_valid_enum_value(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'group' => 'invalid-group',
            'settings' => ['key' => 'value'],
        ]);

        $response->assertSessionHasErrors('group');
    }

    public function test_settings_must_be_an_array(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'group' => 'general',
            'settings' => 'not-an-array',
        ]);

        $response->assertSessionHasErrors('settings');
    }

    public function test_settings_values_can_be_null(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'group' => 'general',
            'settings' => ['some_key' => null],
        ]);

        $response->assertSessionMissing('errors');
    }
}
