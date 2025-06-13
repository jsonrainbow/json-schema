<?php

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
