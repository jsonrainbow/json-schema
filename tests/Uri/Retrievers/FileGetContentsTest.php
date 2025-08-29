<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Uri\Retrievers;

use JsonSchema\Uri\Retrievers\FileGetContents;
use PHPUnit\Framework\TestCase;

class FileGetContentsTest extends TestCase
{
    public function testFetchMissingFile(): void
    {
        $res = new FileGetContents();

        $this->expectException(\JsonSchema\Exception\ResourceNotFoundException::class);

        $res->retrieve(__DIR__ . '/Fixture/missing.json');
    }

    public function testFetchFile(): void
    {
        $res = new FileGetContents();
        $result = $res->retrieve(__DIR__ . '/../Fixture/child.json');
        $this->assertNotEmpty($result);
    }

    public function testContentType(): void
    {
        $res = new FileGetContents();

        $reflector = new \ReflectionObject($res);
        $fetchContentType = $reflector->getMethod('fetchContentType');
        if (PHP_VERSION_ID < 80100) {
            $fetchContentType->setAccessible(true);
        }

        $this->assertTrue($fetchContentType->invoke($res, ['Content-Type: application/json']));
        $this->assertFalse($fetchContentType->invoke($res, ['X-Some-Header: whateverValue']));
    }

    public function testCanHandleHttp301PermanentRedirect(): void
    {
        $res = new FileGetContents();

        $res->retrieve('http://asyncapi.com/definitions/2.0.0/asyncapi.json');

        $this->assertSame('application/schema+json', $res->getContentType());
    }
}
