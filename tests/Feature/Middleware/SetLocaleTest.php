<?php

declare(strict_types=1);

namespace Tests\Feature\Middleware;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

final class SetLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_uses_default_locale_for_guest(): void
    {
        config(['app.locale' => 'en']);

        $this->get(route('home'));

        $this->assertSame('en', App::getLocale());
    }

    public function test_uses_user_preferred_locale(): void
    {
        $user = User::factory()->create(['locale' => 'pt_BR']);

        $this->actingAs($user)->get(route('home'));

        $this->assertSame('pt_BR', App::getLocale());
    }

    public function test_session_locale_overrides_user_preference(): void
    {
        $user = User::factory()->create(['locale' => 'pt_BR']);

        $this->actingAs($user)
            ->withSession(['locale' => 'en'])
            ->get(route('home'));

        $this->assertSame('en', App::getLocale());
    }

    public function test_falls_back_to_default_for_unsupported_locale(): void
    {
        config(['app.locale' => 'en']);
        $user = User::factory()->create(['locale' => 'fr']);

        $this->actingAs($user)->get(route('home'));

        $this->assertSame('en', App::getLocale());
    }

    public function test_supports_portuguese_locale(): void
    {
        $user = User::factory()->create(['locale' => 'pt_BR']);

        $this->actingAs($user)->get(route('home'));

        $this->assertSame('pt_BR', App::getLocale());
    }
}
