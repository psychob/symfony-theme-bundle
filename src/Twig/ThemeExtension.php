<?php

declare(strict_types=1);

namespace PsychoB\Theme\Twig;

use Override;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provides themeAsset() Twig function for generating URLs to combined theme files.
 */
final class ThemeExtension extends AbstractExtension
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {}

    #[Override]
    public function getFunctions(): array
    {
        return [new TwigFunction('themeAsset', $this->themeAsset(...))];
    }

    public function themeAsset(string $name): string
    {
        return $this->urlGenerator->generate('theme_serve', ['file' => $name]);
    }
}
