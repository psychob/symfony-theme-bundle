<?php

declare(strict_types=1);

namespace Tests\PsychoB\Theme\DependencyInjection;

use Override;
use PHPUnit\Framework\TestCase;
use PsychoB\Theme\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

/**
 * Tests for Theme bundle configuration schema.
 */
final class ConfigurationTest extends TestCase
{
    private Processor $processor;
    private Configuration $configuration;

    #[Override]
    protected function setUp(): void
    {
        $this->processor = new Processor();
        $this->configuration = new Configuration();
    }

    private function processConfig(array $config): array
    {
        return $this->processor->processConfiguration($this->configuration, [$config]);
    }

    public function testEmptyConfigurationUsesDefaults(): void
    {
        $result = $this->processConfig([]);

        self::assertFalse($result['sourcemaps']);
        self::assertSame([], $result['paths']);
        self::assertSame([], $result['files']);
    }

    public function testSourcemapsCanBeEnabled(): void
    {
        $result = $this->processConfig([
            'sourcemaps' => true,
        ]);

        self::assertTrue($result['sourcemaps']);
    }

    public function testSourcemapsCanBeDisabled(): void
    {
        $result = $this->processConfig([
            'sourcemaps' => false,
        ]);

        self::assertFalse($result['sourcemaps']);
    }

    public function testPathsCanBeConfigured(): void
    {
        $result = $this->processConfig([
            'paths' => [
                '/some/path'    => 'core',
                '/another/path' => 'landing',
            ],
        ]);

        self::assertSame([
            '/some/path'    => 'core',
            '/another/path' => 'landing',
        ], $result['paths']);
    }

    public function testFilesCanBeConfigured(): void
    {
        $result = $this->processConfig([
            'files' => [
                'frontend.css' => ['@core/a.css', '@core/b.css'],
                'admin.css'    => ['@core/admin.css'],
            ],
        ]);

        self::assertSame([
            'frontend.css' => ['@core/a.css', '@core/b.css'],
            'admin.css'    => ['@core/admin.css'],
        ], $result['files']);
    }

    public function testFullConfiguration(): void
    {
        $result = $this->processConfig([
            'sourcemaps' => true,
            'paths'      => [
                '/project/src/Core/assets' => 'core',
            ],
            'files' => [
                'bundle.css' => ['@core/style.css'],
            ],
        ]);

        self::assertTrue($result['sourcemaps']);
        self::assertSame(['/project/src/Core/assets' => 'core'], $result['paths']);
        self::assertSame(['bundle.css' => ['@core/style.css']], $result['files']);
    }

    public function testGetConfigTreeBuilderReturnsTreeBuilder(): void
    {
        $treeBuilder = $this->configuration->getConfigTreeBuilder();

        self::assertInstanceOf(TreeBuilder::class, $treeBuilder);
        // Verify tree can be built without errors
        $tree = $treeBuilder->buildTree();
        self::assertSame('theme', $tree->getName());
    }
}
