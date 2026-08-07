<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints;

use JsonSchema\DraftIdentifiers;
use JsonSchema\Constraints\Constraint;

class UnevaluatedPropertiesTest extends BaseTestCase
{
    protected $schemaSpec = DraftIdentifiers::DRAFT_2019_09;

    public function getInvalidTests(): \Generator
    {
        yield [
            '{"hello":"world","world":"hello","unexpected":true}',
            '{
                "$schema":"' . DraftIdentifiers::DRAFT_2019_09 . '",
                "type":"object",
                "unevaluatedProperties":false,
                "allOf":[
                    {"properties":{"hello":{"type":"string"}},"required":["hello"]},
                    {"properties":{"world":{"type":"string"}},"required":["world"]}
                ]
            }',
            Constraint::CHECK_MODE_STRICT,
        ];
    }

    public function getValidTests(): \Generator
    {
        yield [
            '{"hello":"world","world":"hello"}',
            '{
                "$schema":"' . DraftIdentifiers::DRAFT_2019_09 . '",
                "type":"object",
                "unevaluatedProperties":false,
                "allOf":[
                    {"properties":{"hello":{"type":"string"}},"required":["hello"]},
                    {"properties":{"world":{"type":"string"}},"required":["world"]}
                ]
            }',
            Constraint::CHECK_MODE_STRICT,
        ];
    }
}
