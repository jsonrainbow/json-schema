<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints;

class DivisibleByTest extends BaseTestCase
{
    /** @var bool */
    protected $validateSchema = true;

    public function getInvalidTests(): \Generator
    {
        yield [
            '{"value": 5.6333}',
            '{
              "type":"object",
              "properties":{
                "value":{"type":"number","divisibleBy":3}
              }
            }'
        ];
        yield [
            '{"value": 35}',
            '{
                "type": "object",
                "properties": {
                    "value": {"type": "integer", "divisibleBy": 1.5}
                }
            }'
        ];
        yield [
            '{"value": 0.00751}',
            '{
                "type": "object",
                "properties": {
                    "value": {"type": "number", "divisibleBy": 0.0001}
                }
            }'
        ];
        yield [
            '{"value": 7}',
            '{
                "type": "object",
                "properties": {
                    "value": {"type": "integer", "divisibleBy": 2}
                }
            }'
        ];
    }

    public function getValidTests(): \Generator
    {
        yield [
            '{"value": 6}',
            '{
              "type":"object",
              "properties":{
                "value":{"type":"number","divisibleBy":3}
              }
            }'
        ];
        yield [
            '{"value": 4.5}',
            '{
                "type": "object",
                "properties": {
                    "value": {"type": "number", "divisibleBy": 1.5}
                }
            }'
        ];
        yield [
            '{"value": 0.0075}',
            '{
                "properties": {
                    "value": {"type": "number", "divisibleBy": 0.0001}
                }
            }'
        ];
        yield [
            '{"value": 1}',
            '{
                "properties": {
                    "value": {"type": "number", "divisibleBy": 0.02}
                }
            }'
        ];
    }
}
