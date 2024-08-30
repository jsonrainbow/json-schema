<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class DisallowTest extends BaseTestCase
{
    // schemas in these tests look like draft-03, but the 'disallow' patterns provided are in
    // violation of the spec - 'disallow' as defined in draft-03 accepts the same values as the
    // 'type' option, and cannot take arbitrary patterns. The implementation in this library is
    // probably deliberate, but noting that it's invalid, schema validation has been disabled
    // for these tests. The 'disallow' option was removed permanently in draft-04.
    protected $schemaSpec = 'http://json-schema.org/draft-03/schema#';
    protected $validateSchema = false;

    public function getInvalidTests(): array
    {
        return [
            [
                '{
                  "value":"The xpto is weird"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{
                      "type":"any",
                      "disallow":{"type":"string","pattern":"xpto"}
                    }
                  }
                }'
            ],
            [
                '{
                  "value":null
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{
                      "type":"any",
                      "disallow":{"type":"null"}
                    }
                  }
                }'
            ],
            [
                '{"value": 1}',
                '{
                    "type": "object",
                    "properties": {
                        "value": {"type": "any", "disallow": "integer"}
                    }
                }'
            ],
            [
                '{"value": true}',
                '{
                    "type": "object",
                    "properties": {
                        "value": {"type": "any", "disallow": ["integer", "boolean"]}
                    }
                }'
            ],
            [
                '{"value": "foo"}',
                '{
                    "type": "object",
                    "properties": {
                        "value": {
                            "type": "any",
                            "disallow":
                                ["string", {
                                    "type": "object",
                                    "properties": {
                                        "foo": {"type": "string"}
                                    }
                                }]
                        }
                    }
                }'
            ],
            [
                '{"value": {"foo": "bar"}}',
                '{
                    "type": "object",
                    "properties": {
                        "value": {
                            "type": "any",
                            "disallow":
                                ["string", {
                                    "type": "object",
                                    "properties": {
                                        "foo": {"type": "string"}
                                    }
                                }]
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
                  "value":"The xpto is weird"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{
                      "type":"any",
                      "disallow":{"type":"string","pattern":"^xpto"}
                    }
                  }
                }'
            ],
            [
                '{
                  "value":1
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{
                      "type":"any",
                      "disallow":{"type":"null"}
                    }
                  }
                }'
            ],
            [
                '{"value": {"foo": 1}}',
                '{
                    "type": "object",
                    "properties": {
                        "value": {
                            "type": "any",
                            "disallow":
                                ["string", {
                                    "type": "object",
                                    "properties": {
                                        "foo": {"type": "string"}
                                    }
                                }]
                        }
                    }
                }'
            ],
            [
                '{"value": true}',
                '{
                    "type": "object",
                    "properties": {
                        "value": {"type": "any", "disallow": "string"}
                    }
                }'
            ]
        ];
    }
}
