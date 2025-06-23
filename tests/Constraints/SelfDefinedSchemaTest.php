<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints;

use JsonSchema\Exception\InvalidArgumentException;
use JsonSchema\Validator;
use stdClass;

class SelfDefinedSchemaTest extends BaseTestCase
{
    /** @var bool */
    protected $validateSchema = true;

    public function getInvalidTests(): \Generator
    {
        yield [
            '{
                "$schema": {
                    "$schema": "http://json-schema.org/draft-04/schema#",
                    "properties": {
                        "name": {
                            "type": "string"
                        },
                        "age" : {
                            "type": "integer",
                            "maximum": 25
                        }
                    }
                },
                "name" : "John Doe",
                "age" : 30,
                "type" : "object"
            }',
            ''
        ];
    }

    public function getValidTests(): \Generator
    {
        yield [
            '{
                "$schema": {
                    "$schema": "http://json-schema.org/draft-04/schema#",
                    "properties": {
                        "name": {
                            "type": "string"
                        },
                        "age" : {
                            "type": "integer",
                            "maximum": 125
                        }
                    }
                },
                "name" : "John Doe",
                "age" : 30,
                "type" : "object"
            }',
            ''
        ];
    }

    public function testInvalidArgumentException(): void
    {
        $value = new stdClass();
        $schema = null;

        $v = new Validator();

        $this->expectException(InvalidArgumentException::class);

        $v->validate($value, $schema);
    }
}
