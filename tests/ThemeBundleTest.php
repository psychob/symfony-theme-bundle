<?php

declare(strict_types=1);

namespace Tests\PsychoB\Theme;

use PHPUnit\Framework\TestCase;
use PsychoB\Theme\ThemeBundle;

/**
 * Tests for ThemeBundle.
 */
final class ThemeBundleTest extends TestCase
{
    public function testGetPathReturnsSourceDirectory(): void
    {
        $bundle = new ThemeBundle();
        $path = $bundle->getPath();

        self::assertDirectoryExists($path);
        self::assertStringEndsWith('src', $path);
    }

    public function testGetPathContainsExpectedFiles(): void
    {
        $bundle = new ThemeBundle();
        $path = $bundle->getPath();

        self::assertFileExists($path . '/ThemeBundle.php');
        self::assertDirectoryExists($path . '/DependencyInjection');
        self::assertDirectoryExists($path . '/Service');
        self::assertDirectoryExists($path . '/Controller');
    }
}
