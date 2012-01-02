<?php

namespace JsonSchema\Tests;

class UniqueItemsTest extends BaseTestCase
{
    public function getInvalidTests()
    {
        return array(
            array(
                '[1,2,2]',
                '{
                  "type":"array",
                  "uniqueItems": true
                }'
            ),
/*            array(
                '[{"a":"b"},{"a":"c"},{"a":"b"}]',
                '{
                  "type":"array",
                  "uniqueItems": true
                }'
            )*/
        );
    }

    public function getValidTests()
    {
        return array(
            array(
                '[1,2,3]',
                '{
                  "type":"array",
                  "uniqueItems": true
                }'
            )
        );
    }
}
