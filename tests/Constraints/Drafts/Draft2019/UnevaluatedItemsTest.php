<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints\Drafts\Draft2019;

use JsonSchema\Constraints\Constraint;
use JsonSchema\DraftIdentifiers;
use JsonSchema\Tests\Constraints\BaseTestCase;

class UnevaluatedItemsTest extends BaseTestCase
{
    protected $schemaSpec = DraftIdentifiers::DRAFT_2019_09;

    public function getInvalidTests(): \Generator
    {
        yield [
            '["foo",42]',
            '{
                "$schema":"' . DraftIdentifiers::DRAFT_2019_09 . '",
                "type":"array",
                "items":[{"type":"string"}],
                "unevaluatedItems":false
            }',
            Constraint::CHECK_MODE_STRICT,
        ];

        yield [
            '["foo","bar",42]',
            '{
                "$schema":"' . DraftIdentifiers::DRAFT_2019_09 . '",
                "type":"array",
                "items":[{"const":"foo"}],
                "anyOf":[{"items":[true,{"const":"bar"}]}],
                "unevaluatedItems":false
            }',
            Constraint::CHECK_MODE_STRICT,
        ];

        yield [
            '["foo",42]',
            '{
                "$schema":"' . DraftIdentifiers::DRAFT_2019_09 . '",
                "type":"array",
                "items":[{"type":"string"}],
                "unevaluatedItems":{"type":"string"}
            }',
            Constraint::CHECK_MODE_STRICT,
        ];
    }

    public function getValidTests(): \Generator
    {
        yield [
            '["foo","bar","then"]',
            '{
                "$schema":"' . DraftIdentifiers::DRAFT_2019_09 . '",
                "type":"array",
                "items":[{"const":"foo"}],
                "allOf":[{"items":[true,{"const":"bar"}]}],
                "if":{"items":[true,true]},
                "then":{"items":[true,true,{"const":"then"}]},
                "unevaluatedItems":false
            }',
            Constraint::CHECK_MODE_STRICT,
        ];

        yield [
            '["foo",42,true]',
            '{
                "$schema":"' . DraftIdentifiers::DRAFT_2019_09 . '",
                "type":"array",
                "items":[{"type":"string"}],
                "additionalItems":true,
                "unevaluatedItems":false
            }',
            Constraint::CHECK_MODE_STRICT,
        ];

        yield [
            '["foo","bar"]',
            '{
                "$schema":"' . DraftIdentifiers::DRAFT_2019_09 . '",
                "type":"array",
                "items":[{"type":"string"}],
                "unevaluatedItems":{"type":"string"}
            }',
            Constraint::CHECK_MODE_STRICT,
        ];
    }
}
