<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints;

use JsonSchema\Constraints\Constraint;

class UndefinedConstraintTest extends BaseTestCase
{
    /**
     * @return array{}
     */
    public function getInvalidTests(): array
    {
        return [];
    }

    /**
     * @return array<string, array{input: string, schema: string, checkMode?: int}>
     */
    public function getValidTests(): array
    {
        return [
            'oneOf with type coercion should not affect value passed to each sub schema (#790)' => [
                'input' => <<<JSON
                    {
                        "id": "LOC1",
                        "related_locations": [
                            {
                                "latitude": "51.047598",
                                "longitude": "3.729943"
                            }
                        ]
                    }
JSON
                ,
                'schema' => <<<JSON
                    {
                        "title": "Location",
                        "type": "object",
                        "properties": {
                            "id": {
                                "type": "string"
                            },
                            "related_locations": {
                                "oneOf": [
                                    {
                                        "type": "null"
                                    },
                                    {
                                        "type": "array",
                                        "items": {
                                            "type": "object",
                                            "properties": {
                                                "latitude": {
                                                    "type": "string"
                                                },
                                                "longitude": {
                                                    "type": "string"
                                                }
                                            }
                                        }
                                    }
                                ]
                            }
                        }
                    }
JSON
                ,
                'checkMode' => Constraint::CHECK_MODE_COERCE_TYPES
            ]
        ];
    }
}
