<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class MinimumMaximumTest extends BaseTestCase
{
    protected $validateSchema = true;

    public function getInvalidTests(): array
    {
        return [
            [
                '{
                  "value":2
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"integer","minimum":4}
                  }
                }'
            ],
            [
                '{"value": 3}',
                '{
                    "type": "object",
                    "properties": {
                        "value": {"type": "integer", "minimum": 3, "exclusiveMinimum": true}
                    }
                }'
            ],
            [
                '{
                  "value":16
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"integer","maximum":8}
                  }
                }'
            ],
            [
                '{"value": 8}',
                '{
                    "type": "object",
                    "properties": {
                        "value": {"type": "integer", "maximum": 8, "exclusiveMaximum": true}
                    }
                }'
            ],
            [
                '{"value": 4}',
                '{
                    "type": "object",
                    "properties": {
                        "value": {"type": "integer", "exclusiveMinimum": true}
                    }
                }'
            ],
            [
                '{"value": 4}',
                '{
                    "type": "object",
                    "properties": {
                        "value": {"type": "integer", "exclusiveMaximum": true}
                    }
                }'
            ],
            [
                '{"value": 4}',
                '{
                    "type": "object",
                    "properties": {
                        "value": {"type": "integer", "minimum": 5, "exclusiveMinimum": false}
                    }
                }'
            ],
            [
                '{"value": 4}',
                '{
                    "properties": {
                        "value": {"type": "integer", "maximum": 3, "exclusiveMaximum": false}
                    }
                }'
            ],
            [
                '{"value": 0.00}',
                '{
                    "properties": {
                        "value": {"type": "number", "minimum": 0, "exclusiveMinimum": true}
                    }
                }'
            ],
            [
                '{"value": 0.00}',
                '{
                    "properties": {
                        "value": {"type": "number", "maximum": 0, "exclusiveMaximum": true}
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
                  "value":6
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"integer","minimum":4}
                  }
                }'
            ],
            [
                '{
                  "value":6
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"integer","maximum":8}
                  }
                }'
            ],
            [
                '{"value": 6}',
                '{
                    "type": "object",
                    "properties": {
                        "value": {"type": "integer", "minimum": 6, "exclusiveMinimum": false}
                    }
                }'
            ],
            [
                '{"value": 6}',
                '{
                    "type": "object",
                    "properties": {
                        "value": {"type": "integer", "maximum": 6, "exclusiveMaximum": false}
                    }
                }'
            ],
            [
                '{"value": 6}',
                '{
                    "type": "object",
                    "properties": {
                        "value": {"type": "integer", "minimum": 6}
                    }
                }'
            ],
            [
                '{"value": 6}',
                '{
                    "type": "object",
                    "properties": {
                        "value": {"type": "integer", "maximum": 6}
                    }
                }'
            ]
        ];
    }
}
