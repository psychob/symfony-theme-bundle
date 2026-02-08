<?php

declare(strict_types=1);

namespace PsychoB\Theme\Service;

/**
 * Provides combined CSS/JS theme files and their source maps.
 */
interface ThemeCombinerInterface
{
    public function getCombinedFile(string $name): CombinedFileResult;

    /**
     * Get a source map by hash (for serving via controller).
     */
    public function getSourceMap(string $hash): ?string;
}
