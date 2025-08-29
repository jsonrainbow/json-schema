<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints;

use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\UndefinedConstraint;
use JsonSchema\DraftIdentifiers;

class RequiredPropertyTest extends BaseTestCase
{
    /**
     * Most tests are draft-03 compliant, but some tests are draft-04, or mix draft-03 and
     * draft-04 syntax within the same schema. Unfortunately, draft-03 and draft-04 required
     * definitions are incompatible, so disabling schema validation for these tests.
     *
     * @var string
     * */
    protected $schemaSpec = DraftIdentifiers::DRAFT_3;

    public function testErrorPropertyIsPopulatedForRequiredIfMissingInInput(): void
    {
        $validator = new UndefinedConstraint();
        $document = json_decode('{ "bar": 42 }', false);
        $schema = json_decode(
            '{
                "type": "object",
                "properties": {
                    "foo": {"type": "number"},
                    "bar": {"type": "number"}
                },
                "required": ["foo"]
            }',
            false
        );

        $validator->check($document, $schema);
        $error = $validator->getErrors();
        $this->assertErrorHasExpectedPropertyValue($error, 'foo');
    }

    public function testPathErrorPropertyIsPopulatedForRequiredIfMissingInInput(): void
    {
        $validator = new UndefinedConstraint();
        $document = json_decode('{ "foo": [{"baz": 1.5}] }', false);
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
            }',
            false
        );

        $validator->check($document, $schema);
        $error = $validator->getErrors();
        $this->assertErrorHasExpectedPropertyValue($error, 'foo[0].bar');
    }

    public function testErrorPropertyIsPopulatedForRequiredIfEmptyValueInInput(): void
    {
        $validator = new UndefinedConstraint();
        $document = json_decode('{ "bar": 42, "foo": null }', false);
        $schema = json_decode(
            '{
                "type": "object",
                "properties": {
                    "foo": {"type": "number"},
                    "bar": {"type": "number"}
                },
                "required": ["foo"]
            }',
            false
        );

        $validator->check($document, $schema);
        $error = $validator->getErrors();
        $this->assertErrorHasExpectedPropertyValue($error, 'foo');
    }

    protected function assertErrorHasExpectedPropertyValue($error, $propertyValue): void
    {
        if (!(isset($error[0]) && is_array($error[0]) && isset($error[0]['property']))) {
            $this->fail(
                "Malformed error response. Expected to have subset in form: array(0 => array('property' => <value>)))"
                . ' . Error response was: ' . json_encode($error)
            );
        }
        $this->assertEquals($propertyValue, $error[0]['property']);
    }

    public function getInvalidTests(): \Generator
    {
        yield [
            '{}',
            '{
              "type":"object",
              "properties":{
                "number":{"type":"number","required":true}
              }
            }'
        ];
        yield [
            '{}',
            '{
                "type": "object",
                "properties": {
                    "number": {"type": "number"}
                },
                "required": ["number"]
            }'
        ];
        yield [
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
        ];
        yield [
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
        ];
        yield [
            '{}',
            '{
                "required": ["foo"]
            }'
        ];
        yield [
            '{
            }',
            '{
                "type": "object",
                "properties": {
                    "foo": { "required": true }
                }
            }'
        ];
        yield [
            '{
              "string":{}
            }',
            '{
              "type":"object",
              "properties": {
                "string":{"type":"string", "required": true}
              }
            }'
        ];
        yield [
            '{
              "number":{}
            }',
            '{
              "type":"object",
              "properties": {
                "number":{"type":"number", "required": true}
              }
            }'
        ];
        yield [
            '{
              "integer":{}
            }',
            '{
              "type":"object",
              "properties": {
                "integer":{"type":"integer", "required": true}
              }
            }'
        ];
        yield [
            '{
                "boolean":{}
            }',
            '{
              "type":"object",
              "properties": {
                "boolean":{"type":"boolean", "required": true}
              }
            }'
        ];
        yield [
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
        ];
        yield [
            '{
              "null":{}
            }',
            '{
              "type":"object",
              "properties": {
                "null":{"type":"null", "required": true}
              }
            }'
        ];
        yield [
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
        ];
        yield [
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
        ];
    }

    public function getValidTests(): \Generator
    {
        yield [
            '{
              "number": 1.4
            }',
            '{
              "type":"object",
              "properties":{
                "number":{"type":"number","required":true}
              }
            }'
        ];
        yield [
            '{}',
            '{
              "type":"object",
              "properties":{
                "number":{"type":"number"}
              }
            }'
        ];
        yield [
            '{}',
            '{
              "type":"object",
              "properties":{
                "number":{"type":"number","required":false}
              }
            }'
        ];
        yield [
            '{
              "number": 0
            }',
            '{
              "type":"object",
              "properties":{
                "number":{"type":"integer","required":true}
              }
            }'
        ];
        yield [
            '{
              "is_active": false
            }',
            '{
              "type":"object",
              "properties":{
                "is_active":{"type":"boolean","required":true}
              }
            }'
        ];
        yield [
            '{
              "status": null
            }',
            '{
              "type":"object",
              "properties":{
                "status":{"type":"null","required":true}
              }
            }'
        ];
        yield [
            '{
              "users": []
            }',
            '{
              "type":"object",
              "properties":{
                "users":{"type":"array","required":true}
              }
            }'
        ];
        yield [
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
        ];
        yield [
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
        ];
        yield [
            '{
                "foo": {}
            }',
            '{
                "type": "object",
                "properties": {
                    "foo": { "required": true }
                }
            }'
        ];
        yield [
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
        ];
        yield [
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
        ];
    }
}
