<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class PatternTest extends BaseTestCase
{
    protected $validateSchema = true;

    public function getInvalidTests(): array
    {
        return [
            [
                '{
                  "value":"Abacates"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","pattern":"^cat"}
                  },
                  "additionalProperties":false
                }'
            ],
            [
                '{"value": "abc"}',
                '{
                    "type": "object",
                    "properties": {
                        "value": {"type": "string", "pattern": "^a*$"}
                    },
                    "additionalProperties": false
                }'
            ],
            [
                '{"value": "Ã¼"}',
                '{
                    "type": "object",
                    "properties": {
                        "value": {"type": "string", "pattern": "^ü$"}
                    },
                    "additionalProperties": false
                }'
            ],
        ];
    }

    public function getValidTests(): array
    {
        return [
            [
                '{
                  "value":"Abacates"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","pattern":"tes$"}
                  },
                  "additionalProperties":false
                }'
            ],
            [
                '{
                  "value":"Abacates"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"string","pattern":"cat"}
                  },
                  "additionalProperties":false
                }'
            ],
            [
                '{"value": "aaa"}',
                '{
                    "type": "object",
                    "properties": {
                        "value": {"type": "string", "pattern": "^a*$"}
                    },
                    "additionalProperties": false
                }'
            ],
            [
                '{"value": "↓æ→"}',
                '{
                    "type": "object",
                    "properties": {
                        "value": {"type": "string", "pattern": "^↓æ.$"}
                    },
                    "additionalProperties": false
                }'
            ]
        ];
    }
}
