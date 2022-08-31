<?php

namespace JsonSchema\Tests\Exception;

use JsonSchema\Exception\InvalidArgumentException;
use LegacyPHPUnit\TestCase;

class InvalidArgumentExceptionTest extends TestCase
{
    public function testHierarchy()
    {
        $exception = new InvalidArgumentException();
        self::assertInstanceOf('\InvalidArgumentException', $exception);
        self::assertInstanceOf('\JsonSchema\Exception\ExceptionInterface', $exception);
    }
}
