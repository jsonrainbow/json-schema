<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class PatternPropertiesTest extends BaseTestCase
{
    protected $validateSchema = true;

    public function getInvalidTests()
    {
        return array(
            // matches pattern but invalid schema for object
            array(
                json_encode(array(
                    'someobject' => array(
                        'foobar' => 'foo',
                        'barfoo' => 'bar',
                    )
                )),
                json_encode(array(
                    'type' => 'object',
                    'patternProperties' => array(
                        '^someobject$' => array(
                            'type' => 'object',
                            'additionalProperties' => false,
                            'properties' => array(
                                'barfoo' => array(
                                    'type' => 'string',
                                ),
                            )
                        )
                    )
                ))
            ),
            // Does not match pattern
            array(
                json_encode(array(
                        'regex_us' => false,
                    )),
                json_encode(array(
                        'type' => 'object',
                        'patternProperties' => array(
                            '^[a-z]+_(jp|de)$' => array(
                                'type' => array('boolean')
                            )
                        ),
                        'additionalProperties' => false
                    ))
            ),
            // Does not match pattern with unicode
            array(
                json_encode(array(
                        '猡猡獛' => false,
                    )),
                json_encode(array(
                        'type' => 'object',
                        'patternProperties' => array(
                            '^[\\x{0080}-\\x{006FFF}]+$' => array(
                                'type' => array('boolean')
                            )
                        ),
                        'additionalProperties' => false
                    ))
            ),
            // An invalid regular expression pattern
            array(
                json_encode(array(
                        'regex_us' => false,
                    )),
                json_encode(array(
                        'type' => 'object',
                        'patternProperties' => array(
                            '^[a-z+_jp|de)$' => array(
                                'type' => array('boolean')
                            )
                        ),
                        'additionalProperties' => false
                    ))
            ),
        );
    }

    public function getValidTests()
    {
        return array(
            array(
                // validates pattern schema
                json_encode(array(
                    'someobject' => array(
                        'foobar' => 'foo',
                        'barfoo' => 'bar',
                    ),
                    'someotherobject' => array(
                        'foobar' => 1234,
                    ),
                    '/products' => array(
                        'get' => array()
                    ),
                    '#products' => array(
                        'get' => array()
                    ),
                    '+products' => array(
                        'get' => array()
                    ),
                    '~products' => array(
                        'get' => array()
                    ),
                    '*products' => array(
                        'get' => array()
                    ),
                    '%products' => array(
                        'get' => array()
                    )
                )),
                json_encode(array(
                    'type' => 'object',
                    'additionalProperties' => false,
                    'patternProperties' => array(
                        '^someobject$' => array(
                            'type' => 'object',
                            'properties' => array(
                                'foobar' => array('type' => 'string'),
                                'barfoo' => array('type' => 'string'),
                            ),
                        ),
                        '^someotherobject$' => array(
                            'type' => 'object',
                            'properties' => array(
                                'foobar' => array('type' => 'number'),
                            ),
                        ),
                        '^/' => array(
                            'type' => 'object',
                            'properties' => array(
                                'get' => array('type' => 'array')
                            )
                        ),
                        '^#' => array(
                            'type' => 'object',
                            'properties' => array(
                                'get' => array('type' => 'array')
                            )
                        ),
                        '^\+' => array(
                            'type' => 'object',
                            'properties' => array(
                                'get' => array('type' => 'array')
                            )
                        ),
                        '^~' => array(
                            'type' => 'object',
                            'properties' => array(
                                'get' => array('type' => 'array')
                            )
                        ),
                        '^\*' => array(
                            'type' => 'object',
                            'properties' => array(
                                'get' => array('type' => 'array')
                            )
                        ),
                        '^%' => array(
                            'type' => 'object',
                            'properties' => array(
                                'get' => array('type' => 'array')
                            )
                        )
                    )
                ))
            ),
            array(
                json_encode(array(
                        'foobar' => true,
                        'regex_us' => 'foo',
                        'regex_de' => 1234
                    )),
                json_encode(array(
                        'type' => 'object',
                        'properties' => array(
                            'foobar' => array('type' => 'boolean')
                        ),
                        'patternProperties' => array(
                            '^[a-z]+_(us|de)$' => array(
                                'type' => array('string', 'integer')
                            )
                        ),
                        'additionalProperties' => false
                    ))
            ),
            // Does match pattern with unicode
            array(
                json_encode(array(
                    'ðæſ' => 'unicode',
                )),
                json_encode(array(
                    'type' => 'object',
                    'patternProperties' => array(
                        '^[\\x{0080}-\\x{10FFFF}]+$' => array(
                            'type' => array('string')
                        )
                    ),
                    'additionalProperties' => false
                ))
            ),
        );
    }
}
