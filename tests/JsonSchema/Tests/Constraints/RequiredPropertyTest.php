<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

use JsonSchema\Constraints\UndefinedConstraint;

class RequiredPropertyTest extends \PHPUnit_Framework_TestCase
{
    public function testErrorPropertyIsPopulatedForRequiredIfMissingInInput()
    {
        $validator = new UndefinedConstraint();
        $document = json_decode(
            '{
            "bar": 42
        }'
        );
        $schema = json_decode(
            '{
            "type": "object",
            "properties": {
                "foo": {"type": "number"},
                "bar": {"type": "number"}
            },
            "required": ["foo"]
        }'
        );

        $validator->check($document, $schema);
        $error = $validator->getErrors();
        $this->assertErrorHasExpectedPropertyValue($error, "foo");
    }

    public function testErrorPropertyIsPopulatedForRequiredIfEmptyValueInInput()
    {
        $validator = new UndefinedConstraint();
        $document = json_decode(
            '{
            "bar": 42,
            "foo": null
        }'
        );
        $schema = json_decode(
            '{
            "type": "object",
            "properties": {
                "foo": {"type": "number"},
                "bar": {"type": "number"}
            },
            "required": ["foo"]
        }'
        );

        $validator->check($document, $schema);
        $error = $validator->getErrors();
        $this->assertErrorHasExpectedPropertyValue($error, "foo");
    }

    protected function assertErrorHasExpectedPropertyValue($error, $propertyValue)
    {
        if (!(isset($error[0]) && is_array($error[0]) && isset($error[0]['property']))) {
            $this->fail(
                "Malformed error response. Expected to have subset in form: array(0 => array('property' => <value>)))"
                . " . Error response was: " . json_encode($error)
            );
        }
        $this->assertEquals($propertyValue, $error[0]['property']);

    }
}
