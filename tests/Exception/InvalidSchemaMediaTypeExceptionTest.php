<?php

namespace JsonSchema\Tests\Exception;

use JsonSchema\Exception\InvalidSchemaMediaTypeException;
use LegacyPHPUnit\TestCase;

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
