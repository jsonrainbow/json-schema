<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

use JsonSchema\Constraints\Constraint;
use JsonSchema\Exception\ValidationException;
use JsonSchema\Validator;

class NotTest extends BaseTestCase
{
    protected $validateSchema = true;

    public function getInvalidTests()
    {
        return array(
            array(
                '{
                    "x": [1, 2]
                }',
                '{
                    "properties": {
                        "x": {
                            "not": {
                                "type": "array",
                                "items": {"type": "integer"},
                                "minItems": 2
                            }
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
                    "x": [1]
                }',
                '{
                    "properties": {
                        "x": {
                            "not": {
                                "type": "array",
                                "items": {"type": "integer"},
                                "minItems": 2
                            }
                        }
                    }
                }'
            ),
            array(
                '{
                    "x": ["foo", 2]
                }',
                '{
                    "properties": {
                        "x": {
                            "not": {
                                "type": "array",
                                "items": {"type": "integer"},
                                "minItems": 2
                            }
                        }
                    }
                }'
            )
        );
    }

    public function testNotEnumWithExceptionConstraint()
    {
        $schema = json_decode('{
            "type": "object",
            "properties": {
                "foo": {
                    "type": "string",
                    "not": {
                        "enum": ["baz"]
                    }
                }
            }
        }');

        $dataPass = json_decode('{"foo":"bar"}');
        $dataFail = json_decode('{"foo":"baz"}');

        $v = new Validator();

        // Test successful not-num match
        $v->validate($dataPass, $schema, Constraint::CHECK_MODE_EXCEPTIONS);
        $this->assertTrue($v->isValid());

        // Test unsuccessful not-enum match
        $failed = false;
        try {
            $v->validate($dataFail, $schema, Constraint::CHECK_MODE_EXCEPTIONS);
        } catch (ValidationException $e) {
            $failed = true;
            $this->assertContains('Matched a schema which it should not', $e->getMessage());
        }
        $this->assertTrue($failed);
    }
}
