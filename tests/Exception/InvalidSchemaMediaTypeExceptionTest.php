<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Exception;

use JsonSchema\Exception\InvalidSchemaMediaTypeException;
use PHPUnit\Framework\TestCase;

class InvalidSchemaMediaTypeExceptionTest extends TestCase
{
    public function testHierarchy(): void
    {
        $exception = new InvalidSchemaMediaTypeException();
        self::assertInstanceOf('\RuntimeException', $exception);
        self::assertInstanceOf(\JsonSchema\Exception\RuntimeException::class, $exception);
        self::assertInstanceOf(\JsonSchema\Exception\ExceptionInterface::class, $exception);
    }
}
