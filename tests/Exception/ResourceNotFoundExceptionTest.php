<?php

namespace JsonSchema\Tests\Exception;

use JsonSchema\Exception\ResourceNotFoundException;

class ResourceNotFoundExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testHierarchy()
    {
        $exception = new ResourceNotFoundException();
        self::assertInstanceOf('\RuntimeException', $exception);
        self::assertInstanceOf('\JsonSchema\Exception\RuntimeException', $exception);
        self::assertInstanceOf('\JsonSchema\Exception\ExceptionInterface', $exception);
    }
}
