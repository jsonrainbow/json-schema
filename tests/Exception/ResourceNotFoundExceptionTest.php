<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Exception;

use JsonSchema\Exception\ResourceNotFoundException;
use PHPUnit\Framework\TestCase;

class ResourceNotFoundExceptionTest extends TestCase
{
    public function testHierarchy(): void
    {
        $exception = new ResourceNotFoundException();
        self::assertInstanceOf('\RuntimeException', $exception);
        self::assertInstanceOf('\JsonSchema\Exception\RuntimeException', $exception);
        self::assertInstanceOf('\JsonSchema\Exception\ExceptionInterface', $exception);
    }
}
