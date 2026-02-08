<?php

declare(strict_types=1);

namespace Tests\PsychoB\Theme\Controller;

use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PsychoB\Theme\Controller\ServeCombinedFilesController;
use PsychoB\Theme\Service\CombinedFileResult;
use PsychoB\Theme\Service\ThemeCombinerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\PsychoB\Theme\ThemeTestCase;

/**
 * Unit tests for ServeCombinedFilesController.
 */
final class ServeCombinedFilesControllerTest extends ThemeTestCase
{
    private ServeCombinedFilesController $controller;
    private ThemeCombinerInterface&MockObject $combiner;

    #[Override]
    protected function setUp(): void
    {
        $this->combiner = $this->createMock(ThemeCombinerInterface::class);
        $this->controller = new ServeCombinedFilesController($this->combiner);
    }

    private function createResult(
        string $content = 'body { color: red; }',
        string $hash = 'abc123def456abc123def456abc123de',
        int $lastModified = 1_700_000_000,
        string $contentType = 'text/css',
        ?string $sourceMapContent = null,
    ): CombinedFileResult {
        return new CombinedFileResult($content, $hash, $lastModified, $contentType, $sourceMapContent);
    }

    public function testServeReturnsOkForConfiguredFile(): void
    {
        $this->combiner->method('getCombinedFile')->willReturn($this->createResult());

        $response = $this->controller->serve('frontend.css', new Request());

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testServeReturnsCssContentType(): void
    {
        $this->combiner->method('getCombinedFile')->willReturn($this->createResult());

        $response = $this->controller->serve('frontend.css', new Request());

        self::assertStringStartsWith('text/css', $response->headers->get('Content-Type') ?? '');
    }

    public function testServeReturnsJsContentType(): void
    {
        $this->combiner->method('getCombinedFile')
            ->willReturn($this->createResult(content: 'var x=1;', contentType: 'application/javascript'));

        $response = $this->controller->serve('app.js', new Request());

        self::assertSame('application/javascript', $response->headers->get('Content-Type'));
    }

    public function testServeReturnsEtagHeader(): void
    {
        $this->combiner->method('getCombinedFile')->willReturn($this->createResult());

        $response = $this->controller->serve('frontend.css', new Request());

        $etag = $response->headers->get('ETag');
        self::assertNotNull($etag);
        self::assertMatchesRegularExpression('/^"[a-f0-9]+"$/', $etag);
    }

    public function testServeReturnsLastModifiedHeader(): void
    {
        $this->combiner->method('getCombinedFile')->willReturn($this->createResult());

        $response = $this->controller->serve('frontend.css', new Request());

        self::assertNotNull($response->headers->get('Last-Modified'));
    }

    public function testServeReturnsCacheControlHeader(): void
    {
        $this->combiner->method('getCombinedFile')->willReturn($this->createResult());

        $response = $this->controller->serve('frontend.css', new Request());

        $cacheControl = $response->headers->get('Cache-Control');
        self::assertNotNull($cacheControl);
        self::assertStringContainsString('must-revalidate', $cacheControl);
        self::assertStringContainsString('public', $cacheControl);
    }

    public function testServeReturnsNotModifiedForMatchingEtag(): void
    {
        $result = $this->createResult();
        $this->combiner->method('getCombinedFile')->willReturn($result);

        $request = new Request();
        $request->headers->set('If-None-Match', '"' . $result->hash . '"');

        $response = $this->controller->serve('frontend.css', $request);

        self::assertSame(Response::HTTP_NOT_MODIFIED, $response->getStatusCode());
    }

    public function testServeReturnsNotModifiedForMatchingIfModifiedSince(): void
    {
        $result = $this->createResult();
        $this->combiner->method('getCombinedFile')->willReturn($result);

        $futureDate = gmdate('D, d M Y H:i:s', $result->lastModified + 3_600) . ' GMT';
        $request = new Request();
        $request->headers->set('If-Modified-Since', $futureDate);

        $response = $this->controller->serve('frontend.css', $request);

        self::assertSame(Response::HTTP_NOT_MODIFIED, $response->getStatusCode());
    }

    public function testServeReturnsContentForStaleIfModifiedSince(): void
    {
        $this->combiner->method('getCombinedFile')->willReturn($this->createResult());

        $request = new Request();
        $request->headers->set('If-Modified-Since', 'Mon, 01 Jan 2020 00:00:00 GMT');

        $response = $this->controller->serve('frontend.css', $request);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertNotEmpty($response->getContent());
    }

    public function testServeReturnsFullContentForNonMatchingEtag(): void
    {
        $this->combiner->method('getCombinedFile')->willReturn($this->createResult());

        $request = new Request();
        $request->headers->set('If-None-Match', '"invalid-etag"');

        $response = $this->controller->serve('frontend.css', $request);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertNotEmpty($response->getContent());
    }

    public function testServeReturnsCorrectContent(): void
    {
        $this->combiner->method('getCombinedFile')
            ->willReturn($this->createResult(content: 'body { margin: 0; }'));

        $response = $this->controller->serve('frontend.css', new Request());

        self::assertSame('body { margin: 0; }', $response->getContent());
    }

    public function testServeSourceMapReturnsNotFoundForMissingMap(): void
    {
        $this->combiner->method('getSourceMap')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->controller->serveSourceMap('deadbeef');
    }

    public function testServeSourceMapReturnsOkForExistingMap(): void
    {
        $this->combiner->method('getSourceMap')->willReturn('{"version":3}');

        $response = $this->controller->serveSourceMap('abc123');

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame('{"version":3}', $response->getContent());
    }

    public function testServeSourceMapReturnsJsonContentType(): void
    {
        $this->combiner->method('getSourceMap')->willReturn('{"version":3}');

        $response = $this->controller->serveSourceMap('abc123');

        self::assertSame('application/json', $response->headers->get('Content-Type'));
    }

    public function testServeSourceMapReturnsCacheControlHeader(): void
    {
        $this->combiner->method('getSourceMap')->willReturn('{"version":3}');

        $response = $this->controller->serveSourceMap('abc123');

        $cacheControl = $response->headers->get('Cache-Control');
        self::assertStringContainsString('immutable', $cacheControl ?? '');
    }
}
