<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints;

use JsonSchema\Constraints\Constraint;

class UndefinedConstraintTest extends BaseTestCase
{
    public function getInvalidTests(): \Generator
    {
        yield from [];
    }

    public function getValidTests(): \Generator
    {
        yield 'oneOf with type coercion should not affect value passed to each sub schema (#790)' => [
            'input' => '{
                "id": "LOC1",
                "related_locations": [
                    {
                        "latitude": "51.047598",
                        "longitude": "3.729943"
                    }
                ]
            }',
            'schema' => '{
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
            }',
            'checkMode' => Constraint::CHECK_MODE_COERCE_TYPES
        ];
        yield 'oneOf with apply defaults should not affect value passed to each sub schema (#510)' => [
            'input' => '{"foo": {"name": "bar"}}',
            'schema' => '{
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
            }',
            'checkMode' => Constraint::CHECK_MODE_APPLY_DEFAULTS
        ];
        yield 'anyOf with apply defaults should not affect value passed to each sub schema (#711)' => [
            'input' => '{ "b": 2 }',
            'schema' => '{
              "anyOf": [
                {
                  "required": [ "a" ],
                  "pro": {
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
            }',
            'checkMode' => Constraint::CHECK_MODE_APPLY_DEFAULTS
        ];
    }
}
