<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Exception;

use JsonSchema\Exception\UriResolverException;
use PHPUnit\Framework\TestCase;

class UriResolverExceptionTest extends TestCase
{
    public function testHierarchy(): void
    {
        $exception = new UriResolverException();
        self::assertInstanceOf('\RuntimeException', $exception);
        self::assertInstanceOf(\JsonSchema\Exception\RuntimeException::class, $exception);
        self::assertInstanceOf(\JsonSchema\Exception\ExceptionInterface::class, $exception);
    }
}
