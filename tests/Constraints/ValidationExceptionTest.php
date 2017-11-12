<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

use JsonSchema\Constraints\Constraint;
use JsonSchema\Exception\ValidationException;
use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;

class ValidationExceptionTest extends TestCase
{
    public function testValidationException()
    {
        $exception = new ValidationException();
        $this->assertInstanceOf('\JsonSchema\Exception\ValidationException', $exception);

        $checkValue = json_decode('{"propertyOne": "thisIsNotAnObject"}');
        $schema = json_decode('{
            "type": "object",
            "additionalProperties": false,
            "properties": {
                "propertyOne": {
                    "type": "object"
                }
            }
        }');

        $validator = new Validator();

        try {
            $validator->validate($checkValue, $schema, Constraint::CHECK_MODE_EXCEPTIONS);
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertEquals(
            'Error validating /propertyOne: String value found, but an object is required',
            $exception->getMessage()
        );

        $this->setExpectedException('JsonSchema\Exception\ValidationException');
        throw $exception;
    }
}
