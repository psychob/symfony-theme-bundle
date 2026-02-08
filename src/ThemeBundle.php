<?php

declare(strict_types=1);

namespace PsychoB\Theme;

use Override;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Bundle for serving combined CSS/JS theme files with intelligent caching.
 */
final class ThemeBundle extends Bundle
{
    #[Override]
    public function getPath(): string
    {
        return __DIR__;
    }
}
