<?php

namespace JsonSchema\Tests\Uri\Retrievers;

use JsonSchema\Uri\Retrievers\FileGetContents;
use PHPUnit\Framework\TestCase;

/**
 * @group FileGetContents
 */
class FileGetContentsTest extends TestCase
{
    /**
     * @expectedException \JsonSchema\Exception\ResourceNotFoundException
     */
    public function testFetchMissingFile()
    {
        $res = new FileGetContents();
        $res->retrieve(__DIR__ . '/Fixture/missing.json');
    }

    public function testFetchFile()
    {
        $res = new FileGetContents();
        $result = $res->retrieve(__DIR__ . '/../Fixture/child.json');
        $this->assertNotEmpty($result);
    }

    public function testContentType()
    {
        $res = new FileGetContents();

        $reflector = new \ReflectionObject($res);
        $fetchContentType = $reflector->getMethod('fetchContentType');
        $fetchContentType->setAccessible(true);

        $this->assertTrue($fetchContentType->invoke($res, array('Content-Type: application/json')));
        $this->assertFalse($fetchContentType->invoke($res, array('X-Some-Header: whateverValue')));
    }

    public function testCanHandleHttp301PermanentRedirect()
    {
        $res = new FileGetContents();

        $res->retrieve('http://asyncapi.com/definitions/2.0.0/asyncapi.json');

        $this->assertSame('application/schema+json', $res->getContentType());
    }
}
