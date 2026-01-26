<?php

declare(strict_types=1);

namespace Tests\PsychoB\Backlog\Theme\Service;

use const JSON_ERROR_NONE;

use Override;
use PHPUnit\Framework\TestCase;
use PsychoB\Backlog\Theme\Service\SourceMapGenerator;

/**
 * Unit tests for SourceMapGenerator.
 */
final class SourceMapGeneratorTest extends TestCase
{
    private SourceMapGenerator $generator;

    #[Override]
    protected function setUp(): void
    {
        $this->generator = new SourceMapGenerator();
    }

    /**
     * Decode and validate source map JSON structure.
     *
     * @return array{version: int, file: string, sources: list<string>, sourcesContent: list<string>, mappings: string}
     */
    private function assertSourceMapStructure(string $json): array
    {
        $decoded = json_decode($json, true);
        self::assertIsArray($decoded);

        self::assertArrayHasKey('version', $decoded);
        self::assertArrayHasKey('file', $decoded);
        self::assertArrayHasKey('sources', $decoded);
        self::assertArrayHasKey('sourcesContent', $decoded);
        self::assertArrayHasKey('mappings', $decoded);

        /** @var array{version: int, file: string, sources: list<string>, sourcesContent: list<string>, mappings: string} */
        return $decoded;
    }

    public function testGenerateReturnsValidJsonStructure(): void
    {
        $sourcePaths = ['/path/to/file.css'];
        $sourceContents = ["body { color: red; }\n"];
        $generatedFile = 'output.css';

        $result = $this->generator->generate($sourcePaths, $sourceContents, $generatedFile);

        $this->assertSourceMapStructure($result);
    }

    public function testGenerateReturnsVersion3(): void
    {
        $result = $this->generator->generate(['/file.css'], ["body {}\n"], 'out.css');

        $decoded = $this->assertSourceMapStructure($result);
        self::assertSame(3, $decoded['version']);
    }

    public function testGenerateIncludesGeneratedFileName(): void
    {
        $result = $this->generator->generate(['/file.css'], ["body {}\n"], 'frontend.css');

        $decoded = $this->assertSourceMapStructure($result);
        self::assertSame('frontend.css', $decoded['file']);
    }

    public function testGenerateUsesBaseNameWhenNoProjectDir(): void
    {
        $sourcePaths = ['/some/long/path/to/clear.css', '/another/path/theme.css'];
        $sourceContents = ["/* clear */\n", "/* theme */\n"];

        $result = $this->generator->generate($sourcePaths, $sourceContents, 'out.css');

        $decoded = $this->assertSourceMapStructure($result);
        self::assertSame(['clear.css', 'theme.css'], $decoded['sources']);
    }

    public function testGenerateUsesRelativePathsWhenProjectDirProvided(): void
    {
        $tempDir = sys_get_temp_dir() . '/sourcemap_test_' . uniqid();
        mkdir($tempDir . '/src/styles', 0o755, true);

        $file1 = $tempDir . '/src/styles/base.css';
        $file2 = $tempDir . '/src/styles/theme.css';
        file_put_contents($file1, "/* base */\n");
        file_put_contents($file2, "/* theme */\n");

        $result = $this->generator->generate(
            [$file1, $file2],
            ["/* base */\n", "/* theme */\n"],
            'out.css',
            $tempDir,
        );

        $decoded = $this->assertSourceMapStructure($result);
        self::assertSame(['/src/styles/base.css', '/src/styles/theme.css'], $decoded['sources']);

        // Cleanup
        unlink($file1);
        unlink($file2);
        rmdir($tempDir . '/src/styles');
        rmdir($tempDir . '/src');
        rmdir($tempDir);
    }

    public function testGenerateUsesPrivatePlaceholderForExternalFiles(): void
    {
        $tempDir = sys_get_temp_dir() . '/sourcemap_test_' . uniqid();
        mkdir($tempDir, 0o755, true);

        // File outside projectDir
        $externalFile = sys_get_temp_dir() . '/external_' . uniqid() . '.css';
        file_put_contents($externalFile, "/* external */\n");

        $result = $this->generator->generate([$externalFile], ["/* external */\n"], 'out.css', $tempDir);

        $decoded = $this->assertSourceMapStructure($result);
        self::assertSame([':private-0'], $decoded['sources']);

        // Cleanup
        unlink($externalFile);
        rmdir($tempDir);
    }

    public function testGenerateIncludesSourceContents(): void
    {
        $content1 = "body { margin: 0; }\n";
        $content2 = "h1 { font-size: 2em; }\n";
        $sourceContents = [$content1, $content2];

        $result = $this->generator->generate(['/a.css', '/b.css'], $sourceContents, 'out.css');

        $decoded = $this->assertSourceMapStructure($result);
        self::assertSame($sourceContents, $decoded['sourcesContent']);
    }

    public function testGenerateMappingsNotEmpty(): void
    {
        $result = $this->generator->generate(['/file.css'], ["line1\nline2\n"], 'out.css');

        $decoded = $this->assertSourceMapStructure($result);
        self::assertNotEmpty($decoded['mappings']);
    }

    public function testGenerateMappingsContainsSemicolonsForMultipleLines(): void
    {
        $content = "line1\nline2\nline3";
        $result = $this->generator->generate(['/file.css'], [$content], 'out.css');

        $decoded = $this->assertSourceMapStructure($result);
        // Three lines means at least 2 semicolons separating line mappings
        $semicolonCount = substr_count($decoded['mappings'], ';');
        self::assertGreaterThanOrEqual(2, $semicolonCount);
    }

    public function testGenerateHandlesMultipleSourceFiles(): void
    {
        $sourcePaths = ['/a.css', '/b.css', '/c.css'];
        $sourceContents = ["body {}\n", "h1 {}\nh2 {}\n", "p {}\n"];

        $result = $this->generator->generate($sourcePaths, $sourceContents, 'combined.css');

        $decoded = $this->assertSourceMapStructure($result);
        self::assertCount(3, $decoded['sources']);
        self::assertCount(3, $decoded['sourcesContent']);
    }

    public function testGenerateHandlesEmptyContent(): void
    {
        $result = $this->generator->generate(['/empty.css'], [''], 'out.css');

        $decoded = $this->assertSourceMapStructure($result);
        self::assertSame([''], $decoded['sourcesContent']);
    }

    public function testGenerateMappingsAreVlqEncoded(): void
    {
        $result = $this->generator->generate(['/file.css'], ["body {}\n"], 'out.css');

        $decoded = $this->assertSourceMapStructure($result);

        // VLQ characters are: A-Z, a-z, 0-9, +, /
        self::assertMatchesRegularExpression('/^[A-Za-z0-9+\/;,]*$/', $decoded['mappings']);
    }

    public function testGenerateOutputIsValidJson(): void
    {
        $result = $this->generator->generate(
            ['/path/with spaces/file.css'],
            ["/* special chars: \\ \" ' */\n"],
            'out.css'
        );

        $this->assertSourceMapStructure($result);
        self::assertSame(JSON_ERROR_NONE, json_last_error());
    }

    public function testGenerateDoesNotEscapeSlashes(): void
    {
        $result = $this->generator->generate(['/a/b/c.css'], ["body {}\n"], 'out.css');

        // JSON should not have escaped slashes due to JSON_UNESCAPED_SLASHES flag
        self::assertStringNotContainsString('\/', $result);
    }
}
