<?php

declare(strict_types=1);

namespace PsychoB\Backlog\Theme\Service;

use const PATHINFO_EXTENSION;

use RuntimeException;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Combines multiple CSS/JS files into a single cached output with intelligent cache invalidation.
 */
final class ThemeCombiner
{
    /**
     * @var array<string, string>
     */
    private readonly array $paths;

    /**
     * @var array<string, list<string>>
     */
    private readonly array $files;

    /**
     * @param array<string, string>       $themePaths
     * @param array<string, list<string>> $themeFiles
     */
    public function __construct(
        array $themePaths,
        array $themeFiles,
        private readonly string $projectDir,
        private readonly bool $sourcemaps,
        private readonly SourceMapGenerator $sourceMapGenerator,
        private readonly CacheInterface $cache,
        private readonly ?Stopwatch $stopwatch = null,
    ) {
        // Fix paths that may have been mangled by container compilation
        $this->paths = $this->fixPaths($themePaths);
        $this->files = $themeFiles;
    }

    /**
     * Fix paths that may have been corrupted by Symfony's container compilation.
     *
     * @param array<string, string> $paths
     *
     * @return array<string, string>
     */
    private function fixPaths(array $paths): array
    {
        $fixed = [];

        foreach ($paths as $path => $namespace) {
            // Replace any mangled project dir with the correct one
            if (str_contains($path, '/src/') || str_contains($path, '/tests/')) {
                // Safe cast: str_contains check guarantees the pattern matches
                $relativePart = (string)preg_replace('#^.*/((src|tests)/.*)$#', '$1', $path);
                $fixed[$this->projectDir . '/' . $relativePart] = $namespace;
            } else {
                $fixed[$path] = $namespace;
            }
        }

        return $fixed;
    }

    public function getCombinedFile(string $name): CombinedFileResult
    {
        $this->stopwatch?->start('ThemeCombiner.getCombinedFile', 'services');

        try {
            if (!isset($this->files[$name])) {
                throw new RuntimeException(\sprintf('Theme file "%s" is not configured.', $name));
            }

            $sourceFiles = $this->files[$name];
            $resolvedPaths = $this->resolveAllPaths($sourceFiles);
            $mtimes = $this->collectMtimes($resolvedPaths);

            if ($mtimes === []) {
                throw new RuntimeException(\sprintf('Theme file "%s" has no source files.', $name));
            }

            $hash = $this->generateHash($resolvedPaths, $mtimes);
            $extension = pathinfo($name, PATHINFO_EXTENSION);
            $cacheKey = 'theme_' . $hash;

            /** @var CombinedFileResult $result */
            return $this->cache->get(
                $cacheKey,
                fn(ItemInterface $item): CombinedFileResult => $this->generateCombinedFile(
                    $resolvedPaths,
                    $mtimes,
                    $hash,
                    $extension
                )
            );
        } finally {
            $this->stopwatch?->stop('ThemeCombiner.getCombinedFile');
        }
    }

    /**
     * Get a source map by hash (for serving via controller).
     */
    public function getSourceMap(string $hash): ?string
    {
        $cacheKey = 'theme_' . $hash;

        /** @var CombinedFileResult|null $result */
        $result = $this->cache->get($cacheKey, fn() => null);

        return $result?->sourceMapContent;
    }

    /**
     * @param list<string> $sourceFiles
     *
     * @return list<string>
     */
    private function resolveAllPaths(array $sourceFiles): array
    {
        return array_map($this->resolvePath(...), $sourceFiles);
    }

    private function resolvePath(string $path): string
    {
        if (!str_starts_with($path, '@')) {
            return $path;
        }

        $withoutAt = substr($path, 1);
        $slashPos = strpos($withoutAt, '/');

        if ($slashPos === false) {
            throw new RuntimeException(\sprintf('Invalid theme path format: "%s"', $path));
        }

        $namespace = substr($withoutAt, 0, $slashPos);
        $relativePath = substr($withoutAt, $slashPos + 1);

        foreach ($this->paths as $basePath => $name) {
            if ($name === $namespace) {
                return $basePath . '/' . $relativePath;
            }
        }

        throw new RuntimeException(\sprintf('Theme namespace "%s" is not configured.', $namespace));
    }

    /**
     * @param list<string> $resolvedPaths
     *
     * @return list<int>
     */
    private function collectMtimes(array $resolvedPaths): array
    {
        $mtimes = [];

        foreach ($resolvedPaths as $path) {
            if (!file_exists($path)) {
                throw new RuntimeException(\sprintf('Theme source file does not exist: "%s"', $path));
            }

            $mtime = filemtime($path);

            if ($mtime === false) {
                throw new RuntimeException(\sprintf('Could not get mtime for file: "%s"', $path));
            }

            $mtimes[] = $mtime;
        }

        return $mtimes;
    }

    /**
     * @param list<string> $resolvedPaths
     * @param list<int>    $mtimes
     */
    private function generateHash(array $resolvedPaths, array $mtimes): string
    {
        $data = \count($resolvedPaths) . implode('', $resolvedPaths) . implode('', array_map(strval(...), $mtimes));

        return md5($data);
    }

    /**
     * @param list<string> $resolvedPaths
     * @param list<int>    $mtimes
     */
    private function generateCombinedFile(
        array $resolvedPaths,
        array $mtimes,
        string $hash,
        string $extension,
    ): CombinedFileResult {
        $this->stopwatch?->start('ThemeCombiner.generateCombinedFile', 'services');

        try {
            $combined = '';
            $contents = [];

            foreach ($resolvedPaths as $path) {
                $content = file_get_contents($path);

                if ($content === false) {
                    throw new RuntimeException(\sprintf('Could not read file: "%s"', $path));
                }

                $contents[] = $content;
                $combined .= $content . "\n";
            }

            $sourceMapContent = null;

            // Generate source map if enabled
            if ($this->sourcemaps) {
                $mapFilename = $hash . '.' . $extension . '.map';
                $sourceMapContent = $this->sourceMapGenerator->generate(
                    $resolvedPaths,
                    $contents,
                    $hash . '.' . $extension,
                    $this->projectDir,
                );

                // Append sourceMappingURL comment
                $combined .= $this->getSourceMapComment($extension, $mapFilename);
            }

            return new CombinedFileResult(
                content: $combined,
                hash: $hash,
                lastModified: $mtimes === [] ? 0 : max($mtimes),
                contentType: $this->getContentType($extension),
                sourceMapContent: $sourceMapContent,
            );
        } finally {
            $this->stopwatch?->stop('ThemeCombiner.generateCombinedFile');
        }
    }

    private function getContentType(string $extension): string
    {
        return match ($extension) {
            'css'   => 'text/css',
            'js'    => 'application/javascript',
            default => throw new RuntimeException(
                \sprintf('Content type for extension "%s" is not supported.', $extension)
            ),
        };
    }

    private function getSourceMapComment(string $extension, string $mapFilename): string
    {
        return match ($extension) {
            'css'   => "\n/*# sourceMappingURL={$mapFilename} */\n",
            'js'    => "\n//# sourceMappingURL={$mapFilename}\n",
            default => '',
        };
    }
}
