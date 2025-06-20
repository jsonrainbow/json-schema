<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class TupleTypingTest extends BaseTestCase
{
    protected $validateSchema = true;

    public function getInvalidTests(): \Generator
    {
        yield [
            '{
              "tupleTyping":[2,"a"]
            }',
            '{
              "type":"object",
              "properties":{
                "tupleTyping":{
                  "type":"array",
                  "items":[
                    {"type":"string"},
                    {"type":"number"}
                  ]
                }
              }
            }'
        ];
        yield [
            '{
              "tupleTyping":["2",2,true]
            }',
            '{
              "type":"object",
              "properties":{
                "tupleTyping":{
                  "type":"array",
                  "items":[
                    {"type":"string"},
                    {"type":"number"}
                  ] ,
                  "additionalItems":false
                }
              }
            }'
        ];
        yield [
            '{
              "tupleTyping":["2",2,3]
            }',
            '{
              "type":"object",
              "properties":{
                "tupleTyping":{
                  "type":"array",
                  "items":[
                    {"type":"string"},
                    {"type":"number"}
                  ] ,
                  "additionalItems":{"type":"string"}
                }
              }
            }'
        ];
        yield [
            '{"data": [1, "foo", true, 1.5]}',
            '{
                "type": "object",
                "properties": {
                    "data": {
                        "type": "array",
                        "items": [{}, {}, {}],
                        "additionalItems": false
                    }
                }
            }'
        ];
    }

    public function getValidTests(): \Generator
    {
        yield [
            '{
              "tupleTyping":["2", 1]
            }',
            '{
              "type":"object",
              "properties":{
                "tupleTyping":{
                  "type":"array",
                  "items":[
                    {"type":"string"},
                    {"type":"number"}
                  ]
                }
              }
            }'
        ];
        yield [
            '{
              "tupleTyping":["2",2,3]
            }',
            '{
              "type":"object",
              "properties":{
                "tupleTyping":{
                  "type":"array",
                  "items":[
                    {"type":"string"},
                    {"type":"number"}
                  ]
                }
              }
            }'
        ];
        yield [
            '{"data": [1, "foo", true]}',
            '{
                "type": "object",
                "properties": {
                    "data": {
                        "type": "array",
                        "items": [{}, {}, {}],
                        "additionalItems": false
                    }
                }
            }'
        ];
    }
}
