<?php

namespace JsonSchema\Tests\Exception;

use JsonSchema\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class InvalidArgumentExceptionTest extends TestCase
{
    public function testHierarchy(): void
    {
        $exception = new InvalidArgumentException();
        self::assertInstanceOf('\InvalidArgumentException', $exception);
        self::assertInstanceOf('\JsonSchema\Exception\ExceptionInterface', $exception);
    }
}
