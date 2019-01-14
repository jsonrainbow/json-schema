<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class MinMaxPropertiesTest extends BaseTestCase
{
    protected $validateSchema = true;

    /**
     * {@inheritdoc}
     */
    public function getValidTests()
    {
        return array(
            array(
                '{
                  "value": {}
                }',
                '{
                  "type": "object",
                  "properties": {
                    "value": {"type": "object", "minProperties": 0}
                  }
                }'
            ),
            array(
                '{
                  "value": {}
                }',
                '{
                  "type": "object",
                  "properties": {
                    "value": {"type": "object", "maxProperties": 1}
                  }
                }'
            ),
            array(
                '{
                  "value": {}
                }',
                '{
                  "type": "object",
                  "properties": {
                    "value": {"type": "object", "minProperties": 0,"maxProperties": 1}
                  }
                }'
            ),
            array(
                '{
                  "value": {"foo": 1, "bar": 2}
                }',
                '{
                  "type": "object",
                  "properties": {
                    "value": {"type": "object", "minProperties": 1,"maxProperties": 2}
                  }
                }'
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getInvalidTests()
    {
        return array(
            array(
                '{
                  "value": {}
                }',
                '{
                  "type": "object",
                  "properties": {
                    "value": {"type": "object", "minProperties": 1}
                  }
                }'
            ),
            array(
                '{}',
                '{
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
            ),
            array(
                '{
                  "value": {
                    "propertyOne": "valueOne",
                    "propertyTwo": "valueTwo"
                  }
                }',
                '{
                  "type": "object",
                  "properties": {
                    "value": {"type": "object", "maxProperties": 1}
                  }
                }'
            ),
            array(
                '{
                  "value": {"foo": 1, "bar": 2, "baz": 3}
                }',
                '{
                  "type": "object",
                  "properties": {
                    "value": {"type": "object", "minProperties": 1,"maxProperties": 2}
                  }
                }'
            ),
            array(
                '{
                  "value": []
                }',
                '{
                  "properties": {
                    "value": {"minProperties": 1,"maxProperties": 2}
                  }
                }'
            ),
        );
    }
}
