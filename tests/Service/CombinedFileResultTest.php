<?php

declare(strict_types=1);

namespace Tests\PsychoB\Theme\Service;

use PHPUnit\Framework\TestCase;
use PsychoB\Theme\Service\CombinedFileResult;
use ReflectionClass;

/**
 * Tests for CombinedFileResult DTO.
 */
final class CombinedFileResultTest extends TestCase
{
    public function testConstructorSetsAllProperties(): void
    {
        $result = new CombinedFileResult(
            content: 'body { margin: 0; }',
            hash: 'abc123',
            lastModified: 1_700_000_000,
            contentType: 'text/css',
            sourceMapContent: '{"version":3}',
        );

        self::assertSame('body { margin: 0; }', $result->content);
        self::assertSame('abc123', $result->hash);
        self::assertSame(1_700_000_000, $result->lastModified);
        self::assertSame('text/css', $result->contentType);
        self::assertSame('{"version":3}', $result->sourceMapContent);
    }

    public function testSourceMapContentIsOptional(): void
    {
        $result = new CombinedFileResult(
            content: 'body {}',
            hash: 'abc123',
            lastModified: 1_700_000_000,
            contentType: 'text/css',
        );

        self::assertNull($result->sourceMapContent);
    }

    public function testIsReadonly(): void
    {
        $result = new CombinedFileResult(
            content: 'body {}',
            hash: 'abc123',
            lastModified: 1_700_000_000,
            contentType: 'text/css',
        );

        $reflection = new ReflectionClass($result);

        self::assertTrue($reflection->isReadOnly());
    }
}
