<?php

namespace JsonSchema\Tests\Exception;

use JsonSchema\Exception\InvalidSourceUriException;
use PHPUnit\Framework\TestCase;

class InvalidSourceUriExceptionTest extends TestCase
{
    public function testHierarchy()
    {
        $exception = new InvalidSourceUriException();
        self::assertInstanceOf('\InvalidArgumentException', $exception);
        self::assertInstanceOf('\JsonSchema\Exception\InvalidArgumentException', $exception);
        self::assertInstanceOf('\JsonSchema\Exception\ExceptionInterface', $exception);
    }
}
