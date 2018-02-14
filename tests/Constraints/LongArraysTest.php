<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

use JsonSchema\Constraints\Factory;
use JsonSchema\SchemaStorage;
use JsonSchema\Validator;

class LongArraysTest extends VeryBaseTestCase
{
    protected $validateSchema = true;

    public function testLongStringArray()
    {
        $schema =
            '{
              "type":"object",
              "properties":{
                "p_array":{
                  "type":"array",
                  "items":{"type":"string"}
                }
              }
            }';

        $tmp = new \stdClass();
        $tmp->p_array = array_map(function ($i) {
            return '#' . $i;
        }, range(1, 100000));
        $input = json_encode($tmp);

        $schemaStorage = new SchemaStorage($this->getUriRetrieverMock(json_decode($schema)));
        $schema = $schemaStorage->getSchema('http://www.my-domain.com/schema.json');

        $validator = new Validator(new Factory($schemaStorage));
        $checkValue = json_decode($input);
        $validator->check($checkValue, $schema);
        $this->assertTrue($validator->isValid(), print_r($validator->getErrors(), true));
    }

    public function testLongNumberArray()
    {
        $schema =
            '{
              "type":"object",
              "properties":{
                "p_array":{
                  "type":"array",
                  "items":{"type":"number"}
                }
              }
            }';

        $tmp = new \stdClass();
        $tmp->p_array = array_map(function ($i) {
            return rand(1, 1000) / 1000.0;
        }, range(1, 100000));
        $input = json_encode($tmp);

        $schemaStorage = new SchemaStorage($this->getUriRetrieverMock(json_decode($schema)));
        $schema = $schemaStorage->getSchema('http://www.my-domain.com/schema.json');

        $validator = new Validator(new Factory($schemaStorage));
        $checkValue = json_decode($input);
        $validator->check($checkValue, $schema);
        $this->assertTrue($validator->isValid(), print_r($validator->getErrors(), true));
    }

    public function testLongIntegerArray()
    {
        $schema =
            '{
              "type":"object",
              "properties":{
                "p_array":{
                  "type":"array",
                  "items":{"type":"integer"}
                }
              }
            }';

        $tmp = new \stdClass();
        $tmp->p_array = array_map(function ($i) {
            return $i;
        }, range(1, 100000));
        $input = json_encode($tmp);

        $schemaStorage = new SchemaStorage($this->getUriRetrieverMock(json_decode($schema)));
        $schema = $schemaStorage->getSchema('http://www.my-domain.com/schema.json');

        $validator = new Validator(new Factory($schemaStorage));
        $checkValue = json_decode($input);
        $validator->check($checkValue, $schema);
        $this->assertTrue($validator->isValid(), print_r($validator->getErrors(), true));
    }
}
