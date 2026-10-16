<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints;

class MinimumMaximumTest extends BaseTestCase
{
    /** @var bool */
    protected $validateSchema = true;

    public function getInvalidTests(): \Generator
    {
        yield [
            '{
              "value":2
            }',
            '{
              "type":"object",
              "properties":{
                "value":{"type":"integer","minimum":4}
              }
            }'
        ];
        yield [
            '{"value": 3}',
            '{
                "type": "object",
                "properties": {
                    "value": {"type": "integer", "minimum": 3, "exclusiveMinimum": true}
                }
            }'
        ];
        yield [
            '{
              "value":16
            }',
            '{
              "type":"object",
              "properties":{
                "value":{"type":"integer","maximum":8}
              }
            }'
        ];
        yield [
            '{"value": 8}',
            '{
                "type": "object",
                "properties": {
                    "value": {"type": "integer", "maximum": 8, "exclusiveMaximum": true}
                }
            }'
        ];
        yield [
            '{"value": 4}',
            '{
                "type": "object",
                "properties": {
                    "value": {"type": "integer", "exclusiveMinimum": true}
                }
            }'
        ];
        yield [
            '{"value": 4}',
            '{
                "type": "object",
                "properties": {
                    "value": {"type": "integer", "exclusiveMaximum": true}
                }
            }'
        ];
        yield [
            '{"value": 4}',
            '{
                "type": "object",
                "properties": {
                    "value": {"type": "integer", "minimum": 5, "exclusiveMinimum": false}
                }
            }'
        ];
        yield [
            '{"value": 4}',
            '{
                "properties": {
                    "value": {"type": "integer", "maximum": 3, "exclusiveMaximum": false}
                }
            }'
        ];
        yield [
            '{"value": 0.00}',
            '{
                "properties": {
                    "value": {"type": "number", "minimum": 0, "exclusiveMinimum": true}
                }
            }'
        ];
        yield [
            '{"value": 0.00}',
            '{
                "properties": {
                    "value": {"type": "number", "maximum": 0, "exclusiveMaximum": true}
                }
            }'
        ];
        yield 'Float minimum is reported without being cast to int' => [
            '{"value": 1.2}',
            '{
                "properties": {
                    "value": {"type": "number", "minimum": 1.5}
                }
            }',
            null,
            [[
                'property' => 'value',
                'pointer' => '/value',
                'message' => 'Must have a minimum value greater than or equal to 1.5',
                'constraint' => ['name' => 'minimum', 'params' => ['minimum' => 1.5]],
                'context' => 1,
            ]],
        ];
        yield 'Float exclusive minimum is reported without being cast to int' => [
            '{"value": 1.5}',
            '{
                "properties": {
                    "value": {"type": "number", "minimum": 1.5, "exclusiveMinimum": true}
                }
            }',
            null,
            [[
                'property' => 'value',
                'pointer' => '/value',
                'message' => 'Must have a minimum value greater than 1.5',
                'constraint' => ['name' => 'exclusiveMinimum', 'params' => ['minimum' => 1.5]],
                'context' => 1,
            ]],
        ];
        yield 'Float maximum is reported without being cast to int' => [
            '{"value": 2.7}',
            '{
                "properties": {
                    "value": {"type": "number", "maximum": 2.5}
                }
            }',
            null,
            [[
                'property' => 'value',
                'pointer' => '/value',
                'message' => 'Must have a maximum value less than or equal to 2.5',
                'constraint' => ['name' => 'maximum', 'params' => ['maximum' => 2.5]],
                'context' => 1,
            ]],
        ];
        yield 'Float exclusive maximum is reported without being cast to int' => [
            '{"value": 2.5}',
            '{
                "properties": {
                    "value": {"type": "number", "maximum": 2.5, "exclusiveMaximum": true}
                }
            }',
            null,
            [[
                'property' => 'value',
                'pointer' => '/value',
                'message' => 'Must have a maximum value less than 2.5',
                'constraint' => ['name' => 'exclusiveMaximum', 'params' => ['maximum' => 2.5]],
                'context' => 1,
            ]],
        ];
    }

    public function getValidTests(): \Generator
    {
        yield [
            '{
              "value":6
            }',
            '{
              "type":"object",
              "properties":{
                "value":{"type":"integer","minimum":4}
              }
            }'
        ];
        yield [
            '{
              "value":6
            }',
            '{
              "type":"object",
              "properties":{
                "value":{"type":"integer","maximum":8}
              }
            }'
        ];
        yield [
            '{"value": 6}',
            '{
                "type": "object",
                "properties": {
                    "value": {"type": "integer", "minimum": 6, "exclusiveMinimum": false}
                }
            }'
        ];
        yield [
            '{"value": 6}',
            '{
                "type": "object",
                "properties": {
                    "value": {"type": "integer", "maximum": 6, "exclusiveMaximum": false}
                }
            }'
        ];
        yield [
            '{"value": 6}',
            '{
                "type": "object",
                "properties": {
                    "value": {"type": "integer", "minimum": 6}
                }
            }'
        ];
        yield [
            '{"value": 6}',
            '{
                "type": "object",
                "properties": {
                    "value": {"type": "integer", "maximum": 6}
                }
            }'
        ];
    }
}
