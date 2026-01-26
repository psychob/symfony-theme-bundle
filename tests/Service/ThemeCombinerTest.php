<?php

declare(strict_types=1);

namespace Tests\PsychoB\Backlog\Theme\Service;

use Override;
use PHPUnit\Framework\TestCase;
use PsychoB\Backlog\Theme\Service\SourceMapGenerator;
use PsychoB\Backlog\Theme\Service\ThemeCombiner;
use RuntimeException;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * Unit tests for ThemeCombiner service.
 */
final class ThemeCombinerTest extends TestCase
{
    private string $tempDir;
    private string $sourceDir;

    #[Override]
    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/theme_combiner_test_' . uniqid();
        $this->sourceDir = $this->tempDir . '/sources';

        mkdir($this->sourceDir, 0o755, true);
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->recursiveDelete($this->tempDir);
    }

    private function recursiveDelete(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        if (is_dir($path)) {
            $items = scandir($path);
            \assert($items !== false);

            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }

                $this->recursiveDelete($path . '/' . $item);
            }
            rmdir($path);
        } else {
            unlink($path);
        }
    }

    private function createSourceFile(string $relativePath, string $content): string
    {
        $fullPath = $this->sourceDir . '/' . $relativePath;
        $dir = \dirname($fullPath);

        if (!is_dir($dir)) {
            mkdir($dir, 0o755, true);
        }

        file_put_contents($fullPath, $content);

        return $fullPath;
    }

    /**
     * @param array<string, string>       $paths
     * @param array<string, list<string>> $files
     */
    private function createCombiner(
        array $paths = [],
        array $files = [],
        bool $sourcemaps = false,
    ): ThemeCombiner {
        return new ThemeCombiner(
            themePaths: $paths,
            themeFiles: $files,
            projectDir: $this->tempDir,
            sourcemaps: $sourcemaps,
            sourceMapGenerator: new SourceMapGenerator(),
            cache: new ArrayAdapter(),
        );
    }

    public function testGetCombinedFileThrowsForUnconfiguredFile(): void
    {
        $combiner = $this->createCombiner();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Theme file "unknown.css" is not configured.');

        $combiner->getCombinedFile('unknown.css');
    }

    public function testGetCombinedFileResolvesNamespacedPaths(): void
    {
        $this->createSourceFile('style/base.css', 'body { margin: 0; }');

        $combiner = $this->createCombiner(
            paths: [$this->sourceDir . '/style' => 'core'],
            files: ['bundle.css' => ['@core/base.css']],
        );

        $result = $combiner->getCombinedFile('bundle.css');

        self::assertStringContainsString('body { margin: 0; }', $result->content);
    }

    public function testGetCombinedFileCombinesMultipleFiles(): void
    {
        $this->createSourceFile('a.css', '/* file a */');
        $this->createSourceFile('b.css', '/* file b */');

        $combiner = $this->createCombiner(
            paths: [$this->sourceDir => 'src'],
            files: ['combined.css' => ['@src/a.css', '@src/b.css']],
        );

        $result = $combiner->getCombinedFile('combined.css');

        self::assertStringContainsString('/* file a */', $result->content);
        self::assertStringContainsString('/* file b */', $result->content);
    }

    public function testGetCombinedFileReturnsCorrectContentType(): void
    {
        $this->createSourceFile('script.js', 'console.log("hello");');
        $this->createSourceFile('style.css', 'body {}');

        $jsCombiner = $this->createCombiner(
            paths: [$this->sourceDir => 'src'],
            files: ['app.js' => ['@src/script.js']],
        );
        $cssCombiner = $this->createCombiner(
            paths: [$this->sourceDir => 'src'],
            files: ['app.css' => ['@src/style.css']],
        );

        $jsResult = $jsCombiner->getCombinedFile('app.js');
        $cssResult = $cssCombiner->getCombinedFile('app.css');

        self::assertSame('application/javascript', $jsResult->contentType);
        self::assertSame('text/css', $cssResult->contentType);
    }

    public function testGetCombinedFileReturnsHash(): void
    {
        $this->createSourceFile('test.css', 'body {}');

        $combiner = $this->createCombiner(
            paths: [$this->sourceDir => 'src'],
            files: ['test.css' => ['@src/test.css']],
        );

        $result = $combiner->getCombinedFile('test.css');

        self::assertNotEmpty($result->hash);
        self::assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $result->hash);
    }

    public function testGetCombinedFileHashChangesWhenContentChanges(): void
    {
        $this->createSourceFile('test.css', 'original content');

        $combiner = $this->createCombiner(
            paths: [$this->sourceDir => 'src'],
            files: ['test.css' => ['@src/test.css']],
        );

        $result1 = $combiner->getCombinedFile('test.css');

        // Modify the source file (need to wait a second for mtime to change)
        sleep(1);
        file_put_contents($this->sourceDir . '/test.css', 'modified content');

        // Create new combiner with fresh cache
        $combiner2 = $this->createCombiner(
            paths: [$this->sourceDir => 'src'],
            files: ['test.css' => ['@src/test.css']],
        );

        $result2 = $combiner2->getCombinedFile('test.css');

        self::assertNotSame($result1->hash, $result2->hash);
    }

    public function testGetCombinedFileReturnsLastModifiedTimestamp(): void
    {
        $this->createSourceFile('test.css', 'body {}');

        $combiner = $this->createCombiner(
            paths: [$this->sourceDir => 'src'],
            files: ['test.css' => ['@src/test.css']],
        );

        $result = $combiner->getCombinedFile('test.css');

        self::assertGreaterThan(0, $result->lastModified);
        self::assertLessThanOrEqual(time(), $result->lastModified);
    }

    public function testGetCombinedFileThrowsForMissingSourceFile(): void
    {
        $combiner = $this->createCombiner(
            paths: [$this->sourceDir => 'src'],
            files: ['test.css' => ['@src/nonexistent.css']],
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Theme source file does not exist');

        $combiner->getCombinedFile('test.css');
    }

    public function testGetCombinedFileThrowsForInvalidNamespace(): void
    {
        $this->createSourceFile('test.css', 'body {}');

        $combiner = $this->createCombiner(
            paths: [$this->sourceDir => 'src'],
            files: ['test.css' => ['@unknown/test.css']],
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Theme namespace "unknown" is not configured.');

        $combiner->getCombinedFile('test.css');
    }

    public function testGetCombinedFileThrowsForInvalidPathFormat(): void
    {
        $combiner = $this->createCombiner(
            paths: [$this->sourceDir => 'src'],
            files: ['test.css' => ['@invalidformat']],
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid theme path format');

        $combiner->getCombinedFile('test.css');
    }

    public function testGetCombinedFileCachesResult(): void
    {
        $this->createSourceFile('test.css', 'body {}');

        $cache = new ArrayAdapter();
        $combiner = new ThemeCombiner(
            themePaths: [$this->sourceDir => 'src'],
            themeFiles: ['test.css' => ['@src/test.css']],
            projectDir: $this->tempDir,
            sourcemaps: false,
            sourceMapGenerator: new SourceMapGenerator(),
            cache: $cache,
        );

        $result1 = $combiner->getCombinedFile('test.css');
        $result2 = $combiner->getCombinedFile('test.css');

        self::assertSame($result1->hash, $result2->hash);
        self::assertSame($result1->content, $result2->content);
    }

    public function testGetCombinedFileGeneratesSourceMapWhenEnabled(): void
    {
        $this->createSourceFile('test.css', "body {}\n");

        $combiner = $this->createCombiner(
            paths: [$this->sourceDir => 'src'],
            files: ['test.css' => ['@src/test.css']],
            sourcemaps: true,
        );

        $result = $combiner->getCombinedFile('test.css');

        self::assertNotNull($result->sourceMapContent);
    }

    public function testGetCombinedFileAppendsSourceMappingUrlComment(): void
    {
        $this->createSourceFile('test.css', "body {}\n");

        $combiner = $this->createCombiner(
            paths: [$this->sourceDir => 'src'],
            files: ['test.css' => ['@src/test.css']],
            sourcemaps: true,
        );

        $result = $combiner->getCombinedFile('test.css');

        self::assertStringContainsString('sourceMappingURL=', $result->content);
        self::assertStringContainsString('.css.map', $result->content);
    }

    public function testGetCombinedFileDoesNotGenerateSourceMapWhenDisabled(): void
    {
        $this->createSourceFile('test.css', "body {}\n");

        $combiner = $this->createCombiner(
            paths: [$this->sourceDir => 'src'],
            files: ['test.css' => ['@src/test.css']],
            sourcemaps: false,
        );

        $result = $combiner->getCombinedFile('test.css');

        self::assertNull($result->sourceMapContent);
        self::assertStringNotContainsString('sourceMappingURL=', $result->content);
    }

    public function testGetCombinedFileSourceMapContainsValidJson(): void
    {
        $this->createSourceFile('test.css', "body { color: red; }\n");

        $combiner = $this->createCombiner(
            paths: [$this->sourceDir => 'src'],
            files: ['test.css' => ['@src/test.css']],
            sourcemaps: true,
        );

        $result = $combiner->getCombinedFile('test.css');

        self::assertNotNull($result->sourceMapContent);
        $decoded = json_decode($result->sourceMapContent, true);

        self::assertIsArray($decoded);
        self::assertSame(3, $decoded['version']);
    }

    public function testGetCombinedFileThrowsForUnsupportedExtension(): void
    {
        $this->createSourceFile('test.txt', 'some text');

        $combiner = $this->createCombiner(
            paths: [$this->sourceDir => 'src'],
            files: ['test.txt' => ['@src/test.txt']],
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Content type for extension "txt" is not supported.');

        $combiner->getCombinedFile('test.txt');
    }

    public function testGetCombinedFileJsSourceMapUsesCorrectCommentSyntax(): void
    {
        $this->createSourceFile('test.js', "console.log('hello');\n");

        $combiner = $this->createCombiner(
            paths: [$this->sourceDir => 'src'],
            files: ['app.js' => ['@src/test.js']],
            sourcemaps: true,
        );

        $result = $combiner->getCombinedFile('app.js');

        // JS uses //# syntax, not /*# */
        self::assertStringContainsString('//# sourceMappingURL=', $result->content);
    }

    public function testGetCombinedFileThrowsForEmptySourceList(): void
    {
        $combiner = $this->createCombiner(paths: [], files: ['empty.css' => []]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Theme file "empty.css" has no source files.');

        $combiner->getCombinedFile('empty.css');
    }

    public function testGetSourceMapReturnsNullForNonExistentCache(): void
    {
        $combiner = $this->createCombiner();

        $result = $combiner->getSourceMap('nonexistent');

        self::assertNull($result);
    }

    public function testGetSourceMapReturnsContentFromCache(): void
    {
        $this->createSourceFile('test.css', "body {}\n");

        $combiner = $this->createCombiner(
            paths: [$this->sourceDir => 'src'],
            files: ['test.css' => ['@src/test.css']],
            sourcemaps: true,
        );

        $result = $combiner->getCombinedFile('test.css');
        $sourceMap = $combiner->getSourceMap($result->hash);

        self::assertNotNull($sourceMap);
        self::assertSame($result->sourceMapContent, $sourceMap);
    }
}
