<?php

declare(strict_types=1);

namespace PsychoB\Backlog\Theme\Service;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;

/**
 * Generates source maps (v3) for combined CSS/JS files.
 */
final class SourceMapGenerator
{
    private const string VLQ_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';

    /**
     * Generate a source map for concatenated files.
     *
     * @param list<string> $sourcePaths    Original file paths
     * @param list<string> $sourceContents Content of each file
     * @param string       $generatedFile  Name of combined file (e.g., "frontend.css")
     * @param string       $projectDir     Project root directory for relative paths
     */
    public function generate(
        array $sourcePaths,
        array $sourceContents,
        string $generatedFile,
        string $projectDir = '',
    ): string {
        $sources = [];
        $sourcesContent = [];
        $mappingLines = [];

        $prevSourceIndex = 0;
        $prevSourceLine = 0;
        $privateCount = 0;

        foreach ($sourcePaths as $index => $path) {
            $sources[] = $this->getRelativePath($path, $projectDir, $privateCount);
            $sourcesContent[] = $sourceContents[$index];

            $content = $sourceContents[$index];
            $lines = explode("\n", $content);
            $lineCount = \count($lines);

            for ($sourceLine = 0; $sourceLine < $lineCount; $sourceLine++) {
                // Each line maps column 0 of generated to column 0 of source
                // VLQ segments: [genColumn, sourceIndex, sourceLine, sourceColumn]
                // All relative to previous values

                $genColumn = 0;
                $sourceIndexDelta = $index - $prevSourceIndex;
                $sourceLineDelta = $sourceLine - $prevSourceLine;
                $sourceColumn = 0;

                $segment = $this->encodeVlq($genColumn)
                    . $this->encodeVlq($sourceIndexDelta)
                    . $this->encodeVlq($sourceLineDelta)
                    . $this->encodeVlq($sourceColumn);

                $mappingLines[] = $segment;

                $prevSourceIndex = $index;
                $prevSourceLine = $sourceLine;
            }
        }

        $mappings = implode(';', $mappingLines);

        return json_encode([
            'version'        => 3,
            'file'           => $generatedFile,
            'sources'        => $sources,
            'sourcesContent' => $sourcesContent,
            'mappings'       => $mappings,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get relative path for source map, or :private-N placeholder for external files.
     */
    private function getRelativePath(string $path, string $projectDir, int &$privateCount): string
    {
        if ($projectDir === '') {
            return basename($path);
        }

        $realPath = realpath($path);
        $realProjectDir = realpath($projectDir);

        if ($realPath === false || $realProjectDir === false) {
            return ':private-' . $privateCount++;
        }

        if (str_starts_with($realPath, $realProjectDir . '/')) {
            return '/' . substr($realPath, \strlen($realProjectDir) + 1);
        }

        return ':private-' . $privateCount++;
    }

    /**
     * Encode a number using VLQ (Variable Length Quantity) base64.
     */
    private function encodeVlq(int $value): string
    {
        $encoded = '';
        $vlq = $value < 0 ? ((-$value) << 1) | 1 : $value << 1;

        do {
            $digit = $vlq & 0x1F;
            $vlq >>= 5;

            if ($vlq > 0) {
                $digit |= 0x20; // Set continuation bit
            }

            $encoded .= self::VLQ_CHARS[$digit];
        } while ($vlq > 0);

        return $encoded;
    }
}
