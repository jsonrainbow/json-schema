<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints;

use JsonSchema\Constraints\Constraint;

class UndefinedConstraintTest extends BaseTestCase
{
    /**
     * @return array{}
     */
    public function getInvalidTests(): array
    {
        return [];
    }

    /**
     * @return array<string, array{input: string, schema: string, checkMode?: int}>
     */
    public function getValidTests(): array
    {
        return [
            'oneOf with type coercion should not affect value passed to each sub schema (#790)' => [
                'input' => <<<JSON
                    {
                        "id": "LOC1",
                        "related_locations": [
                            {
                                "latitude": "51.047598",
                                "longitude": "3.729943"
                            }
                        ]
                    }
JSON
                ,
                'schema' => <<<JSON
                    {
                        "title": "Location",
                        "type": "object",
                        "properties": {
                            "id": {
                                "type": "string"
                            },
                            "related_locations": {
                                "oneOf": [
                                    {
                                        "type": "null"
                                    },
                                    {
                                        "type": "array",
                                        "items": {
                                            "type": "object",
                                            "properties": {
                                                "latitude": {
                                                    "type": "string"
                                                },
                                                "longitude": {
                                                    "type": "string"
                                                }
                                            }
                                        }
                                    }
                                ]
                            }
                        }
                    }
JSON
                ,
                'checkMode' => Constraint::CHECK_MODE_COERCE_TYPES
            ],
            'oneOf with apply defaults should not affect value passed to each sub schema (#510)' => [
                'input' => <<<JSON
                    {"foo": {"name": "bar"}}
JSON
                ,
                'schema' => <<<JSON
                    {
                        "oneOf": [
                            {
                                "type": "object",
                                "properties": {
                                    "foo": {
                                        "type": "object",
                                        "properties": {
                                            "name": {"enum":["baz"],"default":"baz"},
                                            "meta": {"enum":["baz"],"default":"baz"}
                                        }
                                    }
                                }
                            },
                            {
                                "type": "object",
                                "properties": {
                                    "foo": {
                                        "type": "object",
                                        "properties": {
                                            "name": {"enum":["bar"],"default":"bar"},
                                            "meta": {"enum":["bar"],"default":"bar"}
                                        }
                                    }
                                }
                            },
                            {
                                "type": "object",
                                "properties": {
                                    "foo": {
                                        "type": "object",
                                        "properties": {
                                            "name": {"enum":["zip"],"default":"zip"},
                                            "meta": {"enum":["zip"],"default":"zip"}
                                        }
                                    }
                                }
                            }
                        ]
                    }
JSON
                ,
                'checkMode' => Constraint::CHECK_MODE_APPLY_DEFAULTS
            ],
            'anyOf with apply defaults should not affect value passed to each sub schema (#711)' => [
                'input' => <<<JSON
                    {
                        "b": 2
                    }
JSON
                ,
                'schema' => <<<JSON
                    {
                        "anyOf": [
                            {
                                "required": [ "a" ],
                      "properties": {
                          "a": {
                              "type": "integer"
                          },
                          "aDefault": {
                              "type": "integer",
                              "default": 1
                          }
                      },
                      "type": "object",
                      "additionalProperties": false
                    },
                    {
                      "required": [ "b" ],
                      "properties": {
                          "b": {
                              "type": "integer"
                          },
                          "bDefault": {
                              "type": "integer",
                              "default": 2
                          }
                      },
                      "type": "object",
                      "additionalProperties": false
                    }
                  ]
                }
JSON
                ,
                'checkMode' => Constraint::CHECK_MODE_APPLY_DEFAULTS
            ]
        ];
    }
}
