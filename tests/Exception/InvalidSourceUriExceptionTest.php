<?php

namespace JsonSchema\Tests\Exception;

use JsonSchema\Exception\InvalidSourceUriException;

class InvalidSourceUriExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testHierarchy()
    {
        $exception = new InvalidSourceUriException();
        self::assertInstanceOf('\InvalidArgumentException', $exception);
        self::assertInstanceOf('\JsonSchema\Exception\InvalidArgumentException', $exception);
        self::assertInstanceOf('\JsonSchema\Exception\ExceptionInterface', $exception);
    }
}
