<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;

final readonly class RecommendationCookie
{
    public const CONSENT_COOKIE = 'library_cookie_consent';

    public const INTERESTS_COOKIE = 'library_recommendation_interests';

    /**
     * @return array{categoryWeights: array<int, int>, bookIds: list<int>}
     */
    public static function fromRequest(Request $request): array
    {
        if ($request->cookie(self::CONSENT_COOKIE) !== 'accepted') {
            return ['categoryWeights' => [], 'bookIds' => []];
        }

        $rawInterests = $request->cookie(self::INTERESTS_COOKIE);

        if (! is_string($rawInterests)) {
            return ['categoryWeights' => [], 'bookIds' => []];
        }

        $decoded = json_decode(rawurldecode($rawInterests), true);

        if (! is_array($decoded)) {
            return ['categoryWeights' => [], 'bookIds' => []];
        }

        return [
            'categoryWeights' => self::categoryWeights($decoded['categories'] ?? null),
            'bookIds' => self::bookIds($decoded['books'] ?? null),
        ];
    }

    /** @return array<int, int> */
    private static function categoryWeights(mixed $categories): array
    {
        if (! is_array($categories)) {
            return [];
        }

        $weights = [];

        foreach (array_slice($categories, 0, 8, true) as $categoryId => $weight) {
            $categoryId = filter_var($categoryId, FILTER_VALIDATE_INT);
            $weight = filter_var($weight, FILTER_VALIDATE_INT);

            if ($categoryId !== false && $categoryId > 0 && $weight !== false && $weight > 0) {
                $weights[$categoryId] = min($weight, 9);
            }
        }

        return $weights;
    }

    /** @return list<int> */
    private static function bookIds(mixed $books): array
    {
        if (! is_array($books)) {
            return [];
        }

        return array_values(array_unique(array_filter(
            array_map(
                fn (mixed $bookId): int|false => filter_var($bookId, FILTER_VALIDATE_INT),
                array_slice($books, 0, 12),
            ),
            fn (int|false $bookId): bool => $bookId !== false && $bookId > 0,
        )));
    }
}
