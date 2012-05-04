<?php

namespace JsonSchema\Tests;

class PatternPropertiesTest extends BaseTestCase
{
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
                    )
                )),
                json_encode(array(
                    'type' => 'object',
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
                    )
                ))
            ),
        );
    }
}

