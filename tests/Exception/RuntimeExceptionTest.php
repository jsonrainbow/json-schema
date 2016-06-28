<?php

namespace JsonSchema\Tests\Exception;

use JsonSchema\Exception\RuntimeException;

class RuntimeExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testHierarchy()
    {
        $exception = new RuntimeException();
        self::assertInstanceOf('\RuntimeException', $exception);
        self::assertInstanceOf('\JsonSchema\Exception\ExceptionInterface', $exception);
    }
}
