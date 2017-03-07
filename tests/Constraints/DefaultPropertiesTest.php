<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\Factory;
use JsonSchema\SchemaStorage;
use JsonSchema\Validator;

class DefaultPropertiesTest extends VeryBaseTestCase
{
    public function getValidTests()
    {
        return array(
            array(// #0 default value for entire object
                '',
                '{"default":"valueOne"}',
                '"valueOne"'
            ),
            array(// #1 default value in an empty object
                '{}',
                '{"properties":{"propertyOne":{"default":"valueOne"}}}',
                '{"propertyOne":"valueOne"}'
            ),
            array(// #2 default value for top-level property
                '{"propertyOne":"valueOne"}',
                '{"properties":{"propertyTwo":{"default":"valueTwo"}}}',
                '{"propertyOne":"valueOne","propertyTwo":"valueTwo"}'
            ),
            array(// #3 default value for sub-property
                '{"propertyOne":{}}',
                '{"properties":{"propertyOne":{"properties":{"propertyTwo":{"default":"valueTwo"}}}}}',
                '{"propertyOne":{"propertyTwo":"valueTwo"}}'
            ),
            array(// #4 default value for sub-property with sibling
                '{"propertyOne":{"propertyTwo":"valueTwo"}}',
                '{"properties":{"propertyOne":{"properties":{"propertyThree":{"default":"valueThree"}}}}}',
                '{"propertyOne":{"propertyTwo":"valueTwo","propertyThree":"valueThree"}}'
            ),
            array(// #5 default value for top-level property with type check
                '{"propertyOne":"valueOne"}',
                '{"properties":{"propertyTwo":{"default":"valueTwo","type":"string"}}}',
                '{"propertyOne":"valueOne","propertyTwo":"valueTwo"}'
            ),
            array(// #6 default value for top-level property with v3 required check
                '{"propertyOne":"valueOne"}',
                '{"properties":{"propertyTwo":{"default":"valueTwo","required":"true"}}}',
                '{"propertyOne":"valueOne","propertyTwo":"valueTwo"}'
            ),
            array(// #7 default value for top-level property with v4 required check
                '{"propertyOne":"valueOne"}',
                '{"properties":{"propertyTwo":{"default":"valueTwo"}},"required":["propertyTwo"]}',
                '{"propertyOne":"valueOne","propertyTwo":"valueTwo"}'
            ),
            array(// #8 default value for an already set property
                '{"propertyOne":"alreadySetValueOne"}',
                '{"properties":{"propertyOne":{"default":"valueOne"}}}',
                '{"propertyOne":"alreadySetValueOne"}'
            ),
            array(// #9 default item value for an array
                '["valueOne"]',
                '{"type":"array","items":[{},{"type":"string","default":"valueTwo"}]}',
                '["valueOne","valueTwo"]'
            ),
            array(// #10 default item value for an empty array
                '[]',
                '{"type":"array","items":[{"type":"string","default":"valueOne"}]}',
                '["valueOne"]'
            ),
            array(// #11 property without a default available
                '{"propertyOne":"alreadySetValueOne"}',
                '{"properties":{"propertyOne":{"type":"string"}}}',
                '{"propertyOne":"alreadySetValueOne"}'
            ),
            array(// #12 default property value is an object
                '{"propertyOne":"valueOne"}',
                '{"properties":{"propertyTwo":{"default":{}}}}',
                '{"propertyOne":"valueOne","propertyTwo":{}}'
            ),
            array(// #13 default item value is an object
                '[]',
                '{"type":"array","items":[{"default":{}}]}',
                '[{}]'
            ),
            array(// #14 only set required values (draft-04)
                '{}',
                '{
                    "properties": {
                        "propertyOne": {"default": "valueOne"},
                        "propertyTwo": {"default": "valueTwo"}
                    },
                    "required": ["propertyTwo"]
                }',
                '{"propertyTwo":"valueTwo"}',
                Constraint::CHECK_MODE_ONLY_REQUIRED_DEFAULTS
            ),
            array(// #15 only set required values (draft-03)
                '{}',
                '{
                    "properties": {
                        "propertyOne": {"default": "valueOne"},
                        "propertyTwo": {"default": "valueTwo", "required": true}
                    }
                }',
                '{"propertyTwo":"valueTwo"}',
                Constraint::CHECK_MODE_ONLY_REQUIRED_DEFAULTS
            ),
            array(// #16 infinite recursion via $ref
                '{}',
                '{"properties":{"propertyOne": {"$ref": "#","default": {}}}}',
                '{"propertyOne":{}}'
            ),
            array(// #17 default value for null
                'null',
                '{"default":"valueOne"}',
                '"valueOne"'
            )
        );
    }

    /**
     * @dataProvider getValidTests
     */
    public function testValidCases($input, $schema, $expectOutput = null, $checkMode = 0)
    {
        if (is_string($input)) {
            $inputDecoded = json_decode($input);
        } else {
            $inputDecoded = $input;
        }

        $checkMode |= Constraint::CHECK_MODE_APPLY_DEFAULTS;

        $schemaStorage = new SchemaStorage();
        $schemaStorage->addSchema('local://testSchema', json_decode($schema));
        $factory = new Factory($schemaStorage);
        $validator = new Validator($factory);

        $validator->validate($inputDecoded, json_decode('{"$ref": "local://testSchema"}'), $checkMode);

        $this->assertTrue($validator->isValid(), print_r($validator->getErrors(), true));

        if ($expectOutput !== null) {
            $this->assertEquals($expectOutput, json_encode($inputDecoded));
        }
    }

    /**
     * @dataProvider getValidTests
     */
    public function testValidCasesUsingAssoc($input, $schema, $expectOutput = null, $checkMode = 0)
    {
        $input = json_decode($input, true);

        $checkMode |= Constraint::CHECK_MODE_TYPE_CAST;
        self::testValidCases($input, $schema, $expectOutput, $checkMode);
    }

    /**
     * @dataProvider getValidTests
     */
    public function testValidCasesUsingAssocWithoutTypeCast($input, $schema, $expectOutput = null, $checkMode = 0)
    {
        $input = json_decode($input, true);

        self::testValidCases($input, $schema, $expectOutput, $checkMode);
    }

    public function testNoModificationViaReferences()
    {
        $input = json_decode('');
        $schema = json_decode('{"default":{"propertyOne":"valueOne"}}');

        $validator = new Validator();
        $validator->validate($input, $schema, Constraint::CHECK_MODE_TYPE_CAST | Constraint::CHECK_MODE_APPLY_DEFAULTS);

        $this->assertEquals('{"propertyOne":"valueOne"}', json_encode($input));

        $input->propertyOne = 'valueTwo';
        $this->assertEquals('valueOne', $schema->default->propertyOne);
    }

    public function testLeaveBasicTypesAlone()
    {
        $input = json_decode('"ThisIsAString"');
        $schema = json_decode('{"properties": {"propertyOne": {"default": "valueOne"}}}');

        $validator = new Validator();
        $validator->validate($input, $schema, Constraint::CHECK_MODE_APPLY_DEFAULTS);

        $this->assertEquals('"ThisIsAString"', json_encode($input));

        $schema = json_decode('{"items":[{"type":"string","default":"valueOne"}]}');
        $validator->validate($input, $schema, Constraint::CHECK_MODE_APPLY_DEFAULTS);
        $this->assertEquals('"ThisIsAString"', json_encode($input));
    }
}
