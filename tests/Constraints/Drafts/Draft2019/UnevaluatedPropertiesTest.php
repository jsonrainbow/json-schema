MethodException: 
Line |
   2 |  … tedPropertiesTest.php'; $s=$s.Replace([char]13+[char]10,[char]10); $s
     |                            ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     | Cannot convert argument "oldChar", with value: "
", for "Replace" to type "System.Char": "Cannot convert value "
" to type "System.Char". Error: "String must be exactly one character long.""
<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints\Drafts\Draft2019;

use JsonSchema\Constraints\Constraint;
use JsonSchema\DraftIdentifiers;
use JsonSchema\Tests\Constraints\BaseTestCase;

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

        yield [
            '{"foo":"foo","bar":"bar","baz":"not-baz"}',
            '{
                "$schema":"' . DraftIdentifiers::DRAFT_2019_09 . '",
                "type":"object",
                "unevaluatedProperties":false,
                "anyOf":[
                    {"properties":{"foo":{"const":"foo"}}},
                    {"properties":{"bar":{"const":"bar"}}}
                ]
            }',
            Constraint::CHECK_MODE_STRICT,
        ];

        yield [
            '{"kind":"a","value":"ok","extra":true}',
            '{
                "$schema":"' . DraftIdentifiers::DRAFT_2019_09 . '",
                "type":"object",
                "unevaluatedProperties":false,
                "if":{"properties":{"kind":{"const":"a"}}},
                "then":{"properties":{"value":{"type":"string"}}}
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

        yield [
            '{"foo":"ok"}',
            '{
                "$schema":"' . DraftIdentifiers::DRAFT_2019_09 . '",
                "type":"object",
                "unevaluatedProperties":false,
                "additionalProperties":{"type":"string"}
            }',
            Constraint::CHECK_MODE_STRICT,
        ];

        yield [
            '{"foo":"foo","bar":"bar"}',
            '{
                "$schema":"' . DraftIdentifiers::DRAFT_2019_09 . '",
                "type":"object",
                "unevaluatedProperties":false,
                "anyOf":[
                    {"properties":{"foo":{"const":"foo"}}},
                    {"properties":{"bar":{"const":"bar"}}}
                ]
            }',
            Constraint::CHECK_MODE_STRICT,
        ];

        yield [
            '{"kind":"a","value":"ok"}',
            '{
                "$schema":"' . DraftIdentifiers::DRAFT_2019_09 . '",
                "type":"object",
                "unevaluatedProperties":false,
                "if":{"properties":{"kind":{"const":"a"}}},
                "then":{"properties":{"value":{"type":"string"}}}
            }',
            Constraint::CHECK_MODE_STRICT,
        ];

        yield [
            '{"foo":"foo","bar":"bar"}',
            '{
                "$schema":"' . DraftIdentifiers::DRAFT_2019_09 . '",
                "type":"object",
                "properties":{"foo":{"type":"string"}},
                "unevaluatedProperties":false,
                "dependentSchemas":{"foo":{"properties":{"bar":{"type":"string"}}}}
            }',
            Constraint::CHECK_MODE_STRICT,
        ];
    }
}

