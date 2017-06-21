<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests;

use JsonSchema\Validator;

class RefTest extends \PHPUnit_Framework_TestCase
{
    public function dataRefIgnoresSiblings()
    {
        return array(
            // #0 check that $ref is resolved and the instance is validated against
            // the referenced schema
            array(
                '{
                    "definitions":{"test": {"type": "integer"}},
                    "properties": {
                        "propertyOne": {"$ref": "#/definitions/test"}
                    }
                }',
                '{"propertyOne": "not an integer"}',
                false
            ),
            // #1 check that sibling properties of $ref are ignored during validation
            array(
                '{
                    "definitions":{
                        "test": {"type": "integer"}
                    },
                    "properties": {
                        "propertyOne": {
                            "$ref": "#/definitions/test",
                            "maximum": 5
                        }
                    }
                }',
                '{"propertyOne": 10}',
                true
            ),
        );
    }

    /** @dataProvider dataRefIgnoresSiblings */
    public function testRefIgnoresSiblings($schema, $document, $isValid)
    {
        $document = json_decode($document);
        $schema = json_decode($schema);

        $v = new Validator();
        $v->validate($document, $schema);

        $this->assertEquals($isValid, $v->isValid());
    }
}
