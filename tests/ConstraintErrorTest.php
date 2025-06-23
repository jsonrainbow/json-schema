<?php

declare(strict_types=1);

namespace JsonSchema\Tests;

use JsonSchema\ConstraintError;
use PHPUnit\Framework\TestCase;

class ConstraintErrorTest extends TestCase
{
    public function testGetValidMessage(): void
    {
        $e = ConstraintError::ALL_OF();
        $this->assertEquals('Failed to match all schemas', $e->getMessage());
    }

    public function testGetInvalidMessage(): void
    {
        $e = ConstraintError::MISSING_ERROR();

        $this->expectException('\JsonSchema\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Missing error message for missingError');

        $e->getMessage();
    }
}
