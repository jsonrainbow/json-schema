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
use JsonSchema\Uri\UriResolver;
use JsonSchema\Validator;

class CoerciveTest extends BasicTypesTest
{
    /**
     * @dataProvider getValidCoerceTests
     */
    public function testValidCoerceCasesUsingAssoc($input, $schema)
    {
        $checkMode = Constraint::CHECK_MODE_COERCE | Constraint::CHECK_MODE_TYPE_CAST;

        $schemaStorage = new SchemaStorage($this->getUriRetrieverMock(json_decode($schema)));
        $schema = $schemaStorage->getSchema('http://www.my-domain.com/schema.json');

        $validator = new Validator($checkMode, new Factory($schemaStorage, null));

        $value = json_decode($input, true);

        $validator->check($value, $schema);
        $this->assertTrue($validator->isValid(), print_r($validator->getErrors(), true));
    }

    /**
     * @dataProvider getValidCoerceTests
     */
    public function testValidCoerceCases($input, $schema, $errors = array())
    {
        $checkMode = Constraint::CHECK_MODE_COERCE | Constraint::CHECK_MODE_TYPE_CAST;

        $schemaStorage = new SchemaStorage($this->getUriRetrieverMock(json_decode($schema)));
        $schema = $schemaStorage->getSchema('http://www.my-domain.com/schema.json');

        $validator = new Validator($checkMode, new Factory($schemaStorage, null));
        $value = json_decode($input);

        $this->assertTrue(gettype($value->number) == "string");
        $this->assertTrue(gettype($value->integer) == "string");
        $this->assertTrue(gettype($value->boolean) == "string");

        $validator->check($value, $schema);

        $this->assertTrue(gettype($value->number) == "double");
        $this->assertTrue(gettype($value->integer) == "integer");
        $this->assertTrue(gettype($value->boolean) == "boolean");

        $this->assertTrue($validator->isValid(), print_r($validator->getErrors(), true));
    }

    /**
     * @dataProvider getInvalidCoerceTests
     */
    public function testInvalidCoerceCases($input, $schema, $errors = array())
    {
        $checkMode = Constraint::CHECK_MODE_COERCE | Constraint::CHECK_MODE_TYPE_CAST;

        $schemaStorage = new SchemaStorage($this->getUriRetrieverMock(json_decode($schema)));
        $schema = $schemaStorage->getSchema('http://www.my-domain.com/schema.json');

        $validator = new Validator($checkMode, new Factory($schemaStorage, null));
        $validator->check(json_decode($input), $schema);

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
        $checkMode = Constraint::CHECK_MODE_COERCE | Constraint::CHECK_MODE_TYPE_CAST;

        $schemaStorage = new SchemaStorage($this->getUriRetrieverMock(json_decode($schema)));
        $schema = $schemaStorage->getSchema('http://www.my-domain.com/schema.json');

        $validator = new Validator($checkMode, new Factory($schemaStorage, null));
        $validator->check(json_decode($input, true), $schema);

        if (array() !== $errors) {
            $this->assertEquals($errors, $validator->getErrors(), print_r($validator->getErrors(), true));
        }
        $this->assertFalse($validator->isValid(), print_r($validator->getErrors(), true));
    }

    public function getValidCoerceTests()
    {
        return array(
            array(
                '{
                  "string":"string test",
                  "number":"1.5",
                  "integer":"1",
                  "boolean":"true",
                  "object":{},
                  "array":[],
                  "null":null,
                  "any": "string",
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
                    "boolean":{"type":"boolean"},
                    "object":{"type":"object"},
                    "array":{"type":"array"},
                    "null":{"type":"null"},
                    "any": {"type":"any"},
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
