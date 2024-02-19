<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class IfThenElseTest extends BaseTestCase
{
    protected $validateSchema = true;

    public function getInvalidTests()
    {
        return array(
            // If "foo" === "bar", then "bar" must be defined, else Validation Failed.
            // But "foo" === "bar" and "bar" is not defined.
            array(
                '{
                  "foo":"bar"
                }',
                '{
                    "type": "object",
                    "properties": {
                        "foo": {"type": "string"},
                        "bar": {"type": "string"}
                    },
                    "if": {
                        "properties": {"foo": {"enum": ["bar"]}},
                        "required": ["foo"]
                    },
                    "then": {"required": ["bar"]},
                    "else": false
                }'
            ),
            // If "foo" === "bar", then "bar" must be defined, else Validation Failed.
            // But "foo" !== "bar".
            array(
                '{
                  "foo":"baz"
                }',
                '{
                    "type": "object",
                    "properties": {
                        "foo": {"type": "string"},
                        "bar": {"type": "string"}
                    },
                    "if": {
                        "properties": {"foo": {"enum": ["bar"]}},
                        "required": ["foo"]
                    },
                    "then": {"required": ["bar"]},
                    "else": false
                }'
            ),
            // If "foo" === "bar", then "bar" must === "baz", else Validation Failed.
            // But "foo" === "bar" and "bar" !== "baz".
            array(
                '{
                  "foo":"bar",
                  "bar":"potato"
                }',
                '{
                    "type": "object",
                    "properties": {
                        "foo": {"type": "string"},
                        "bar": {"type": "string"}
                    },
                    "if": {
                        "properties": {"foo": {"enum": ["bar"]}},
                        "required": ["foo"]
                    },
                    "then": {
                        "properties": {"bar": {"enum": ["baz"]}},
                        "required": ["bar"]
                    },
                    "else": false
                }'
            ),
            // Always go to "else".
            // But schema is invalid.
            array(
                '{
                  "foo":"bar"
                }',
                '{
                    "type": "object",
                    "properties": {
                        "foo": {"type": "string"},
                        "bar": {"type": "string"}
                    },
                    "if": false,
                    "then": true,
                    "else": {
                        "properties": {"bar": {"enum": ["baz"]}},
                        "required": ["bar"]
                    }
                }'
            ),
            // Always go to "then".
            // But schema is invalid.
            array(
                '{
                  "foo":"bar"
                }',
                '{
                    "type": "object",
                    "properties": {
                        "foo": {"type": "string"},
                        "bar": {"type": "string"}
                    },
                    "if": true,
                    "then": {
                        "properties": {"bar": {"enum": ["baz"]}},
                        "required": ["bar"]
                    },
                    "else": true
                }'
            )
        );
    }

    public function getValidTests()
    {
        return array(
            // Always validate.
            array(
                '{
                  "foo":"bar"
                }',
                '{
                    "type": "object",
                    "properties": {
                        "foo": {"type": "string"},
                        "bar": {"type": "string"}
                    },
                    "if": true,
                    "then": true,
                    "else": false
                }'
            ),
            // Always validate schema in then.
            array(
                '{
                  "foo":"bar"
                }',
                '{
                    "type": "object",
                    "properties": {
                        "foo": {"type": "string"},
                        "bar": {"type": "string"}
                    },
                    "if": true,
                    "then": {
                        "properties": {"foo": {"enum": ["bar"]}},
                        "required": ["foo"]
                    },
                    "else": false
                }'
            ),
            // Always validate schema in else.
            array(
                '{
                  "foo":"bar"
                }',
                '{
                    "type": "object",
                    "properties": {
                        "foo": {"type": "string"},
                        "bar": {"type": "string"}
                    },
                    "if": false,
                    "then": false,
                    "else": {
                        "properties": {"foo": {"enum": ["bar"]}},
                        "required": ["foo"]
                    }
                }'
            ),
            // "If" is evaluated to true, so "then" is to validate.
            array(
                '{
                  "foo":"bar",
                  "bar":"baz"
                }',
                '{
                    "type": "object",
                    "properties": {
                        "foo": {"type": "string"},
                        "bar": {"type": "string"}
                    },
                    "if": {
                        "properties": {"foo": {"enum": ["bar"]}},
                        "required": ["foo"]
                    },
                    "then": {
                        "properties": {"bar": {"enum": ["baz"]}},
                        "required": ["bar"]
                    },
                    "else": false
                }'
            ),
            // "If" is evaluated to false, so "else" is to validate.
            array(
                '{
                  "foo":"bar",
                  "bar":"baz"
                }',
                '{
                    "type": "object",
                    "properties": {
                        "foo": {"type": "string"},
                        "bar": {"type": "string"}
                    },
                    "if": {
                        "properties": {"foo": {"enum": ["potato"]}},
                        "required": ["foo"]
                    },
                    "then": false,
                    "else": {
                        "properties": {"bar": {"enum": ["baz"]}},
                        "required": ["bar"]
                    }
                }'
            ),
        );
    }
}
