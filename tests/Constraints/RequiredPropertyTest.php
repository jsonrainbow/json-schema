<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\UndefinedConstraint;

class RequiredPropertyTest extends BaseTestCase
{
    // Most tests are draft-03 compliant, but some tests are draft-04, or mix draft-03 and
    // draft-04 syntax within the same schema. Unfortunately, draft-03 and draft-04 required
    // definitions are incompatible, so disabling schema validation for these tests.
    protected $schemaSpec = 'http://json-schema.org/draft-03/schema#';
    protected $validateSchema = false;

    public function testErrorPropertyIsPopulatedForRequiredIfMissingInInput()
    {
        $validator = new UndefinedConstraint();
        $document = json_decode(
            '{
            "bar": 42
        }'
        );
        $schema = json_decode(
            '{
            "type": "object",
            "properties": {
                "foo": {"type": "number"},
                "bar": {"type": "number"}
            },
            "required": ["foo"]
        }'
        );

        $validator->check($document, $schema);
        $error = $validator->getErrors();
        $this->assertErrorHasExpectedPropertyValue($error, 'foo');
    }

    public function testPathErrorPropertyIsPopulatedForRequiredIfMissingInInput()
    {
        $validator = new UndefinedConstraint();
        $document = json_decode(
            '{
                "foo": [{"baz": 1.5}]
            }'
        );
        $schema = json_decode(
            '{
                "type": "object",
                "properties": {
                    "foo": {
                        "type": "array",
                        "items": {
                            "type": "object",
                            "properties": {
                                "bar": {"type": "number"},
                                "baz": {"type": "number"}
                            },
                            "required": ["bar"]
                        }
                    }
                },
                "required": ["foo"]
            }'
        );

        $validator->check($document, $schema);
        $error = $validator->getErrors();
        $this->assertErrorHasExpectedPropertyValue($error, 'foo[0].bar');
    }

    public function testErrorPropertyIsPopulatedForRequiredIfEmptyValueInInput()
    {
        $validator = new UndefinedConstraint();
        $document = json_decode(
            '{
            "bar": 42,
            "foo": null
        }'
        );
        $schema = json_decode(
            '{
            "type": "object",
            "properties": {
                "foo": {"type": "number"},
                "bar": {"type": "number"}
            },
            "required": ["foo"]
        }'
        );

        $validator->check($document, $schema);
        $error = $validator->getErrors();
        $this->assertErrorHasExpectedPropertyValue($error, 'foo');
    }

    protected function assertErrorHasExpectedPropertyValue($error, $propertyValue)
    {
        if (!(isset($error[0]) && is_array($error[0]) && isset($error[0]['property']))) {
            $this->fail(
                "Malformed error response. Expected to have subset in form: array(0 => array('property' => <value>)))"
                . ' . Error response was: ' . json_encode($error)
            );
        }
        $this->assertEquals($propertyValue, $error[0]['property']);
    }

    public function getInvalidTests()
    {
        return [
            [
                '{}',
                '{
                  "type":"object",
                  "properties":{
                    "number":{"type":"number","required":true}
                  }
                }'
            ],
            [
                '{}',
                '{
                    "type": "object",
                    "properties": {
                        "number": {"type": "number"}
                    },
                    "required": ["number"]
                }'
            ],
            [
                '{
                    "foo": {}
                }',
                '{
                    "type": "object",
                    "properties": {
                        "foo": {
                            "type": "object",
                            "properties": {
                                "bar": {"type": "number"}
                            },
                            "required": ["bar"]
                        }
                    }
                }'
            ],
            [
                '{
                    "bar": 1.4
                 }',
                '{
                    "type": "object",
                    "properties": {
                        "foo": {"type": "string", "required": true},
                        "bar": {"type": "number"}
                    },
                    "required": ["bar"]
                }'
            ],
            [
                '{}',
                '{
                    "required": ["foo"]
                }'
            ],
            [
                '{
                }',
                '{
                    "type": "object",
                    "properties": {
                        "foo": { "required": true }
                    }
                }'
            ],
            [
                '{
                  "string":{}
                }',
                '{
                  "type":"object",
                  "properties": {
                    "string":{"type":"string", "required": true}
                  }
                }'
            ],
            [
                '{
                  "number":{}
                }',
                '{
                  "type":"object",
                  "properties": {
                    "number":{"type":"number", "required": true}
                  }
                }'
            ],
            [
                '{
                  "integer":{}
                }',
                '{
                  "type":"object",
                  "properties": {
                    "integer":{"type":"integer", "required": true}
                  }
                }'
            ],
            [
                '{
                  "boolean":{}
                }',
                '{
                  "type":"object",
                  "properties": {
                    "boolean":{"type":"boolean", "required": true}
                  }
                }'
            ],
            [
                '{
                  "array":{}
                }',
                '{
                  "type":"object",
                  "properties": {
                    "array":{"type":"array", "required": true}
                  }
                }',
                Constraint::CHECK_MODE_NORMAL
            ],
            [
                '{
                  "null":{}
                }',
                '{
                  "type":"object",
                  "properties": {
                    "null":{"type":"null", "required": true}
                  }
                }'
            ],
            [
                '{
                  "foo": {"baz": 1.5}
                }',
                '{
                  "type": "object",
                  "properties": {
                    "foo": {
                      "type": "object",
                      "properties": {
                        "bar": {"type": "number"}
                      },
                      "required": ["bar"]
                    }
                  }
                }'
            ],
            [
                '{
                  "foo": {"baz": 1.5}
                }',
                '{
                  "type": "object",
                  "properties": {
                    "foo": {
                      "type": "object",
                      "properties": {
                        "bar": {"type": "number", "required": true}
                      }
                    }
                  }
                }'
            ],
        ];
    }

    public function getValidTests()
    {
        return [
            [
                '{
                  "number": 1.4
                }',
                '{
                  "type":"object",
                  "properties":{
                    "number":{"type":"number","required":true}
                  }
                }'
            ],
            [
                '{}',
                '{
                  "type":"object",
                  "properties":{
                    "number":{"type":"number"}
                  }
                }'
            ],
            [
                '{}',
                '{
                  "type":"object",
                  "properties":{
                    "number":{"type":"number","required":false}
                  }
                }'
            ],
            [
                '{
                  "number": 0
                }',
                '{
                  "type":"object",
                  "properties":{
                    "number":{"type":"integer","required":true}
                  }
                }'
            ],
            [
                '{
                  "is_active": false
                }',
                '{
                  "type":"object",
                  "properties":{
                    "is_active":{"type":"boolean","required":true}
                  }
                }'
            ],
            [
                '{
                  "status": null
                }',
                '{
                  "type":"object",
                  "properties":{
                    "status":{"type":"null","required":true}
                  }
                }'
            ],
            [
                '{
                  "users": []
                }',
                '{
                  "type":"object",
                  "properties":{
                    "users":{"type":"array","required":true}
                  }
                }'
            ],
            [
                '{
                    "foo": "foo",
                    "bar": 1.4
                 }',
                '{
                    "type": "object",
                    "properties": {
                        "foo": {"type": "string", "required": true},
                        "bar": {"type": "number"}
                    },
                    "required": ["bar"]
                }'
            ],
            [
                '{
                    "foo": {"bar": 1.5}
                }',
                '{
                    "type": "object",
                    "properties": {
                        "foo": {
                            "type": "object",
                            "properties": {
                                "bar": {"type": "number"}
                            },
                            "required": ["bar"]
                        }
                    },
                    "required": ["foo"]
                }'
            ],
            [
                '{
                    "foo": {}
                }',
                '{
                    "type": "object",
                    "properties": {
                        "foo": { "required": true }
                    }
                }'
            ],
            [
                '{
                  "boo": {"bar": 1.5}
                }',
                '{
                  "type": "object",
                  "properties": {
                    "foo": {
                      "type": "object",
                      "properties": {
                        "bar": {"type": "number"}
                      },
                      "required": ["bar"]
                    }
                  }
                }'
            ],
            [
                '{
                  "boo": {"bar": 1.5}
                }',
                '{
                  "type": "object",
                  "properties": {
                    "foo": {
                      "type": "object",
                      "properties": {
                        "bar": {"type": "number", "required": true}
                      }
                    }
                  }
                }'
            ],
        ];
    }
}
