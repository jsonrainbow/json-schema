<?php

namespace JsonSchema\Tests\Uri\Retrievers
{
    use JsonSchema\Tests\Constraints\VeryBaseTestCase;
use JsonSchema\Uri\Retrievers\FileGetContents;

    /**
     * @group FileGetContents
     */
    class FileGetContentsTest extends VeryBaseTestCase
    {
        public function testFetchMissingFile()
        {
            $res = new FileGetContents();

            $this->expectExceptionCompat('\JsonSchema\Exception\ResourceNotFoundException');
            $res->retrieve(__DIR__ . '/Fixture/missing.json');
        }

        public function testFetchFile()
        {
            $res = new FileGetContents();
            $result = $res->retrieve(__DIR__ . '/../Fixture/child.json');
            $this->assertNotEmpty($result);
        }

        public function testFalseReturn()
        {
            $res = new FileGetContents();

            $this->expectExceptionCompat(
                '\JsonSchema\Exception\ResourceNotFoundException',
                'JSON schema not found at http://example.com/false'
            );
            $res->retrieve('http://example.com/false');
        }

        public function testFetchDirectory()
        {
            $res = new FileGetContents();

            $this->expectExceptionCompat(
                '\JsonSchema\Exception\ResourceNotFoundException',
                'JSON schema not found at file:///this/is/a/directory/'
            );
            $res->retrieve('file:///this/is/a/directory/');
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
    }
}

namespace JsonSchema\Uri\Retrievers
{
    function file_get_contents($uri)
    {
        switch ($uri) {
            case 'http://example.com/false': return false;
            case 'file:///this/is/a/directory/': return '';
            default: return \file_get_contents($uri);
        }
    }
}
