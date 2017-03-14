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
use JsonSchema\Constraints\TypeCheck\LooseTypeCheck;
use JsonSchema\Validator;

class CoerciveTest extends VeryBaseTestCase
{
    protected $factory = null;

    public function setUp()
    {
        $this->factory = new Factory();
        $this->factory->setConfig(Constraint::CHECK_MODE_TYPE_CAST | Constraint::CHECK_MODE_COERCE_TYPES);
    }

    public function dataCoerceCases()
    {
        // check type conversions
        $types = array(
            // toType
            'string' => array(
                //    fromType      fromValue       toValue         valid   Test Number
                array('string',     '"string"',     'string',       true),  // #0
                array('integer',    '45',           '45',           true),  // #1
                array('boolean',    'true',         'true',         true),  // #2
                array('boolean',    'false',        'false',        true),  // #3
                array('NULL',       'null',         '',             true),  // #4
                array('array',      '[45]',         '45',           true),  // #5
                array('object',     '{"a":"b"}',    null,           false), // #6
                array('array',      '[{"a":"b"}]',  null,           false), // #7
            ),
            'integer' => array(
                array('string',     '"45"',         45,             true),  // #8
                array('integer',    '45',           45,             true),  // #9
                array('boolean',    'true',         1,              true),  // #10
                array('boolean',    'false',        0,              true),  // #11
                array('NULL',       'null',         0,              true),  // #12
                array('array',      '["-45"]',      -45,            true),  // #13
                array('object',     '{"a":"b"}',    null,           false), // #14
            ),
            'boolean' => array(
                array('string',     '"true"',       true,           true),  // #15
                array('integer',    '1',            true,           true),  // #16
                array('boolean',    'true',         true,           true),  // #17
                array('NULL',       'null',         false,          true),  // #18
                array('array',      '["true"]',     true,           true),  // #19
                array('object',     '{"a":"b"}',     null,          false), // #20
                array('string',     '""',           null,           false), // #21
                array('string',     '"ABC"',        null,           false), // #22
                array('integer',    '2',            null,           false), // #23
            ),
            'NULL' => array(
                array('string',     '""',           null,           true),  // #24
                array('integer',    '0',            null,           true),  // #25
                array('boolean',    'false',        null,           true),  // #26
                array('NULL',       'null',         null,           true),  // #27
                array('array',      '[0]',          null,           true),  // #28
                array('object',     '{"a":"b"}',    null,           false), // #29
                array('string',     '"null"',       null,           false), // #30
                array('integer',    '-1',           null,           false), // #31
            ),
            'array' => array(
                array('string',     '"ABC"',        array('ABC'),   true),  // #32
                array('integer',    '45',           array(45),      true),  // #33
                array('boolean',    'true',         array(true),    true),  // #34
                array('NULL',       'null',         array(null),    true),  // #35
                array('array',      '["ABC"]',      array('ABC'),   true),  // #36
                array('object',     '{"a":"b"}',    null,           false), // #37
            ),
        );

        $tests = array();
        foreach ($types as $toType => $testCases) {
            foreach ($testCases as $testCase) {
                $tests[] = array(
                    sprintf('{"properties":{"propertyOne":{"type":"%s"}}}', strtolower($toType)),
                    sprintf('{"propertyOne":%s}', $testCase[1]),
                    $testCase[0],
                    $toType,
                    $testCase[2],
                    $testCase[3]
                );
            }
        }

        // #38 check post-coercion validation (to array)
        $tests[] = array(
            '{"properties":{"propertyOne":{"type":"array","items":[{"type":"number"}]}}}',
            '{"propertyOne":"ABC"}',
            'string', null, null, false
        );

        // #39 check post-coercion validation (from array)
        $tests[] = array(
            '{"properties":{"propertyOne":{"type":"number"}}}',
            '{"propertyOne":["ABC"]}',
            'array', null, null, false
        );

        // #40 check multiple types (first valid)
        $tests[] = array(
            '{"properties":{"propertyOne":{"type":["number", "string"]}}}',
            '{"propertyOne":42}',
            'integer', 'integer', 42, true
        );

        // #41 check multiple types (last valid)
        $tests[] = array(
            '{"properties":{"propertyOne":{"type":["number", "string"]}}}',
            '{"propertyOne":"42"}',
            'string', 'string', '42', true
        );

        // #42 check the meaning of life
        $tests[] = array(
            '{"properties":{"propertyOne":{"type":"any"}}}',
            '{"propertyOne":"42"}',
            'string', 'string', '42', true
        );

        // #43 check turple coercion
        $tests[] = array(
            '{"properties":{"propertyOne":{"type":"array","items":[{"type":"number"},{"type":"string"}]}}}',
            '{"propertyOne":["42", 42]}',
            'array', 'array', array(42, '42'), true
        );

        // #44 check early coercion
        $tests[] = array(
            '{"properties":{"propertyOne":{"type":["object", "number", "string"]}}}',
            '{"propertyOne":"42"}',
            'string', 'integer', 42, true, true
        );

        // #45 check multiple types (none valid)
        $tests[] = array(
            '{"properties":{"propertyOne":{"type":["number", "boolean"]}}}',
            '{"propertyOne":"42"}',
            'string', 'integer', 42, true
        );

        return $tests;
    }

