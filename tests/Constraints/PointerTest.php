<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;

class PointerTest extends TestCase
{
    protected $validateSchema = true;

    public function testVariousPointers()
    {
        $schema = array(
            'type' => 'object',
            'required' => array('prop1', 'prop2', 'prop3', 'prop4'),
            'properties' => array(
                'prop1' => array(
                    'type' => 'string'
                ),
                'prop2' => array(
                    'type' => 'object',
                    'required' => array('prop2.1'),
                    'properties' => array(
                        'prop2.1' => array(
                            'type' => 'string'
                        )
                    )
                ),
                'prop3' => array(
                    'type' => 'object',
                    'required' => array('prop3/1'),
                    'properties' => array(
                        'prop3/1' => array(
                            'type' => 'object',
                            'required' => array('prop3/1.1'),
                            'properties' => array(
                                'prop3/1.1' => array(
                                    'type' => 'string'
                                )
                            )
                        )
                    )
                ),
                'prop4' => array(
                    'type' => 'array',
                    'minItems' => 1,
                    'items' => array(
                        'type' => 'object',
                        'required' => array('prop4-child'),
                        'properties' => array(
                            'prop4-child' => array(
                                'type' => 'string'
                            )
                        )
                    )
                )
            )
        );

        $value = array(
            'prop2' => array(
                'foo' => 'bar'
            ),
            'prop3' => array(
                'prop3/1' => array(
                    'foo' => 'bar'
                )
            ),
            'prop4' => array(
                array(
                    'foo' => 'bar'
                )
            )
        );

        $validator = new Validator();
        $checkValue = json_decode(json_encode($value));
        $validator->check($checkValue, json_decode(json_encode($schema)));

        $this->assertEquals(
            array(
                array(
                    'property' => 'prop1',
                    'pointer' => '/prop1',
                    'message' => 'The property prop1 is required',
                    'constraint' => 'required',
                    'context'    => Validator::ERROR_DOCUMENT_VALIDATION
                ),
                array(
                    'property' => 'prop2.prop2.1',
                    'pointer' => '/prop2/prop2.1',
                    'message' => 'The property prop2.1 is required',
                    'constraint' => 'required',
                    'context'    => Validator::ERROR_DOCUMENT_VALIDATION
                ),
                array(
                    'property' => 'prop3.prop3/1.prop3/1.1',
                    'pointer' => '/prop3/prop3~11/prop3~11.1',
                    'message' => 'The property prop3/1.1 is required',
                    'constraint' => 'required',
                    'context'    => Validator::ERROR_DOCUMENT_VALIDATION
                ),
                array(
                    'property' => 'prop4[0].prop4-child',
                    'pointer' => '/prop4/0/prop4-child',
                    'message' => 'The property prop4-child is required',
                    'constraint' => 'required',
                    'context'    => Validator::ERROR_DOCUMENT_VALIDATION
                )
            ),
            $validator->getErrors()
        );
    }
}
