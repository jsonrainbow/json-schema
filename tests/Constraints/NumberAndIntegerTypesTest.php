<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints;

class NumberAndIntegerTypesTest extends BaseTestCase
{
    /** @var bool */
    protected $validateSchema = true;

    public function getInvalidTests(): \Generator
    {
        yield [
            'input' => '{ "integer": 1.4 }',
            'schema' => '{
              "type":"object",
              "properties":{
                "integer":{"type":"integer"}
              }
            }'
        ];
        yield [
            'input' => '{"integer": 1.001}',
            'schema' => '{
                "type": "object",
                "properties": {
                    "integer": {"type": "integer"}
                }
            }'
        ];
        yield [
            'input' => '{"integer": true}',
            'schema' => '{
                "type": "object",
                "properties": {
                    "integer": {"type": "integer"}
                }
            }'
        ];
        yield [
            'input' => '{"number": "x"}',
            'schema' => '{
                "type": "object",
                "properties": {
                    "number": {"type": "number"}
                }
            }'
        ];
    }

    public function getValidTests(): \Generator
    {
        yield [
            'input' => '{ "integer": 1 }',
            'schema' => '{
              "type":"object",
              "properties":{
                "integer":{"type":"integer"}
              }
            }'
        ];
        yield [
            'input' => '{ "number": 1.4 }',
            'schema' => '{
              "type":"object",
              "properties":{
                "number":{"type":"number"}
              }
            }'
        ];
        yield [
            'input' => '{"number": 1e5}',
            'schema' => '{
                "type": "object",
                "properties": {
                    "number": {"type": "number"}
                }
            }'
        ];
        yield [
            'input' => '{"number": 1}',
            'schema' => '{
                "type": "object",
                "properties": {
                    "number": {"type": "number"}

                }
            }'
        ];
        yield [
            'input' => '{"number": -49.89}',
            'schema' => '{
                "type": "object",
                "properties": {
                    "number": {
                      "type": "number",
                      "multipleOf": 0.01
                    }
                }
            }'
        ];
    }
}
