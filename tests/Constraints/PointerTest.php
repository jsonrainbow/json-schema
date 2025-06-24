<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints;

use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;

class PointerTest extends TestCase
{
    protected $validateSchema = true;

    public function testVariousPointers(): void
    {
        $schema = [
            'type' => 'object',
            'required' => ['prop1', 'prop2', 'prop3', 'prop4'],
            'properties' => [
                'prop1' => [
                    'type' => 'string'
                ],
                'prop2' => [
                    'type' => 'object',
                    'required' => ['prop2.1'],
                    'properties' => [
                        'prop2.1' => [
                            'type' => 'string'
                        ]
                    ]
                ],
                'prop3' => [
                    'type' => 'object',
                    'required' => ['prop3/1'],
                    'properties' => [
                        'prop3/1' => [
                            'type' => 'object',
                            'required' => ['prop3/1.1'],
                            'properties' => [
                                'prop3/1.1' => [
                                    'type' => 'string'
                                ]
                            ]
                        ]
                    ]
                ],
                'prop4' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'items' => [
                        'type' => 'object',
                        'required' => ['prop4-child'],
                        'properties' => [
                            'prop4-child' => [
                                'type' => 'string'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $value = [
            'prop2' => [
                'foo' => 'bar'
            ],
            'prop3' => [
                'prop3/1' => [
                    'foo' => 'bar'
                ]
            ],
            'prop4' => [
                [
                    'foo' => 'bar'
                ]
            ]
        ];

        $validator = new Validator();
        $checkValue = json_decode(json_encode($value));
        $validator->validate($checkValue, json_decode(json_encode($schema)));

        $this->assertEquals(
            [
                [
                    'property' => 'prop1',
                    'pointer' => '/prop1',
                    'message' => 'The property prop1 is required',
                    'constraint' => [
                        'name' => 'required',
                        'params' => [
                            'property' => 'prop1'
                        ]
                    ],
                    'context'    => Validator::ERROR_DOCUMENT_VALIDATION
                ],
                [
                    'property' => 'prop2.prop2.1',
                    'pointer' => '/prop2/prop2.1',
                    'message' => 'The property prop2.1 is required',
                    'constraint' => [
                        'name' => 'required',
                        'params' => [
                            'property' => 'prop2.1'
                        ]
                    ],
                    'context'    => Validator::ERROR_DOCUMENT_VALIDATION
                ],
                [
                    'property' => 'prop3.prop3/1.prop3/1.1',
                    'pointer' => '/prop3/prop3~11/prop3~11.1',
                    'message' => 'The property prop3/1.1 is required',
                    'constraint' => [
                        'name' => 'required',
                        'params' => [
                            'property' => 'prop3/1.1'
                        ]
                    ],
                    'context'    => Validator::ERROR_DOCUMENT_VALIDATION
                ],
                [
                    'property' => 'prop4[0].prop4-child',
                    'pointer' => '/prop4/0/prop4-child',
                    'message' => 'The property prop4-child is required',
                    'constraint' => [
                        'name' => 'required',
                        'params' => [
                            'property' => 'prop4-child'
                        ]
                    ],
                    'context'    => Validator::ERROR_DOCUMENT_VALIDATION
                ]
            ],
            $validator->getErrors()
        );
    }
}
