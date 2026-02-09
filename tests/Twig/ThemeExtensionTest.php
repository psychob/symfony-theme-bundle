<?php

declare(strict_types=1);

namespace Tests\PsychoB\Theme\Twig;

use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PsychoB\Theme\Twig\ThemeExtension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\PsychoB\Theme\ThemeTestCase;
use Twig\TwigFunction;

/**
 * Tests for the Theme Twig extension.
 */
final class ThemeExtensionTest extends ThemeTestCase
{
    private ThemeExtension $extension;
    private MockObject&UrlGeneratorInterface $urlGenerator;

    #[Override]
    protected function setUp(): void
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->extension = new ThemeExtension($this->urlGenerator);
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
        $this->urlGenerator->method('generate')
            ->with('theme_serve', ['file' => 'frontend.css'])
            ->willReturn('/_/theme/frontend.css')
        ;

        $url = $this->extension->themeAsset('frontend.css');

        self::assertSame('/_/theme/frontend.css', $url);
    }

    public function testThemeAssetGeneratesUrlForDifferentFiles(): void
    {
        $this->urlGenerator->method('generate')
            ->willReturnCallback(fn(string $route, array $params): string => '/_/theme/' . $params['file'])
        ;

        $frontendUrl = $this->extension->themeAsset('frontend.css');
        $adminUrl = $this->extension->themeAsset('admin.css');

        self::assertStringContainsString('frontend.css', $frontendUrl);
        self::assertStringContainsString('admin.css', $adminUrl);
        self::assertNotSame($frontendUrl, $adminUrl);
    }

    public function testThemeAssetDelegatestoUrlGenerator(): void
    {
        $this->urlGenerator->expects(self::once())
            ->method('generate')
            ->with('theme_serve', ['file' => 'app.js'])
            ->willReturn('/_/theme/app.js')
        ;

        $this->extension->themeAsset('app.js');
    }
}
