<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

use JsonSchema\Constraints\Constraint;

class MinItemsMaxItemsTest extends BaseTestCase
{
    protected $validateSchema = true;

    public function getInvalidTests(): array
    {
        return [
            'Input violating minItems constraint' => [
                'input' => '{
                  "value":[2]
                }',
                'schema' => '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"array","minItems":2,"maxItems":4}
                  }
                }',
                'checkMode' => Constraint::CHECK_MODE_NORMAL,
                [[
                    'property' => 'value',
                    'pointer' => '/value',
                    'message' => 'There must be a minimum of 2 items in the array, 1 found',
                    'constraint' => [
                        'name' => 'minItems',
                        'params' => [
                            'minItems' => 2,
                            'found' => 1
                        ]
                    ],
                    'context' => 1
                ]]
            ],
            'Input violating maxItems constraint' => [
                'input' => '{
                  "value":[2,2,5,8,5]
                }',
                'schema' => '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"array","minItems":2,"maxItems":4}
                  }
                }',
                'checkMode' => Constraint::CHECK_MODE_NORMAL,
                [[
                    'property' => 'value',
                    'pointer' => '/value',
                    'message' => 'There must be a maximum of 4 items in the array, 5 found',
                    'constraint' => [
                        'name' => 'maxItems',
                        'params' => [
                            'maxItems' => 4,
                            'found' => 5
                        ]
                    ],
                    'context' => 1
                ]]
            ]
        ];
    }

    public function getValidTests(): array
    {
        return [
            [
                '{
                  "value":[2,2]
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"array","minItems":2,"maxItems":4}
                  }
                }'
            ],
            [
                '{
                  "value":[2,2,5,8]
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"array","minItems":2,"maxItems":4}
                  }
                }'
            ]
        ];
    }
}
