<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\BookSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\SettingSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_english_and_portuguese_translations_have_identical_coverage(): void
    {
        foreach (['auth', 'pagination', 'passwords', 'ui', 'validation'] as $file) {
            $english = array_keys(Arr::dot(require lang_path("en/{$file}.php")));
            $portuguese = array_keys(Arr::dot(require lang_path("pt_BR/{$file}.php")));

            sort($english);
            sort($portuguese);

            $this->assertSame($english, $portuguese, "Translation keys differ in {$file}.php");
        }
    }

    public function test_application_uses_trans_instead_of_short_translation_helper(): void
    {
        foreach ([app_path(), database_path(), resource_path(), base_path('routes')] as $directory) {
            foreach (File::allFiles($directory) as $file) {
                if (! in_array($file->getExtension(), ['php', 'ts', 'vue'], true)) {
                    continue;
                }

                $this->assertStringNotContainsString('_'.'_(', $file->getContents(), $file->getPathname());
            }
        }
    }

    public function test_user_can_switch_locale_and_receive_translations_through_inertia(): void
    {
        $user = User::factory()->create(['locale' => 'en']);

        $this->actingAs($user)->put(route('locale.update'), ['locale' => 'pt_BR'])
            ->assertRedirect()
            ->assertSessionHas('locale', 'pt_BR');

        $this->assertSame('pt_BR', $user->refresh()->preferredLocale());

        $this->get(route('home'))->assertInertia(
            fn (AssertableInertia $page) => $page
                ->where('i18n.locale', 'pt_BR')
                ->where('i18n.translations.brand.name', 'DraftCircle'),
        );

        $this->put(route('locale.update'), ['locale' => 'invalid'])->assertSessionHasErrors('locale');
    }

    public function test_seeders_generate_data_according_to_configured_locale(): void
    {
        App::setLocale('pt_BR');
        $this->seed([UserSeeder::class, CategorySeeder::class, SettingSeeder::class, BookSeeder::class]);

        $this->assertSame('Administrador do DraftCircle', User::query()->where('email', 'admin@draftcircle.local')->value('name'));
        $this->assertSame('Engenharia', Category::query()->where('slug', 'engineering')->value('name'));
        $this->assertSame('DraftCircle', Setting::query()->where('key', 'library_name')->value('value'));
        $this->assertSame('pt-BR', Book::query()->firstOrFail()->language);
        $this->assertFalse(Book::query()->whereNull('cover_path')->exists());
        $this->assertFalse(Book::query()->whereNull('isbn')->exists());

        Book::query()->forceDelete();
        App::setLocale('en');
        $this->seed([UserSeeder::class, CategorySeeder::class, SettingSeeder::class, BookSeeder::class]);

        $this->assertSame('DraftCircle Administrator', User::query()->where('email', 'admin@draftcircle.local')->value('name'));
        $this->assertSame('Engineering', Category::query()->where('slug', 'engineering')->value('name'));
        $this->assertSame('DraftCircle', Setting::query()->where('key', 'library_name')->value('value'));
        $this->assertSame('en', Book::query()->firstOrFail()->language);
    }
}
