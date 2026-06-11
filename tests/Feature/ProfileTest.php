<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_security_page_is_displayed_separately_from_personal_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/profile/security')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Profile/Security'));
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', trans('ui.profile.updated'))
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_profile_avatar_can_be_uploaded_and_replaces_the_old_local_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('avatars/old-avatar.jpg', 'old avatar');
        $user = User::factory()->create(['avatar' => 'avatars/old-avatar.jpg']);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => UploadedFile::fake()->image('avatar.jpg', 80, 80),
            ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile');

        Storage::disk('public')->assertMissing('avatars/old-avatar.jpg');
        $avatar = $user->refresh()->avatar;
        $this->assertNotNull($avatar);
        Storage::disk('public')->assertExists($avatar);
        $this->assertSame('/storage/'.$avatar, $user->avatar_url);
    }

    public function test_profile_avatar_can_be_uploaded_with_browser_method_spoofing(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/profile', [
                '_method' => 'patch',
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => UploadedFile::fake()->image('new-avatar.png', 80, 80),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $avatar = $user->refresh()->avatar;
        $this->assertNotNull($avatar);
        Storage::disk('public')->assertExists($avatar);
    }

    public function test_user_can_delete_their_account(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('avatars/account-avatar.jpg', 'avatar');
        $user = User::factory()->create();
        $user->update(['avatar' => 'avatars/account-avatar.jpg']);

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
        Storage::disk('public')->assertMissing('avatars/account-avatar.jpg');
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
