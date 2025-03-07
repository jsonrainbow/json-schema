<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

use JsonSchema\Constraints\Constraint;

class MinMaxPropertiesTest extends BaseTestCase
{
    protected $validateSchema = true;

    /**
     * {@inheritdoc}
     */
    public function getValidTests(): array
    {
        return [
            'Empty object with minProperties: 0' => [
                'input' => '{
                  "value": {}
                }',
                'schema' => '{
                  "type": "object",
                  "properties": {
                    "value": {"type": "object", "minProperties": 0}
                  }
                }'
            ],
            'Empty object with maxProperties: 1' => [
                'input' => '{
                  "value": {}
                }',
                'schema' => '{
                  "type": "object",
                  "properties": {
                    "value": {"type": "object", "maxProperties": 1}
                  }
                }'
            ],
            'Empty object with minProperties: 0 and maxProperties: 1' => [
                'input' => '{
                  "value": {}
                }',
                'schema' => '{
                  "type": "object",
                  "properties": {
                    "value": {"type": "object", "minProperties": 0,"maxProperties": 1}
                  }
                }'
            ],
            'Object with two properties with minProperties: 1 and maxProperties: 2' => [
                'input' => '{
                  "value": {"foo": 1, "bar": 2}
                }',
                'schema' => '{
                  "type": "object",
                  "properties": {
                    "value": {"type": "object", "minProperties": 1,"maxProperties": 2}
                  }
                }'
            ],
            'Empty array with minProperties: 1 and maxProperties: 2' => [
                'input' => '{
                  "value": []
                }',
                'schema' => '{
                  "properties": {
                    "value": {"minProperties": 1,"maxProperties": 2}
                  }
                }',
                'checkMode' => Constraint::CHECK_MODE_NORMAL,
            ],
            'Array with two items with maxProperties: 1' => [
                'input' => '{
                  "value": [1, 2]
                }',
                'schema' => '{
                  "properties": {
                    "value": {"maxProperties": 1}
                  }
                }'
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getInvalidTests(): array
    {
        return [
            'Empty object with minProperties: 1' => [
                'input' => '{
                  "value": {}
                }',
                'schema' => '{
                  "type": "object",
                  "properties": {
                    "value": {"type": "object", "minProperties": 1}
                  }
                }'
            ],
            'Empty object with minProperties' => [
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
            ],
            'Object with two properties with maxProperties: 1' => [
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
            ],
            'Object with two properties with minProperties: 1 and maxProperties: 2' => [
                'input' => '{
                  "value": {"foo": 1, "bar": 2, "baz": 3}
                }',
                'schema' => '{
                  "type": "object",
                  "properties": {
                    "value": {"type": "object", "minProperties": 1,"maxProperties": 2}
                  }
                }'
            ],
        ];
    }
}
