<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class NumberAndIntegerTypesTest extends BaseTestCase
{
    protected $validateSchema = true;

    public function getInvalidTests(): array
    {
        return [
            [
                'input' => '{
                  "integer": 1.4
                }',
                'schema' => '{
                  "type":"object",
                  "properties":{
                    "integer":{"type":"integer"}
                  }
                }'
            ],
            [
                'input' => '{"integer": 1.001}',
                'schema' => '{
                    "type": "object",
                    "properties": {
                        "integer": {"type": "integer"}
                    }
                }'
            ],
            [
                'input' => '{"integer": true}',
                'schema' => '{
                    "type": "object",
                    "properties": {
                        "integer": {"type": "integer"}
                    }
                }'
            ],
            [
                'input' => '{"number": "x"}',
                'schema' => '{
                    "type": "object",
                    "properties": {
                        "number": {"type": "number"}
                    }
                }'
            ]
        ];
    }

    public function getValidTests(): array
    {
        return [
            [
                'input' => '{
                  "integer": 1
                }',
                'schema' => '{
                  "type":"object",
                  "properties":{
                    "integer":{"type":"integer"}
                  }
                }'
            ],
            [
                'input' => '{
                  "number": 1.4
                }',
                'schema' => '{
                  "type":"object",
                  "properties":{
                    "number":{"type":"number"}
                  }
                }'
            ],
            [
                'input' => '{"number": 1e5}',
                'schema' => '{
                    "type": "object",
                    "properties": {
                        "number": {"type": "number"}
                    }
                }'
            ],
            [
                'input' => '{"number": 1}',
                'schema' => '{
                    "type": "object",
                    "properties": {
                        "number": {"type": "number"}

                    }
                }'
            ],
            [
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
            ]
        ];
    }
}