    /** @dataProvider dataCoerceCases **/
    public function testCoerceCases($schema, $data, $startType, $endType, $endValue, $valid, $early = false, $assoc = false)
    {
        if ($early) {
            $this->factory->addConfig(Constraint::CHECK_MODE_EARLY_COERCE);
        }
        $validator = new Validator($this->factory);

        $schema = json_decode($schema);
        $data = json_decode($data, $assoc);

        // check initial type
        $type = gettype(LooseTypeCheck::propertyGet($data, 'propertyOne'));
        if ($assoc && $type == 'array' && $startType == 'object') {
            $type = 'object';
        }
        $this->assertEquals($startType, $type, "Incorrect type '$type': expected '$startType'");

        $validator->validate($data, $schema);

        // check validity
        if ($valid) {
            $prettyPrint = defined('\JSON_PRETTY_PRINT') ? constant('\JSON_PRETTY_PRINT') : 0;
            $this->assertTrue(
                $validator->isValid(),
                'Validation failed: ' . json_encode($validator->getErrors(), $prettyPrint)
            );

            // check end type
            $type = gettype(LooseTypeCheck::propertyGet($data, 'propertyOne'));
            $this->assertEquals($endType, $type, "Incorrect type '$type': expected '$endType'");

            // check end value
            $value = LooseTypeCheck::propertyGet($data, 'propertyOne');
            $this->assertTrue(
                $value === $endValue,
                sprintf(
                    "Incorrect value '%s': expected '%s'",
                    is_scalar($value) ? $value : gettype($value),
                    is_scalar($endValue) ? $endValue : gettype($endValue)
                )
            );
        } else {
            $this->assertFalse($validator->isValid(), 'Validation succeeded, but should have failed');
            $this->assertEquals(1, count($validator->getErrors()));
        }

        $this->factory->removeConfig(Constraint::CHECK_MODE_EARLY_COERCE);
    }

    /** @dataProvider dataCoerceCases **/
    public function testCoerceCasesUsingAssoc($schema, $data, $startType, $endType, $endValue, $valid, $early = false)
    {
        $this->testCoerceCases($schema, $data, $startType, $endType, $endValue, $valid, $early, true);
    }

    public function testCoerceAPI()
    {
        $input = json_decode('{"propertyOne": "10"}');
        $schema = json_decode('{"properties":{"propertyOne":{"type":"number"}}}');
        $v = new Validator();
        $v->coerce($input, $schema);
        $this->assertEquals('{"propertyOne":10}', json_encode($input));
    }
}
