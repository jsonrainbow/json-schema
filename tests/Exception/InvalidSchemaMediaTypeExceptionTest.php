<?php

namespace JsonSchema\Tests\Exception;

use JsonSchema\Exception\InvalidSchemaMediaTypeException;

class InvalidSchemaMediaTypeExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testHierarchy()
    {
        $exception = new InvalidSchemaMediaTypeException();
        self::assertInstanceOf('\RuntimeException', $exception);
        self::assertInstanceOf('\JsonSchema\Exception\RuntimeException', $exception);
        self::assertInstanceOf('\JsonSchema\Exception\ExceptionInterface', $exception);
    }
}
