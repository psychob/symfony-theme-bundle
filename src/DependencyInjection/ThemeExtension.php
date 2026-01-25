<?php

declare(strict_types=1);

namespace PsychoB\Backlog\Theme\DependencyInjection;

use Override;
use PsychoB\Backlog\Theme\Controller\ServeCombinedFilesController;
use PsychoB\Backlog\Theme\Service\SourceMapGenerator;
use PsychoB\Backlog\Theme\Service\ThemeCombiner;
use PsychoB\Backlog\Theme\Twig\ThemeExtension as TwigThemeExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Loads theme configuration and registers the ThemeCombiner service.
 */
final class ThemeExtension extends Extension
{
    private const string CACHE_SERVICE_ID = 'cache.theme';

    #[Override]
    public function getConfiguration(array $config, ContainerBuilder $container): Configuration
    {
        return new Configuration();
    }

    #[Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        // Register SourceMapGenerator
        $sourceMapGeneratorDefinition = new Definition(SourceMapGenerator::class);
        $container->setDefinition(SourceMapGenerator::class, $sourceMapGeneratorDefinition);

        // Register ThemeCombiner
        $sourcemapsEnabled = (bool)($config['sourcemaps'] ?? false);

        $definition = new Definition(ThemeCombiner::class);
        $definition->setArgument('$themePaths', $config['paths'] ?? []);
        $definition->setArgument('$themeFiles', $config['files'] ?? []);
        $definition->setArgument('$projectDir', '%kernel.project_dir%');
        $definition->setArgument('$sourcemaps', $sourcemapsEnabled);
        $definition->setArgument('$sourceMapGenerator', new Reference(SourceMapGenerator::class));
        $definition->setArgument('$cache', new Reference(self::CACHE_SERVICE_ID));
        $definition->setAutowired(true);
        $definition->setAutoconfigured(true);

        $container->setDefinition(ThemeCombiner::class, $definition);

        $controllerDefinition = new Definition(ServeCombinedFilesController::class);
        $controllerDefinition->setAutowired(true);
        $controllerDefinition->setAutoconfigured(true);
        $container->setDefinition(ServeCombinedFilesController::class, $controllerDefinition);

        $twigDefinition = new Definition(TwigThemeExtension::class);
        $twigDefinition->setAutowired(true);
        $twigDefinition->setAutoconfigured(true);
        $container->setDefinition(TwigThemeExtension::class, $twigDefinition);
    }
}
