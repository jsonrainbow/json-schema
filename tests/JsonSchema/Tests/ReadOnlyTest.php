<?php

namespace JsonSchema\Tests;

class ReadOnlyTest extends BaseTestCase
{
    public function getInvalidTests()
    {
        //is readonly really required?
        return array(
            array(
                '{ "number": [] }',
                '{
                  "type":"object",
                  "properties":{
                    "number":{"type":"string","readonly":true}
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
                  "number": "1.4"
                }',
                '{
                  "type":"object",
                  "properties":{
                    "number":{"type":"string","readonly":true}
                  }
                }'
            )
        );
    }
}
