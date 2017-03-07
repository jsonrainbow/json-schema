<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests;

use JsonSchema\ConstraintError;

class ConstraintErrorTest extends \PHPUnit_Framework_TestCase
{
    public function testGetValidMessage()
    {
        $e = ConstraintError::ALL_OF();
        $this->assertEquals('Failed to match all schemas', $e->getMessage());
    }

    public function testGetInvalidMessage()
    {
        $e = ConstraintError::ALL_OF();

        // set value to something silly
        $r = new \ReflectionClass('\MabeEnum\Enum');
        $v = $r->getProperty('value');
        $v->setAccessible(true);
        $v->setValue($e, 'notARealErrorName');

        $this->setExpectedException(
            '\JsonSchema\Exception\InvalidArgumentException',
            'Missing error message for notARealErrorName'
        );
        $e->getMessage();
    }
}
