<?php

namespace JsonSchema\Tests\Exception;

use JsonSchema\Exception\UriResolverException;
use PHPUnit\Framework\TestCase;

class UriResolverExceptionTest extends TestCase
{
    public function testHierarchy()
    {
        $exception = new UriResolverException();
        self::assertInstanceOf('\RuntimeException', $exception);
        self::assertInstanceOf('\JsonSchema\Exception\RuntimeException', $exception);
        self::assertInstanceOf('\JsonSchema\Exception\ExceptionInterface', $exception);
    }
}
