<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints;

use JsonSchema\Constraints\Constraint;

class MinMaxPropertiesTest extends BaseTestCase
{
    /** @var bool */
    protected $validateSchema = true;

    public function getValidTests(): \Generator
    {
        yield 'Empty object with minProperties: 0' => [
            'input' => '{
              "value": {}
            }',
            'schema' => '{
              "type": "object",
              "properties": {
                "value": {"type": "object", "minProperties": 0}
              }
            }'
        ];
        yield 'Empty object with maxProperties: 1' => [
            'input' => '{
              "value": {}
            }',
            'schema' => '{
              "type": "object",
              "properties": {
                "value": {"type": "object", "maxProperties": 1}
              }
            }'
        ];
        yield 'Empty object with minProperties: 0 and maxProperties: 1' => [
            'input' => '{
              "value": {}
            }',
            'schema' => '{
              "type": "object",
              "properties": {
                "value": {"type": "object", "minProperties": 0,"maxProperties": 1}
              }
            }'
        ];
        yield 'Object with two properties with minProperties: 1 and maxProperties: 2' => [
            'input' => '{
              "value": {"foo": 1, "bar": 2}
            }',
            'schema' => '{
              "type": "object",
              "properties": {
                "value": {"type": "object", "minProperties": 1,"maxProperties": 2}
              }
            }'
        ];
        yield 'Empty array with minProperties: 1 and maxProperties: 2' => [
            'input' => '{
              "value": []
            }',
            'schema' => '{
              "properties": {
                "value": {"minProperties": 1,"maxProperties": 2}
              }
            }',
            'checkMode' => Constraint::CHECK_MODE_NORMAL,
        ];
        yield 'Array with two items with maxProperties: 1' => [
            'input' => '{
              "value": [1, 2]
            }',
            'schema' => '{
              "properties": {
                "value": {"maxProperties": 1}
              }
            }'
        ];
    }

    public function getInvalidTests(): \Generator
    {
        yield 'Empty object with minProperties: 1' => [
            'input' => '{
              "value": {}
            }',
            'schema' => '{
              "type": "object",
              "properties": {
                "value": {"type": "object", "minProperties": 1}
              }
            }'
        ];
        yield 'Empty object with minProperties' => [
            'input' => '{}',
            'schema' => '{
              "type": "object",
              "properties": {
                "propertyOne": {
                  "type": "string"
                },
                "propertyTwo": {
                  "type": "string"
                }
              },
              "minProperties": 1
            }'
        ];
        yield 'Object with two properties with maxProperties: 1' => [
            'input' => '{
              "value": {
                "propertyOne": "valueOne",
                "propertyTwo": "valueTwo"
              }
            }',
            'schema' => '{
              "type": "object",
              "properties": {
                "value": {"type": "object", "maxProperties": 1}
              }
            }'
        ];
        yield 'Object with two properties with minProperties: 1 and maxProperties: 2' => [
            'input' => '{
              "value": {"foo": 1, "bar": 2, "baz": 3}
            }',
            'schema' => '{
              "type": "object",
              "properties": {
                "value": {"type": "object", "minProperties": 1,"maxProperties": 2}
              }
            }'
        ];
    }
}
