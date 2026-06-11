<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\RecommendationCookie;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

final class RecommendationCookieTest extends TestCase
{
    public function test_returns_empty_when_no_consent_cookie(): void
    {
        $request = Request::create('/');

        $result = RecommendationCookie::fromRequest($request);

        $this->assertSame([], $result['categoryWeights']);
        $this->assertSame([], $result['bookIds']);
    }

    public function test_returns_empty_when_consent_is_not_accepted(): void
    {
        $request = Request::create('/');
        $request->cookies->set(RecommendationCookie::CONSENT_COOKIE, 'declined');

        $result = RecommendationCookie::fromRequest($request);

        $this->assertSame([], $result['categoryWeights']);
        $this->assertSame([], $result['bookIds']);
    }

    public function test_returns_empty_when_interests_cookie_is_missing(): void
    {
        $request = Request::create('/');
        $request->cookies->set(RecommendationCookie::CONSENT_COOKIE, 'accepted');

        $result = RecommendationCookie::fromRequest($request);

        $this->assertSame([], $result['categoryWeights']);
        $this->assertSame([], $result['bookIds']);
    }

    public function test_returns_empty_when_interests_cookie_is_invalid_json(): void
    {
        $request = Request::create('/');
        $request->cookies->set(RecommendationCookie::CONSENT_COOKIE, 'accepted');
        $request->cookies->set(RecommendationCookie::INTERESTS_COOKIE, 'not-json');

        $result = RecommendationCookie::fromRequest($request);

        $this->assertSame([], $result['categoryWeights']);
        $this->assertSame([], $result['bookIds']);
    }

    public function test_parses_valid_consent_and_interests(): void
    {
        $interests = rawurlencode(json_encode([
            'categories' => ['3' => 2, '7' => 5],
            'books' => [10, 20, 30],
        ], JSON_THROW_ON_ERROR));

        $request = Request::create('/');
        $request->cookies->set(RecommendationCookie::CONSENT_COOKIE, 'accepted');
        $request->cookies->set(RecommendationCookie::INTERESTS_COOKIE, $interests);

        $result = RecommendationCookie::fromRequest($request);

        $this->assertSame([3 => 2, 7 => 5], $result['categoryWeights']);
        $this->assertSame([10, 20, 30], $result['bookIds']);
    }

    public function test_caps_category_weight_at_9(): void
    {
        $interests = rawurlencode(json_encode([
            'categories' => ['1' => 99],
            'books' => [],
        ], JSON_THROW_ON_ERROR));

        $request = Request::create('/');
        $request->cookies->set(RecommendationCookie::CONSENT_COOKIE, 'accepted');
        $request->cookies->set(RecommendationCookie::INTERESTS_COOKIE, $interests);

        $result = RecommendationCookie::fromRequest($request);

        $this->assertSame(9, $result['categoryWeights'][1]);
    }

    public function test_ignores_zero_or_negative_category_weight(): void
    {
        $interests = rawurlencode(json_encode([
            'categories' => ['1' => 0, '2' => -5],
            'books' => [],
        ], JSON_THROW_ON_ERROR));

        $request = Request::create('/');
        $request->cookies->set(RecommendationCookie::CONSENT_COOKIE, 'accepted');
        $request->cookies->set(RecommendationCookie::INTERESTS_COOKIE, $interests);

        $result = RecommendationCookie::fromRequest($request);

        $this->assertSame([], $result['categoryWeights']);
    }

    public function test_limits_to_8_categories(): void
    {
        $categories = array_fill_keys(array_map(strval(...), range(1, 12)), 1);
        $interests = rawurlencode(json_encode(['categories' => $categories, 'books' => []], JSON_THROW_ON_ERROR));

        $request = Request::create('/');
        $request->cookies->set(RecommendationCookie::CONSENT_COOKIE, 'accepted');
        $request->cookies->set(RecommendationCookie::INTERESTS_COOKIE, $interests);

        $result = RecommendationCookie::fromRequest($request);

        $this->assertCount(8, $result['categoryWeights']);
    }

    public function test_limits_book_ids_to_12(): void
    {
        $interests = rawurlencode(json_encode([
            'categories' => [],
            'books' => range(1, 20),
        ], JSON_THROW_ON_ERROR));

        $request = Request::create('/');
        $request->cookies->set(RecommendationCookie::CONSENT_COOKIE, 'accepted');
        $request->cookies->set(RecommendationCookie::INTERESTS_COOKIE, $interests);

        $result = RecommendationCookie::fromRequest($request);

        $this->assertCount(12, $result['bookIds']);
    }

    public function test_deduplicates_book_ids(): void
    {
        $interests = rawurlencode(json_encode([
            'categories' => [],
            'books' => [5, 5, 10, 5],
        ], JSON_THROW_ON_ERROR));

        $request = Request::create('/');
        $request->cookies->set(RecommendationCookie::CONSENT_COOKIE, 'accepted');
        $request->cookies->set(RecommendationCookie::INTERESTS_COOKIE, $interests);

        $result = RecommendationCookie::fromRequest($request);

        $this->assertSame([5, 10], $result['bookIds']);
    }

    public function test_ignores_non_integer_book_ids(): void
    {
        $interests = rawurlencode(json_encode([
            'categories' => [],
            'books' => ['abc', 0, 7],
        ], JSON_THROW_ON_ERROR));

        $request = Request::create('/');
        $request->cookies->set(RecommendationCookie::CONSENT_COOKIE, 'accepted');
        $request->cookies->set(RecommendationCookie::INTERESTS_COOKIE, $interests);

        $result = RecommendationCookie::fromRequest($request);

        $this->assertSame([7], $result['bookIds']);
    }
}
