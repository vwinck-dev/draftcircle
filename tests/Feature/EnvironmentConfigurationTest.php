<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

final class EnvironmentConfigurationTest extends TestCase
{
    public function test_test_suite_uses_isolated_services(): void
    {
        $this->assertTrue(app()->environment('testing'));
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('array', config('cache.default'));
        $this->assertSame('array', config('session.driver'));
        $this->assertSame('sync', config('queue.default'));
        $this->assertFalse(config('telescope.enabled'));
    }

    public function test_example_and_ci_environment_files_define_the_same_keys(): void
    {
        $this->assertSame(
            $this->environmentKeys('.env.example'),
            $this->environmentKeys('.env.ci'),
        );
    }

    /** @return list<string> */
    private function environmentKeys(string $file): array
    {
        preg_match_all(
            pattern: '/^([A-Z][A-Z0-9_]*)=/m',
            subject: File::get(base_path($file)),
            matches: $matches,
        );

        $keys = $matches[1];
        sort($keys);

        return $keys;
    }
}
