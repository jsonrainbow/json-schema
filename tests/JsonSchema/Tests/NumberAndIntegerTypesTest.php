<?php

namespace JsonSchema\Tests;

class NumberAndIntegerTypesTest extends BaseTestCase
{
    public function getInvalidTests()
    {
        return array(
            array(
                '{
                  "number": 1.4
                }',
                '{
                  "type":"object",
                  "properties":{
                    "number":{"type":"integer"}
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
                  "number": 1
                }',
                '{
                  "type":"object",
                  "properties":{
                    "number":{"type":"number"}
                  }
                }'
            ),
            array(
                '{
                  "number": 1.4
                }',
                '{
                  "type":"object",
                  "properties":{
                    "number":{"type":"number"}
                  }
                }'
            )
        );
    }
}
