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
            array(// default value for entire object
                '',
                '{"default":"valueOne"}',
                '"valueOne"'
            ),
            array(// default value in an empty object
                '{}',
                '{"properties":{"propertyOne":{"default":"valueOne"}}}',
                '{"propertyOne":"valueOne"}'
            ),
            array(// default value for top-level property
                '{"propertyOne":"valueOne"}',
                '{"properties":{"propertyTwo":{"default":"valueTwo"}}}',
                '{"propertyOne":"valueOne","propertyTwo":"valueTwo"}'
            ),
            array(// default value for sub-property
                '{"propertyOne":{}}',
                '{"properties":{"propertyOne":{"properties":{"propertyTwo":{"default":"valueTwo"}}}}}',
                '{"propertyOne":{"propertyTwo":"valueTwo"}}'
            ),
            array(// default value for sub-property with sibling
                '{"propertyOne":{"propertyTwo":"valueTwo"}}',
                '{"properties":{"propertyOne":{"properties":{"propertyThree":{"default":"valueThree"}}}}}',
                '{"propertyOne":{"propertyTwo":"valueTwo","propertyThree":"valueThree"}}'
            ),
            array(// default value for top-level property with type check
                '{"propertyOne":"valueOne"}',
                '{"properties":{"propertyTwo":{"default":"valueTwo","type":"string"}}}',
                '{"propertyOne":"valueOne","propertyTwo":"valueTwo"}'
            ),
            array(// default value for top-level property with v3 required check
                '{"propertyOne":"valueOne"}',
                '{"properties":{"propertyTwo":{"default":"valueTwo","required":"true"}}}',
                '{"propertyOne":"valueOne","propertyTwo":"valueTwo"}'
            ),
            array(// default value for top-level property with v4 required check
                '{"propertyOne":"valueOne"}',
                '{"properties":{"propertyTwo":{"default":"valueTwo"}},"required":["propertyTwo"]}',
                '{"propertyOne":"valueOne","propertyTwo":"valueTwo"}'
            ),
            array(//default value for an already set property
                '{"propertyOne":"alreadySetValueOne"}',
                '{"properties":{"propertyOne":{"default":"valueOne"}}}',
                '{"propertyOne":"alreadySetValueOne"}'
            ),
            array(//default item value for an array
                '["valueOne"]',
                '{"type":"array","items":[{},{"type":"string","default":"valueTwo"}]}',
                '["valueOne","valueTwo"]'
            ),
            array(//default item value for an empty array
                '[]',
                '{"type":"array","items":[{"type":"string","default":"valueOne"}]}',
                '["valueOne"]'
            ),
            array(//property without a default available
                '{"propertyOne":"alreadySetValueOne"}',
                '{"properties":{"propertyOne":{"type":"string"}}}',
                '{"propertyOne":"alreadySetValueOne"}'
            ),
            array(// default property value is an object
                '{"propertyOne":"valueOne"}',
                '{"properties":{"propertyTwo":{"default":{}}}}',
                '{"propertyOne":"valueOne","propertyTwo":{}}'
            ),
            array(// default item value is an object
                '[]',
                '{"type":"array","items":[{"default":{}}]}',
                '[{}]'
            )
        );
    }

    /**
     * @dataProvider getValidTests
     */
    public function testValidCases($input, $schema, $expectOutput = null, $validator = null)
    {
        if (is_string($input)) {
            $inputDecoded = json_decode($input);
        } else {
            $inputDecoded = $input;
        }

        if ($validator === null) {
            $factory = new Factory(null, null, Constraint::CHECK_MODE_APPLY_DEFAULTS);
            $validator = new Validator($factory);
        }
        $validator->validate($inputDecoded, json_decode($schema));

        $this->assertTrue($validator->isValid(), print_r($validator->getErrors(), true));

        if ($expectOutput !== null) {
            $this->assertEquals($expectOutput, json_encode($inputDecoded));
        }
    }

    /**
     * @dataProvider getValidTests
     */
    public function testValidCasesUsingAssoc($input, $schema, $expectOutput = null)
    {
        $input = json_decode($input, true);

        $factory = new Factory(null, null, Constraint::CHECK_MODE_TYPE_CAST | Constraint::CHECK_MODE_APPLY_DEFAULTS);
        self::testValidCases($input, $schema, $expectOutput, new Validator($factory));
    }

    /**
     * @dataProvider getValidTests
     */
    public function testValidCasesUsingAssocWithoutTypeCast($input, $schema, $expectOutput = null)
    {
        $input = json_decode($input, true);
        $factory = new Factory(null, null, Constraint::CHECK_MODE_APPLY_DEFAULTS);
        self::testValidCases($input, $schema, $expectOutput, new Validator($factory));
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
}
