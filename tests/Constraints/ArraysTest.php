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

    public function getInvalidTests(): array
    {
        return [
            [
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
            ],
            [
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
            ],
            [
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
            ],
            [
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
            ],
            [ // Test array items.enum where type string fail validation if value(s) is/are not in items.enum
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
            ],
            [ // Test array items.enum where type integer fail validation if value(s) is/are not in items.enum
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
            ],
            [ // Test array items.enum where type number fail validation if value(s) is/are not in items.enum
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
            ],
            [
                '{"data": [{"not_a_string_but_object":"string_but_in_object"}]}',
                '{
                    "type": "object",
                    "properties": {
                        "data": {
                            "type": "array",
                            "items": {"type":"string"},
                            "additionalItems": false
                        }
                    }
                }'
            ]
        ];
    }

    public function getValidTests(): array
    {
        return [
            [
                '{
                  "array":[1,2,"a"]
                }',
                '{
                  "type":"object",
                  "properties":{
                    "array":{"type":"array"}
                  }
                }'
            ],
            [
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
            ],
            [
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
            ],
            [
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
            ],
            [
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
            ],
            [
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
            ],
            [ // test more schema items than array items
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
            ],
            [ // Test array items.enum where type string passes validation if value(s) is/are in items.enum
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
            ],
            [ // Test array items.enum where type integer passes validation if value(s) is/are in items.enum
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
            ],
            [ // Test array items.enum where type number passes validation if value(s) is/are in items.enum
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
            ],
        ];
    }
}
