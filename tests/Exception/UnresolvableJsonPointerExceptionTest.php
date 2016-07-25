<?php

namespace JsonSchema\Tests\Exception;

use JsonSchema\Exception\UnresolvableJsonPointerException;

class UnresolvableJsonPointerExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testHierarchy()
    {
        $exception = new UnresolvableJsonPointerException();
        self::assertInstanceOf('\InvalidArgumentException', $exception);
        self::assertInstanceOf('\JsonSchema\Exception\InvalidArgumentException', $exception);
        self::assertInstanceOf('\JsonSchema\Exception\ExceptionInterface', $exception);
    }
}
