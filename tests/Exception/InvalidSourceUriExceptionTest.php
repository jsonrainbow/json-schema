<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Exception;

use JsonSchema\Exception\InvalidSourceUriException;
use PHPUnit\Framework\TestCase;

class InvalidSourceUriExceptionTest extends TestCase
{
    public function testHierarchy(): void
    {
        $exception = new InvalidSourceUriException();
        self::assertInstanceOf('\InvalidArgumentException', $exception);
        self::assertInstanceOf(\JsonSchema\Exception\InvalidArgumentException::class, $exception);
        self::assertInstanceOf(\JsonSchema\Exception\ExceptionInterface::class, $exception);
    }
}
