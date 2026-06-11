<?php

declare(strict_types=1);

namespace App\Services;

final readonly class CommentModerationService
{
    public function __construct(private SettingService $settings) {}

    public function blockedWord(string $body): ?string
    {
        $configured = $this->settings->all()['general']['blocked_comment_words'] ?? '';
        $words = preg_split('/[\r\n,]+/u', $configured, flags: PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($words as $word) {
            $word = trim($word);

            if ($word === '') {
                continue;
            }

            $phrase = preg_replace('/\s+/u', '\\s+', preg_quote($word, '/'));
            $pattern = '/(?<![\p{L}\p{N}_])'.$phrase.'(?![\p{L}\p{N}_])/iu';

            if (preg_match($pattern, $body) === 1) {
                return $word;
            }
        }

        return null;
    }
}
