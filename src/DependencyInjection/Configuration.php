<?php

declare(strict_types=1);

namespace PsychoB\Theme\DependencyInjection;

use Override;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration schema for theme.yaml.
 */
final class Configuration implements ConfigurationInterface
{
    #[Override]
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('theme');
        $rootNode = $treeBuilder->getRootNode();

        /** @psalm-suppress UnusedMethodCall */
        $rootNode->children()
            ->booleanNode('sourcemaps')
            ->defaultFalse()
            ->info('Enable source maps generation (only effective when kernel.debug=true)')
            ->end()
            ->arrayNode('paths')
            ->useAttributeAsKey('path')
            ->scalarPrototype()
            ->end()
            ->end()
            ->arrayNode('files')
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->scalarPrototype()
            ->end()
            ->end()
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
