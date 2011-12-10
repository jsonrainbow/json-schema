<?php

namespace JsonSchema\Tests;

class BasicTypesTest extends BaseTestCase
{
    public function getInvalidTests()
    {
        return array(
            array(
                '{
                  "string":null,
                  "number":null,
                  "integer":null,
                  "boolean":null,
                  "object":null,
                  "array":null,
                  "null":1
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
                    "null":{"type":"null"}
                  },
                  "additionalProperties":false
                }'
            )
        );
    }

    public function getValidTests()
    {
        return array(
            array(
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
            )
        );
    }
}
