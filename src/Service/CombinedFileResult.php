<?php

declare(strict_types=1);

namespace PsychoB\Backlog\Theme\Service;

/**
 * Result of combining multiple files into a single cached output.
 */
final readonly class CombinedFileResult
{
    public function __construct(
        public string $content,
        public string $hash,
        public int $lastModified,
        public string $contentType,
        public ?string $sourceMapContent = null,
    ) {}
}
