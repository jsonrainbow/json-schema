<?php

namespace JsonSchema\Tests;

class DivisibleByTest extends BaseTestCase
{
    public function getInvalidTests()
    {
        return array(
            array(
                '{
                  "value":5.6333
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"number","divisibleBy":3}
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
                  "value":6
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"number","divisibleBy":3}
                  }
                }'
            )
        );
    }
}
