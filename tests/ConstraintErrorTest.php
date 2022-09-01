<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests;

use JsonSchema\ConstraintError;
use LegacyPHPUnit\TestCase;
use PHPUnitGoodPractices\Polyfill\PolyfillTrait;

class ConstraintErrorTest extends TestCase
{
    use PolyfillTrait;

    public function testGetValidMessage()
    {
        $e = ConstraintError::ALL_OF();
        $this->assertEquals('Failed to match all schemas', $e->getMessage());
    }

    public function testGetInvalidMessage()
    {
        $e = ConstraintError::MISSING_ERROR();

        $this->expectException(
            '\JsonSchema\Exception\InvalidArgumentException',
            'Missing error message for missingError'
        );
        $e->getMessage();
    }
}
