<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class BasicTypesTest extends BaseTestCase
{
    protected $schemaSpec = 'http://json-schema.org/draft-03/schema#';
    protected $validateSchema = true;

    public function getInvalidTests(): \Generator
    {

        yield [
            '{
              "string":null
            }',
            '{
              "type":"object",
              "properties": {
                "string":{"type":"string"}
              },
              "additionalProperties":false
            }'
        ];
        yield [
            '{
              "number":null
            }',
            '{
              "type":"object",
              "properties": {
                "number":{"type":"number"}
              },
              "additionalProperties":false
            }'
        ];
        yield [
            '{
              "integer":null
            }',
            '{
              "type":"object",
              "properties": {
                "integer":{"type":"integer"}
              },
              "additionalProperties":false
            }'
        ];
        yield [
            '{
              "boolean":null
            }',
            '{
              "type":"object",
              "properties": {
                "boolean":{"type":"boolean"}
              },
              "additionalProperties":false
            }'
        ];
        yield [
            '{
              "object":null
            }',
            '{
              "type":"object",
              "properties": {
                "object":{"type":"object"}
              },
              "additionalProperties":false
            }'
        ];
        yield [
            '{
              "array":null
            }',
            '{
              "type":"object",
              "properties": {
                "array":{"type":"array"}
              },
              "additionalProperties":false
            }'
        ];
        yield [
            '{
              "null":1
            }',
            '{
              "type":"object",
              "properties": {
                "null":{"type":"null"}
              },
              "additionalProperties":false
            }'
            ];
    }

    public function getValidTests(): \Generator
    {
        yield [
            '{
              "string":"string test",
              "number":1,
              "integer":1,
              "boolean":true,
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
            }'
        ];
    }
}
