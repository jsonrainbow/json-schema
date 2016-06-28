<?php

namespace JsonSchema\Tests\Exception;

use JsonSchema\Exception\UriResolverException;

class UriResolverExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testHierarchy()
    {
        $exception = new UriResolverException();
        self::assertInstanceOf('\RuntimeException', $exception);
        self::assertInstanceOf('\JsonSchema\Exception\RuntimeException', $exception);
        self::assertInstanceOf('\JsonSchema\Exception\ExceptionInterface', $exception);
    }
}
