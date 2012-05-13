<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class MinimumMaximumTest extends BaseTestCase
{
    public function getInvalidTests()
    {
        return array(
            array(
                '{
                  "value":2
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"integer","minimum":4}
                  }
                }'
            ),
            array(
                '{
                  "value":16
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"integer","maximum":8}
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
                    "value":{"type":"integer","minimum":4}
                  }
                }'
            ),
            array(
                '{
                  "value":6
                }',
                '{
                  "type":"object",
                  "properties":{
                    "value":{"type":"integer","maximum":8}
                  }
                }'
            )
        );
    }
}
