<?php

declare(strict_types=1);

namespace Tests\PsychoB\Theme\DependencyInjection;

use Override;
use PHPUnit\Framework\TestCase;
use PsychoB\Theme\Controller\ServeCombinedFilesController;
use PsychoB\Theme\DependencyInjection\Configuration;
use PsychoB\Theme\DependencyInjection\ThemeExtension;
use PsychoB\Theme\Service\SourceMapGenerator;
use PsychoB\Theme\Service\ThemeCombiner;
use PsychoB\Theme\Twig\ThemeExtension as TwigThemeExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests for Theme DI extension.
 */
final class ThemeExtensionTest extends TestCase
{
    private ContainerBuilder $container;
    private ThemeExtension $extension;

    #[Override]
    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.cache_dir', '/tmp/cache');
        $this->extension = new ThemeExtension();
    }

    public function testLoadRegistersSourceMapGenerator(): void
    {
        $this->extension->load([], $this->container);

        self::assertTrue($this->container->hasDefinition(SourceMapGenerator::class));
    }

    public function testLoadRegistersThemeCombiner(): void
    {
        $this->extension->load([], $this->container);

        self::assertTrue($this->container->hasDefinition(ThemeCombiner::class));
    }

    public function testLoadRegistersController(): void
    {
        $this->extension->load([], $this->container);

        self::assertTrue($this->container->hasDefinition(ServeCombinedFilesController::class));
    }

    public function testLoadRegistersTwigExtension(): void
    {
        $this->extension->load([], $this->container);

        self::assertTrue($this->container->hasDefinition(TwigThemeExtension::class));
    }

    public function testLoadPassesConfigurationToThemeCombiner(): void
    {
        $config = [
            'sourcemaps' => true,
            'paths'      => ['/some/path' => 'core'],
            'files'      => ['bundle.css' => ['@core/style.css']],
        ];

        $this->extension->load([$config], $this->container);

        $definition = $this->container->getDefinition(ThemeCombiner::class);

        self::assertSame(['/some/path' => 'core'], $definition->getArgument('$themePaths'));
        self::assertSame(['bundle.css' => ['@core/style.css']], $definition->getArgument('$themeFiles'));
        self::assertTrue($definition->getArgument('$sourcemaps'));
    }

    public function testLoadDisablesSourcemapsWhenConfiguredFalse(): void
    {
        $config = [
            'sourcemaps' => false,
        ];

        $this->extension->load([$config], $this->container);

        $definition = $this->container->getDefinition(ThemeCombiner::class);

        self::assertFalse($definition->getArgument('$sourcemaps'));
    }

    public function testLoadSetsAutowiredAndAutoconfigured(): void
    {
        $this->extension->load([], $this->container);

        $combinerDef = $this->container->getDefinition(ThemeCombiner::class);
        $controllerDef = $this->container->getDefinition(ServeCombinedFilesController::class);
        $twigDef = $this->container->getDefinition(TwigThemeExtension::class);

        self::assertTrue($combinerDef->isAutowired());
        self::assertTrue($combinerDef->isAutoconfigured());
        self::assertTrue($controllerDef->isAutowired());
        self::assertTrue($controllerDef->isAutoconfigured());
        self::assertTrue($twigDef->isAutowired());
        self::assertTrue($twigDef->isAutoconfigured());
    }

    public function testGetConfigurationReturnsConfiguration(): void
    {
        $config = $this->extension->getConfiguration([], $this->container);

        self::assertInstanceOf(Configuration::class, $config);
    }
}
