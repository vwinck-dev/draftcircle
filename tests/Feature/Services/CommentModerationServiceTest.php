<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Enums\SettingGroup;
use App\Models\Setting;
use App\Services\CommentModerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CommentModerationServiceTest extends TestCase
{
    use RefreshDatabase;

    private CommentModerationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CommentModerationService::class);
    }

    public function test_returns_null_for_clean_comment(): void
    {
        $this->setBlockedWords('spam,badword');

        $this->assertNull($this->service->blockedWord('This is a perfectly fine comment.'));
    }

    public function test_detects_exact_blocked_word(): void
    {
        $this->setBlockedWords('badword');

        $this->assertSame('badword', $this->service->blockedWord('This contains badword in it.'));
    }

    public function test_detects_word_at_start_of_string(): void
    {
        $this->setBlockedWords('spam');

        $this->assertSame('spam', $this->service->blockedWord('spam is here'));
    }

    public function test_detects_word_at_end_of_string(): void
    {
        $this->setBlockedWords('spam');

        $this->assertSame('spam', $this->service->blockedWord('this is spam'));
    }

    public function test_is_case_insensitive(): void
    {
        $this->setBlockedWords('badword');

        $this->assertSame('badword', $this->service->blockedWord('This has BADWORD inside.'));
    }

    public function test_does_not_match_word_as_substring(): void
    {
        $this->setBlockedWords('bad');

        $this->assertNull($this->service->blockedWord('badminton is a sport'));
    }

    public function test_detects_blocked_phrase_with_spaces(): void
    {
        $this->setBlockedWords('hate speech');

        $this->assertSame('hate speech', $this->service->blockedWord('This is hate speech okay?'));
    }

    public function test_detects_blocked_phrase_with_extra_whitespace(): void
    {
        $this->setBlockedWords('hate speech');

        $this->assertSame('hate speech', $this->service->blockedWord('This is hate  speech okay?'));
    }

    public function test_supports_newline_separated_blocklist(): void
    {
        $this->setBlockedWords("spam\nbadword");

        $this->assertSame('badword', $this->service->blockedWord('Contains badword here.'));
    }

    public function test_supports_comma_separated_blocklist(): void
    {
        $this->setBlockedWords('spam,badword,ugly');

        $this->assertSame('spam', $this->service->blockedWord('spam alert'));
    }

    public function test_returns_first_matching_word(): void
    {
        $this->setBlockedWords('alpha,beta');

        $result = $this->service->blockedWord('alpha and beta are here');

        $this->assertSame('alpha', $result);
    }

    public function test_returns_null_when_blocked_list_is_empty(): void
    {
        $this->setBlockedWords('');

        $this->assertNull($this->service->blockedWord('anything goes here'));
    }

    private function setBlockedWords(string $words): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'blocked_comment_words'],
            ['value' => $words, 'group' => SettingGroup::General, 'type' => 'string'],
        );
    }
}
