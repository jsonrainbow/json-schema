<?php

namespace JsonSchema\Tests\Exception;

use JsonSchema\Exception\InvalidSchemaMediaTypeException;
use PHPUnit\Framework\TestCase;

class InvalidSchemaMediaTypeExceptionTest extends TestCase
{
    public function testHierarchy()
    {
        $exception = new InvalidSchemaMediaTypeException();
        self::assertInstanceOf('\RuntimeException', $exception);
        self::assertInstanceOf('\JsonSchema\Exception\RuntimeException', $exception);
        self::assertInstanceOf('\JsonSchema\Exception\ExceptionInterface', $exception);
    }
}
