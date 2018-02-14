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

class CoerciveTest extends BasicTypesTest
{
    protected $schemaSpec = 'http://json-schema.org/draft-03/schema#';
    protected $validateSchema = true;

    /**
     * @dataProvider getValidCoerceTests
     */
    public function testValidCoerceCasesUsingAssoc($input, $schema)
    {
        $checkMode = Constraint::CHECK_MODE_TYPE_CAST | Constraint::CHECK_MODE_COERCE_TYPES;

        $schemaStorage = new SchemaStorage($this->getUriRetrieverMock(json_decode($schema)));
        $schema = $schemaStorage->getSchema('http://www.my-domain.com/schema.json');

        $validator = new Validator(new Factory($schemaStorage, null, $checkMode));

        $value = json_decode($input, true);

        $validator->validate($value, $schema, $checkMode);
        $this->assertTrue($validator->isValid(), print_r($validator->getErrors(), true));
    }

    /**
     * @dataProvider getValidCoerceTests
     */
    public function testValidCoerceCases($input, $schema, $errors = array())
    {
        $checkMode = Constraint::CHECK_MODE_TYPE_CAST | Constraint::CHECK_MODE_COERCE_TYPES;

        $schemaStorage = new SchemaStorage($this->getUriRetrieverMock(json_decode($schema)));
        $schema = $schemaStorage->getSchema('http://www.my-domain.com/schema.json');

        $validator = new Validator(new Factory($schemaStorage, null, $checkMode));
        $value = json_decode($input);

        $this->assertTrue(gettype($value->number) == 'string');
        $this->assertTrue(gettype($value->integer) == 'string');
        $this->assertTrue(gettype($value->boolean) == 'string');

        $validator->validate($value, $schema, $checkMode);

        $this->assertTrue(gettype($value->number) == 'double');
        $this->assertTrue(gettype($value->integer) == 'integer');
        $this->assertTrue(gettype($value->negativeInteger) == 'integer');
        $this->assertTrue(gettype($value->boolean) == 'boolean');

        $this->assertTrue($value->number === 1.5);
        $this->assertTrue($value->integer === 1);
        $this->assertTrue($value->negativeInteger === -2);
        $this->assertTrue($value->boolean === true);

        $this->assertTrue(gettype($value->multitype1) == 'boolean');
        $this->assertTrue(gettype($value->multitype2) == 'double');
        $this->assertTrue(gettype($value->multitype3) == 'integer');

        $this->assertTrue($value->number === 1.5);
        $this->assertTrue($value->integer === 1);
        $this->assertTrue($value->negativeInteger === -2);
        $this->assertTrue($value->boolean === true);

        $this->assertTrue($validator->isValid(), print_r($validator->getErrors(), true));
    }

    /**
     * @dataProvider getInvalidCoerceTests
     */
    public function testInvalidCoerceCases($input, $schema, $errors = array())
    {
        $checkMode = Constraint::CHECK_MODE_TYPE_CAST | Constraint::CHECK_MODE_COERCE_TYPES;

        $schemaStorage = new SchemaStorage($this->getUriRetrieverMock(json_decode($schema)));
        $schema = $schemaStorage->getSchema('http://www.my-domain.com/schema.json');

        $validator = new Validator(new Factory($schemaStorage, null, $checkMode));
        $value = json_decode($input);
        $validator->validate($value, $schema, $checkMode);

        if (array() !== $errors) {
            $this->assertEquals($errors, $validator->getErrors(), print_r($validator->getErrors(), true));
        }
        $this->assertFalse($validator->isValid(), print_r($validator->getErrors(), true));
    }

    /**
     * @dataProvider getInvalidCoerceTests
     */
    public function testInvalidCoerceCasesUsingAssoc($input, $schema, $errors = array())
    {
        $checkMode = Constraint::CHECK_MODE_TYPE_CAST | Constraint::CHECK_MODE_COERCE_TYPES;

        $schemaStorage = new SchemaStorage($this->getUriRetrieverMock(json_decode($schema)));
        $schema = $schemaStorage->getSchema('http://www.my-domain.com/schema.json');

        $validator = new Validator(new Factory($schemaStorage, null, $checkMode));
        $value = json_decode($input, true);
        $validator->validate($value, $schema, $checkMode);

        if (array() !== $errors) {
            $this->assertEquals($errors, $validator->getErrors(), print_r($validator->getErrors(), true));
        }
        $this->assertFalse($validator->isValid(), print_r($validator->getErrors(), true));
    }

