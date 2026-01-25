<?php

declare(strict_types=1);

namespace Tests\PsychoB\Backlog\Theme\Twig;

use Override;
use PsychoB\Backlog\Theme\Twig\ThemeExtension;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\TwigFunction;

/**
 * Tests for the Theme Twig extension.
 */
final class ThemeExtensionTest extends KernelTestCase
{
    private ThemeExtension $extension;

    #[Override]
    protected function setUp(): void
    {
        self::bootKernel();

        /** @var ThemeExtension $extension */
        $extension = self::getContainer()->get(ThemeExtension::class);
        $this->extension = $extension;
    }

    public function testGetFunctionsReturnsThemeAssetFunction(): void
    {
        $functions = $this->extension->getFunctions();

        self::assertCount(1, $functions);
        self::assertInstanceOf(TwigFunction::class, $functions[0]);
        self::assertSame('themeAsset', $functions[0]->getName());
    }

    public function testThemeAssetGeneratesUrlForConfiguredFile(): void
    {
        $url = $this->extension->themeAsset('frontend.css');

        self::assertStringContainsString('/_/theme/frontend.css', $url);
    }

    public function testThemeAssetGeneratesUrlForDifferentFiles(): void
    {
        $frontendUrl = $this->extension->themeAsset('frontend.css');
        $adminUrl = $this->extension->themeAsset('admin.css');

        self::assertStringContainsString('frontend.css', $frontendUrl);
        self::assertStringContainsString('admin.css', $adminUrl);
        self::assertNotSame($frontendUrl, $adminUrl);
    }

    public function testThemeAssetReturnsRelativeUrl(): void
    {
        $url = $this->extension->themeAsset('frontend.css');

        // URL should start with / (relative to domain)
        self::assertStringStartsWith('/', $url);
    }
}
