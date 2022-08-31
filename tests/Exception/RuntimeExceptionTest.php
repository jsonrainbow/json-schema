<?php

namespace JsonSchema\Tests\Exception;

use JsonSchema\Exception\RuntimeException;
use LegacyPHPUnit\TestCase;

class RuntimeExceptionTest extends TestCase
{
    public function testHierarchy()
    {
        $exception = new RuntimeException();
        self::assertInstanceOf('\RuntimeException', $exception);
        self::assertInstanceOf('\JsonSchema\Exception\ExceptionInterface', $exception);
    }
}
