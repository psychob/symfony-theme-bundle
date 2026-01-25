<?php

declare(strict_types=1);

namespace PsychoB\Backlog\Theme\Controller;

use PsychoB\Backlog\Theme\Service\ThemeCombiner;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Serves combined CSS/JS theme files with HTTP caching support.
 */
#[Route('/_/theme')]
final class ServeCombinedFilesController
{
    public function __construct(
        private readonly ThemeCombiner $combiner,
    ) {}

    #[Route('/{hash}.{ext}.map', name: 'theme_serve_map', methods: ['GET'], requirements: [
        'hash' => '[a-f0-9]+',
        'ext'  => 'css|js',
    ])]
    public function serveSourceMap(string $hash): Response
    {
        $content = $this->combiner->getSourceMap($hash);

        if ($content === null) {
            throw new NotFoundHttpException('Source map not found.');
        }

        $response = new Response($content, Response::HTTP_OK, ['Content-Type' => 'application/json']);
        $response->setPublic();
        $response->setMaxAge(31_536_000);
        $response->setImmutable();

        return $response;
    }

    #[Route('/{file}', name: 'theme_serve', methods: ['GET'])]
    public function serve(string $file, Request $request): Response
    {
        $result = $this->combiner->getCombinedFile($file);
        $etag = '"' . $result->hash . '"';

        // Check If-None-Match (ETag) - most reliable
        if ($request->headers->get('If-None-Match') === $etag) {
            return new Response('', Response::HTTP_NOT_MODIFIED);
        }

        // Check If-Modified-Since header
        $ifModifiedSince = $request->headers->get('If-Modified-Since');

        if ($ifModifiedSince !== null) {
            $ifModifiedTime = strtotime($ifModifiedSince);

            if ($ifModifiedTime !== false && $ifModifiedTime >= $result->lastModified) {
                return new Response('', Response::HTTP_NOT_MODIFIED);
            }
        }

        $response = new Response($result->content, Response::HTTP_OK, [
            'Content-Type'  => $result->contentType,
            'Last-Modified' => gmdate('D, d M Y H:i:s', $result->lastModified) . ' GMT',
            'ETag'          => $etag,
        ]);
        $response->setPublic();
        $response->headers->addCacheControlDirective('must-revalidate');

        return $response;
    }
}
