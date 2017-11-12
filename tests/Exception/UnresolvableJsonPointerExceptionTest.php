<?php

namespace JsonSchema\Tests\Exception;

use JsonSchema\Exception\UnresolvableJsonPointerException;
use PHPUnit\Framework\TestCase;

class UnresolvableJsonPointerExceptionTest extends TestCase
{
    public function testHierarchy()
    {
        $exception = new UnresolvableJsonPointerException();
        self::assertInstanceOf('\InvalidArgumentException', $exception);
        self::assertInstanceOf('\JsonSchema\Exception\InvalidArgumentException', $exception);
        self::assertInstanceOf('\JsonSchema\Exception\ExceptionInterface', $exception);
    }
}
