<?php

namespace JsonSchema\Tests;

class SelfDefinedSchemaTest extends BaseTestCase
{
    public function getInvalidTests()
    {
        return array(
            array(
                '{
                    "$schema": {
                        "properties": {
                            "name": {
                                "type": "string"
                            },
                            "age" : {
                                "type": "integer",
                                "maximum": 25
                            }
                        }
                    },
                    "name" : "John Doe",
                    "age" : 30,
                    "type" : "object"
                }',
                ''
            )
        );
    }

    public function getValidTests()
    {
        return array(
            array(
                '{
                    "$schema": {
                        "properties": {
                            "name": {
                                "type": "string"
                            },
                            "age" : {
                                "type": "integer",
                                "maximum": 125
                            }
                        }
                    },
                    "name" : "John Doe",
                    "age" : 30,
                    "type" : "object"
                }',
                ''
            )
        );
    }
}
