<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class ArraysTest extends BaseTestCase
{
    protected $validateSchema = true;

    public function getInvalidTests()
    {
        return array(
            array(
                '{
                  "array":[1,2,"a"]
                }',
                '{
                  "type":"object",
                  "properties":{
                    "array":{
                      "type":"array",
                      "items":{"type":"number"}
                    }
                  }
                }'
            ),
            array(
                '{
                  "array":[1,2,"a"]
                }',
                '{
                  "type":"object",
                  "properties":{
                    "array":{
                      "type":"array",
                      "items":{"type":"number"},
                      "additionalItems":{"type":"boolean"}
                    }
                  }
                }'
            ),
            array(
                '{
                  "array":[1,2,null]
                }',
                '{
                  "type":"object",
                  "properties":{
                    "array":{
                      "type":"array",
                      "items":{"type":["number","boolean"]}
                    }
                  }
                }'
            ),
            array(
                '{"data": [1, 2, 3, "foo"]}',
                '{
                    "type": "object",
                    "properties": {
                        "data": {
                            "type": "array",
                            "items": [],
                            "additionalItems": {"type": "integer"}
                        }
                    }
                }'
            ),
            array( // Test array items.enum where type string fail validation if value(s) is/are not in items.enum
                '{"data": ["a", "b"]}',
                '{
                    "type": "object",
                    "properties": {
                        "data": {
                            "type": "array",
                            "items": {
                                "type": "string",
                                "enum": ["b", "c"]
                            }
                        }
                    }
                }'
            ),
            array( // Test array items.enum where type integer fail validation if value(s) is/are not in items.enum
                '{"data": [1, 2]}',
                '{
                    "type": "object",
                    "properties": {
                        "data": {
                            "type": "array",
                            "items": {
                                "type": "integer",
                                "enum": [2, 3]
                            }
                        }
                    }
                }'
            ),
            array( // Test array items.enum where type number fail validation if value(s) is/are not in items.enum
                '{"data": [1.25, 2.25]}',
                '{
                    "type": "object",
                    "properties": {
                        "data": {
                            "type": "array",
                            "items": {
                                "type": "number",
                                "enum": [1.25, 2]
                            }
                        }
                    }
                }'
            )
        );
    }

    public function getValidTests()
    {
        return array(
            array(
                '{
                  "array":[1,2,"a"]
                }',
                '{
                  "type":"object",
                  "properties":{
                    "array":{"type":"array"}
                  }
                }'
            ),
            array(
                '{
                  "array":[1,2,"a"]
                }',
                '{
                  "type":"object",
                  "properties":{
                    "array":{
                      "type":"array",
                      "items":{"type":"number"},
                      "additionalItems": {"type": "string"}
                    }
                  }
                }'
            ),
            array(
                '{"data": [1, 2, 3, 4]}',
                '{
                    "type": "object",
                    "properties": {
                        "data": {
                            "type": "array",
                            "items": [],
                            "additionalItems": {"type": "integer"}
                        }
                    }
                }'
            ),
            array(
                '{"data": [1, "foo", false]}',
                '{
                    "type": "object",
                    "properties": {
                        "data": {
                            "type": "array",
                            "items": []
                        }
                    }
                }'
            ),
            array(
                '{"data": [1, "foo", false]}',
                '{
                    "type": "object",
                    "properties": {
                        "data": {
                            "type": "array",
                            "items": {}
                        }
                    }
                }'
            ),
            array(
                '{"data": [1, 2, 3, 4, 5]}',
                '{
                    "type": "object",
                    "properties": {
                        "data": {
                            "type": "array",
                            "additionalItems": false
                        }
                    }
                }'
            ),
            array( // test more schema items than array items
                '{"data": [1, 2]}',
                '{
                    "type": "object",
                    "properties": {
                        "data": {
                        "type": "array",
                            "items": [
                                {"type": "number"},
                                {"type": "number"},
                                {"type": "number"}
                            ]
                        }
                    }
                }'
            ),
            array( // Test array items.enum where type string passes validation if value(s) is/are in items.enum
                '{"data": ["c", "c", "b"]}',
                '{
                    "type": "object",
                    "properties": {
                        "data": {
                            "type": "array",
                            "items": {
                                "type": "string",
                                "enum": ["b", "c"]
                            }
                        }
                    }
                }'
            ),
            array( // Test array items.enum where type integer passes validation if value(s) is/are in items.enum
                '{"data": [1, 1, 2]}',
                '{
                    "type": "object",
                    "properties": {
                        "data": {
                            "type": "array",
                            "items": {
                                "type": "integer",
                                "enum": [1, 2]
                            }
                        }
                    }
                }'
            ),
            array( // Test array items.enum where type number passes validation if value(s) is/are in items.enum
                '{"data": [1.25, 1.25, 2.25]}',
                '{
                    "type": "object",
                    "properties": {
                        "data": {
                            "type": "array",
                            "items": {
                                "type": "number",
                                "enum": [1.25, 2.25]
                            }
                        }
                    }
                }'
            )
        );
    }
}