    public function testCoerceAPI()
    {
        $input = json_decode('{"propertyOne": "10"}');
        $schema = json_decode('{"properties":{"propertyOne":{"type":"number"}}}');
        $v = new Validator();
        $v->coerce($input, $schema);
        $this->assertEquals('{"propertyOne":10}', json_encode($input));
    }

    public function getValidCoerceTests()
    {
        return array(
            array(
                '{
                  "string":"string test",
                  "number":"1.5",
                  "integer":"1",
                  "negativeInteger":"-2",
                  "boolean":"true",
                  "object":{},
                  "array":[],
                  "null":null,
                  "any": "string",
                  "allOf": "1",
                  "multitype1": "false",
                  "multitype2": "1.2",
                  "multitype3": "7",
                  "arrayOfIntegers":["-1","0","1"],
                  "tupleTyping":["1","2.2","true"],
                  "any1": 2.6,
                  "any2": 4,
                  "any3": false,
                  "any4": {},
                  "any5": [],
                  "any6": null
                }',
                '{
                  "type":"object",
                  "properties":{
                    "string":{"type":"string"},
                    "number":{"type":"number"},
                    "integer":{"type":"integer"},
                    "negativeInteger":{"type":"integer"},
                    "boolean":{"type":"boolean"},
                    "object":{"type":"object"},
                    "array":{"type":"array"},
                    "null":{"type":"null"},
                    "any": {"type":"any"},
                    "allOf" : {"allOf":[{
                        "type" : "string"
                    },{
                        "type" : "integer"
                    }]},
                    "multitype1": {"type":["boolean","integer","number"]},
                    "multitype2": {"type":["boolean","integer","number"]},
                    "multitype3": {"type":["boolean","integer","number"]},
                     "arrayOfIntegers":{
                        "items":{
                            "type":"integer"
                        }
                    },
                    "tupleTyping":{
                      "type":"array",
                      "items":[
                        {"type":"integer"},
                        {"type":"number"}
                      ],
                      "additionalItems":{"type":"boolean"}
                    },
                    "any1": {"type":"any"},
                    "any2": {"type":"any"},
                    "any3": {"type":"any"},
                    "any4": {"type":"any"},
                    "any5": {"type":"any"},
                    "any6": {"type":"any"}
                  },
                  "additionalProperties":false
                }',
            ),
        );
    }

    public function getInvalidCoerceTests()
    {
        return array(
            array(
                '{
                  "string":null
                }',
                '{
                  "type":"object",
                  "properties": {
                    "string":{"type":"string"}
                  },
                  "additionalProperties":false
                }',
            ),
            array(
                '{
                  "number":"five"
                }',
                '{
                  "type":"object",
                  "properties": {
                    "number":{"type":"number"}
                  },
                  "additionalProperties":false
                }',
            ),
            array(
                '{
                  "integer":"5.2"
                }',
                '{
                  "type":"object",
                  "properties": {
                    "integer":{"type":"integer"}
                  },
                  "additionalProperties":false
                }',
            ),
            array(
                '{
                  "boolean":"0"
                }',
                '{
                  "type":"object",
                  "properties": {
                    "boolean":{"type":"boolean"}
                  },
                  "additionalProperties":false
                }',
            ),
            array(
                '{
                  "object":null
                }',
                '{
                  "type":"object",
                  "properties": {
                    "object":{"type":"object"}
                  },
                  "additionalProperties":false
                }',
            ),
            array(
                '{
                  "array":null
                }',
                '{
                  "type":"object",
                  "properties": {
                    "array":{"type":"array"}
                  },
                  "additionalProperties":false
                }',
            ),
            array(
                '{
                  "null":1
                }',
                '{
                  "type":"object",
                  "properties": {
                    "null":{"type":"null"}
                  },
                  "additionalProperties":false
                }',
            ),
        );
    }
}
