<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\Enums\BookFormat;

final readonly class BookData
{
    /**
     * @param list<int> $categoryIds
     * @param list<string> $pages
     */
    public function __construct(
        public string $title,
        public string $author,
        public ?string $publisher,
        public ?int $year,
        public string $language,
        public ?string $isbn,
        public ?string $description,
        public ?string $coverPath,
        public BookFormat $format,
        public int $totalCopies,
        public bool $isActive,
        public array $categoryIds,
        public array $pages,
        public bool $pagesProvided,
    ) {}

    /**
     * Validated request payloads are heterogeneous by design.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: self::requiredString(data: $data, key: 'title'),
            author: self::requiredString(data: $data, key: 'author'),
            publisher: self::nullableString(data: $data, key: 'publisher'),
            year: is_int($data['year'] ?? null) ? $data['year'] : null,
            language: self::requiredString(data: $data, key: 'language', fallback: 'en'),
            isbn: self::nullableString(data: $data, key: 'isbn'),
            description: self::nullableString(data: $data, key: 'description'),
            coverPath: self::nullableString(data: $data, key: 'cover_path'),
            format: BookFormat::from(self::requiredString(data: $data, key: 'format')),
            totalCopies: is_int($data['total_copies'] ?? null) ? $data['total_copies'] : 0,
            isActive: is_bool($data['is_active'] ?? null) ? $data['is_active'] : true,
            categoryIds: self::categoryIds($data['category_ids'] ?? []),
            pages: self::pages($data['pages'] ?? []),
            pagesProvided: ($data['pages_provided'] ?? false) === true || array_key_exists('pages', $data),
        );
    }

    /** @param array<string, mixed> $data */
    private static function requiredString(array $data, string $key, string $fallback = ''): string
    {
        return is_string($data[$key] ?? null) ? $data[$key] : $fallback;
    }

    /** @param array<string, mixed> $data */
    private static function nullableString(array $data, string $key): ?string
    {
        return is_string($data[$key] ?? null) && $data[$key] !== '' ? $data[$key] : null;
    }

    /** @return list<int> */
    private static function categoryIds(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, is_int(...)));
    }

    /** @return list<string> */
    private static function pages(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $pages = [];

        foreach ($value as $page) {
            if (is_array($page) && is_string($page['content'] ?? null) && trim($page['content']) !== '') {
                $pages[] = trim($page['content']);
            }
        }

        return $pages;
    }
}
