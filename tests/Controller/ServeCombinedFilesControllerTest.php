<?php

declare(strict_types=1);

namespace Tests\PsychoB\Backlog\Theme\Controller;

use Override;
use Psr\Cache\CacheItemPoolInterface;
use PsychoB\Backlog\Theme\Service\ThemeCombiner;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;
use Tests\PsychoB\Backlog\WebBacklogTestCase;

/**
 * Functional tests for ServeCombinedFilesController.
 */
final class ServeCombinedFilesControllerTest extends WebBacklogTestCase
{
    private KernelBrowser $client;

    #[Override]
    protected function setUp(): void
    {
        $this->client = self::createClient();

        // Clear theme cache to ensure fresh state
        $cache = self::getContainer()->get('cache.theme');
        \assert($cache instanceof CacheItemPoolInterface);
        $cache->clear();
    }

    public function testServeReturnsOkForConfiguredFile(): void
    {
        $this->client->request('GET', '/_/theme/frontend.css');

        self::assertResponseIsSuccessful();
    }

    public function testServeReturnsCssContentType(): void
    {
        $this->client->request('GET', '/_/theme/frontend.css');

        $response = $this->client->getResponse();
        self::assertStringStartsWith('text/css', $response->headers->get('Content-Type') ?? '');
    }

    public function testServeReturnsEtagHeader(): void
    {
        $this->client->request('GET', '/_/theme/frontend.css');

        $response = $this->client->getResponse();
        $etag = $response->headers->get('ETag');

        self::assertNotNull($etag);
        self::assertMatchesRegularExpression('/^"[a-f0-9]{32}"$/', $etag);
    }

    public function testServeReturnsLastModifiedHeader(): void
    {
        $this->client->request('GET', '/_/theme/frontend.css');

        $response = $this->client->getResponse();
        $lastModified = $response->headers->get('Last-Modified');

        self::assertNotNull($lastModified);
    }

    public function testServeReturnsCacheControlHeader(): void
    {
        $this->client->request('GET', '/_/theme/frontend.css');

        $response = $this->client->getResponse();
        $cacheControl = $response->headers->get('Cache-Control');

        self::assertNotNull($cacheControl);
        self::assertStringContainsString('must-revalidate', $cacheControl);
        self::assertStringContainsString('public', $cacheControl);
    }

    public function testServeReturnsNotModifiedForMatchingEtag(): void
    {
        // First request to get the ETag
        $this->client->request('GET', '/_/theme/frontend.css');
        $etag = $this->client->getResponse()
            ->headers->get('ETag')
        ;

        // Second request with If-None-Match
        $this->client->request('GET', '/_/theme/frontend.css', [], [], [
            'HTTP_IF_NONE_MATCH' => $etag,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_MODIFIED);
    }

    public function testServeReturnsNotModifiedForMatchingIfModifiedSince(): void
    {
        // First request to get the Last-Modified
        $this->client->request('GET', '/_/theme/frontend.css');

        // Second request with If-Modified-Since set to a future date
        $futureDate = gmdate('D, d M Y H:i:s', time() + 3_600) . ' GMT';
        $this->client->request('GET', '/_/theme/frontend.css', [], [], [
            'HTTP_IF_MODIFIED_SINCE' => $futureDate,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_MODIFIED);
    }

    public function testServeReturnsContentForStaleIfModifiedSince(): void
    {
        // Request with an old If-Modified-Since date
        $oldDate = 'Mon, 01 Jan 2020 00:00:00 GMT';
        $this->client->request('GET', '/_/theme/frontend.css', [], [], [
            'HTTP_IF_MODIFIED_SINCE' => $oldDate,
        ]);

        self::assertResponseIsSuccessful();
        self::assertNotEmpty($this->client->getResponse()->getContent());
    }

    public function testServeReturnsFullContentForNonMatchingEtag(): void
    {
        $this->client->request('GET', '/_/theme/frontend.css', [], [], [
            'HTTP_IF_NONE_MATCH' => '"invalid-etag"',
        ]);

        self::assertResponseIsSuccessful();
        self::assertNotEmpty($this->client->getResponse()->getContent());
    }

    public function testServeSourceMapReturnsNotFoundForMissingMap(): void
    {
        $this->client->request('GET', '/_/theme/0000000000000000000000000000dead.css.map');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testServeSourceMapReturnsOkForExistingMap(): void
    {
        /** @var ThemeCombiner $combiner */
        $combiner = self::getContainer()->get(ThemeCombiner::class);
        $result = $combiner->getCombinedFile('frontend.css');

        // Only test source map endpoint if source maps are enabled
        if ($result->sourceMapContent === null) {
            self::markTestSkipped('Source maps are disabled in test environment.');
        }

        $this->client->request('GET', '/_/theme/' . $result->hash . '.css.map');

        self::assertResponseIsSuccessful();
    }

    public function testServeSourceMapReturnsJsonContentType(): void
    {
        /** @var ThemeCombiner $combiner */
        $combiner = self::getContainer()->get(ThemeCombiner::class);
        $result = $combiner->getCombinedFile('frontend.css');

        if ($result->sourceMapContent === null) {
            self::markTestSkipped('Source maps are disabled in test environment.');
        }

        $this->client->request('GET', '/_/theme/' . $result->hash . '.css.map');

        $response = $this->client->getResponse();
        self::assertSame('application/json', $response->headers->get('Content-Type'));
    }

    public function testServeSourceMapReturnsCacheControlHeader(): void
    {
        /** @var ThemeCombiner $combiner */
        $combiner = self::getContainer()->get(ThemeCombiner::class);
        $result = $combiner->getCombinedFile('frontend.css');

        if ($result->sourceMapContent === null) {
            self::markTestSkipped('Source maps are disabled in test environment.');
        }

        $this->client->request('GET', '/_/theme/' . $result->hash . '.css.map');

        $cacheControl = $this->client->getResponse()
            ->headers->get('Cache-Control')
        ;
        self::assertStringContainsString('immutable', $cacheControl ?? '');
    }

    public function testServeAdminCssReturnsOk(): void
    {
        $this->client->request('GET', '/_/theme/admin.css');

        self::assertResponseIsSuccessful();
        self::assertStringStartsWith('text/css', $this->client->getResponse()->headers->get('Content-Type') ?? '');
    }

    public function testServeCssContentIsNotEmpty(): void
    {
        $this->client->request('GET', '/_/theme/frontend.css');

        $content = $this->client->getResponse()
            ->getContent()
        ;
        self::assertNotEmpty($content);
        self::assertIsString($content);
    }
}
