<?php

namespace JsonSchema\Tests;

class ExtendsTest extends BaseTestCase
{
    public function getInvalidTests()
    {
        return array(
            array(
                '{
                  "name":"bruno",
                  "age":50
                }',
                '{
                    "id": "person",
                    "type": "object",
                    "properties": {
                        "name": {
                            "type": "string"
                        },
                        "age" : {
                            "type": "integer",
                             "maximum":120
                        }
                    },
                    "extends": {
                        "id": "oldPerson",
                        "type": "object",
                        "properties": {
                            "age" : {"minimum":70}
                        }
                    }
                }'
            ),
            array(
                '{
                  "name":"bruno",
                  "age":180
                }',
                '{
                    "id": "person",
                    "type": "object",
                    "properties": {
                        "name": {
                            "type": "string"
                        },
                        "age" : {
                            "type": "integer",
                             "maximum":120
                        }
                    },
                    "extends": {
                        "id": "oldPerson",
                        "type": "object",
                        "properties": {
                            "age" : {"minimum":70}
                        }
                    }
                }'
            )
        );
    }

    public function getValidTests()
    {
        return array(
            array(
                '{
                  "name":"bruno",
                  "age":80
                }',
                '{
                    "id": "person",
                    "type": "object",
                    "properties": {
                        "name": {
                            "type": "string"
                        },
                        "age" : {
                            "type": "integer",
                             "maximum":120
                        }
                    },
                    "extends": {
                        "id": "oldPerson",
                        "type": "object",
                        "properties": {
                            "age" : {"minimum":70}
                        }
                    }
                }'
            )
        );
    }
}
