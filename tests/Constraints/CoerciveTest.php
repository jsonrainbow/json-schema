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

    public function setUp(): void
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
                array('string',     '"ABC"',        'ABC',          true),  // #0
                array('integer',    '45',           '45',           true),  // #1
                array('boolean',    'true',         'true',         true),  // #2
                array('boolean',    'false',        'false',        true),  // #3
                array('NULL',       'null',         '',             true),  // #4
                array('array',      '[45]',         '45',           true),  // #5
                array('object',     '{"a":"b"}',    null,           false), // #6
                array('array',      '[{"a":"b"}]',  null,           false), // #7
                array('array',      '[1,2]',  		array(1, 2),     false), // #8
            ),
            'integer' => array(
                array('string',     '"45"',         45,             true),  // #9
                array('integer',    '45',           45,             true),  // #10
                array('boolean',    'true',         1,              true),  // #11
                array('boolean',    'false',        0,              true),  // #12
                array('NULL',       'null',         0,              true),  // #13
                array('array',      '["-45"]',      -45,            true),  // #14
                array('object',     '{"a":"b"}',    null,           false), // #15
                array('array',      '["ABC"]',      null,           false), // #16
            ),
            'boolean' => array(
                array('string',     '"true"',       true,           true),  // #17
                array('integer',    '1',            true,           true),  // #18
                array('boolean',    'true',         true,           true),  // #19
                array('NULL',       'null',         false,          true),  // #20
                array('array',      '["true"]',     true,           true),  // #21
                array('object',     '{"a":"b"}',    null,           false), // #22
                array('string',     '""',           null,           false), // #23
                array('string',     '"ABC"',        null,           false), // #24
                array('integer',    '2',            null,           false), // #25
            ),
            'NULL' => array(
                array('string',     '""',           null,           true),  // #26
                array('integer',    '0',            null,           true),  // #27
                array('boolean',    'false',        null,           true),  // #28
                array('NULL',       'null',         null,           true),  // #29
                array('array',      '[0]',          null,           true),  // #30
                array('object',     '{"a":"b"}',    null,           false), // #31
                array('string',     '"null"',       null,           false), // #32
                array('integer',    '-1',           null,           false), // #33
            ),
            'array' => array(
                array('string',     '"ABC"',        array('ABC'),   true),  // #34
                array('integer',    '45',           array(45),      true),  // #35
                array('boolean',    'true',         array(true),    true),  // #36
                array('NULL',       'null',         array(null),    true),  // #37
                array('array',      '["ABC"]',      array('ABC'),   true),  // #38
                array('object',     '{"a":"b"}',    null,           false), // #39
            ),
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
            'string', 'integer', 42, true, Constraint::CHECK_MODE_EARLY_COERCE
        );

        // #45 check multiple types (none valid)
        $tests[] = array(
            '{"properties":{"propertyOne":{"type":["number", "boolean"]}}}',
            '{"propertyOne":"42"}',
            'string', 'integer', 42, true
        );

        // #46 check coercion with "const"
        $tests[] = array(
            '{"properties":{"propertyOne":{"type":"string","const":"42"}}}',
            '{"propertyOne":42}',
            'integer', 'string', '42', true
        );

        // #47 check coercion with "const"
        $tests[] = array(
            '{"properties":{"propertyOne":{"type":"number","const":42}}}',
            '{"propertyOne":"42"}',
            'string', 'integer', 42, true
        );

        // #48 check boolean coercion with "const"
        $tests[] = array(
            '{"properties":{"propertyOne":{"type":"boolean","const":false}}}',
            '{"propertyOne":"false"}',
            'string', 'boolean', false, true
        );

        // #49 check boolean coercion with "const"
        $tests[] = array(
            '{"properties":{"propertyOne":{"type":"boolean","const":true}}}',
            '{"propertyOne":"true"}',
            'string', 'boolean', true, true
        );

        // #50 check boolean coercion with "const"
        $tests[] = array(
            '{"properties":{"propertyOne":{"type":"boolean","const":true}}}',
            '{"propertyOne":1}',
            'integer', 'boolean', true, true
        );

        // #51 check boolean coercion with "const"
        $tests[] = array(
            '{"properties":{"propertyOne":{"type":"boolean","const":false}}}',
            '{"propertyOne":"false"}',
            'string', 'boolean', false, true
        );

        // #52 check post-coercion validation (to array)
        $tests[] = array(
            '{"properties":{"propertyOne":{"type":"array","items":[{"type":"number"}]}}}',
            '{"propertyOne":"ABC"}',
            'string', null, null, false
        );

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

        return $tests;
    }

    /** @dataProvider dataCoerceCases **/
    public function testCoerceCases($schema, $data, $startType, $endType, $endValue, $valid, $extraFlags = 0, $assoc = false)
    {
        $validator = new Validator($this->factory);

        $schema = json_decode($schema);
        $data = json_decode($data, $assoc);

        // check initial type
        $type = gettype(LooseTypeCheck::propertyGet($data, 'propertyOne'));
        if ($assoc && $type == 'array' && $startType == 'object') {
            $type = 'object';
        }
        $this->assertEquals($startType, $type, "Incorrect type '$type': expected '$startType'");

        $validator->validate($data, $schema, $this->factory->getConfig() | $extraFlags);

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
            $this->assertSame($value, $endValue, sprintf(
                "Incorrect value '%s': expected '%s'",
                is_scalar($value) ? $value : gettype($value),
                is_scalar($endValue) ? $endValue : gettype($endValue)
            ));
        } else {
            $this->assertFalse($validator->isValid(), 'Validation succeeded, but should have failed');
            $this->assertCount(1, $validator->getErrors());
        }
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
