<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints\Drafts\Draft2019;

use JsonSchema\Constraints\Constraint;
use JsonSchema\DraftIdentifiers;
use JsonSchema\Tests\Constraints\BaseTestCase;

class UnevaluatedItemsTest extends BaseTestCase
{
    /** @var string */
    protected $schemaSpec = DraftIdentifiers::DRAFT_2019_09;

    public function getInvalidTests(): \Generator
    {
        yield 'an item not evaluated by items is not allowed' => [
            '["foo",42]',
            <<<'JSON'
            {
                "$schema":"https://json-schema.org/draft/2019-09/schema",
                "type":"array",
                "items":[{"type":"string"}],
                "unevaluatedItems":false
            }
JSON
            ,
            Constraint::CHECK_MODE_STRICT,
        ];

        yield 'an item not evaluated by items or anyOf is not allowed' => [
            '["foo","bar",42]',
            <<<'JSON'
            {
                "$schema":"https://json-schema.org/draft/2019-09/schema",
                "type":"array",
                "items":[{"const":"foo"}],
                "anyOf":[{"items":[true,{"const":"bar"}]}],
                "unevaluatedItems":false
            }
JSON
            ,
            Constraint::CHECK_MODE_STRICT,
        ];

        yield 'an unevaluated item must match the unevaluatedItems schema' => [
            '["foo",42]',
            <<<'JSON'
            {
                "$schema":"https://json-schema.org/draft/2019-09/schema",
                "type":"array",
                "items":[{"type":"string"}],
                "unevaluatedItems":{"type":"string"}
            }
JSON
            ,
            Constraint::CHECK_MODE_STRICT,
        ];

        yield 'an item evaluated only by a failing anyOf branch is not allowed' => [
            '[1]',
            <<<'JSON'
            {
                "$schema":"https://json-schema.org/draft/2019-09/schema",
                "type":"array",
                "unevaluatedItems":false,
                "anyOf":[
                    {"unevaluatedItems":{"type":"string"}},
                    true
                ]
            }
JSON
            ,
            Constraint::CHECK_MODE_STRICT,
        ];
    }

    public function getValidTests(): \Generator
    {
        yield 'items evaluated by items, allOf and then are allowed' => [
            '["foo","bar","then"]',
            <<<'JSON'
            {
                "$schema":"https://json-schema.org/draft/2019-09/schema",
                "type":"array",
                "items":[{"const":"foo"}],
                "allOf":[{"items":[true,{"const":"bar"}]}],
                "if":{"items":[true,true]},
                "then":{"items":[true,true,{"const":"then"}]},
                "unevaluatedItems":false
            }
JSON
            ,
            Constraint::CHECK_MODE_STRICT,
        ];

        yield 'items evaluated by additionalItems are allowed' => [
            '["foo",42,true]',
            <<<'JSON'
            {
                "$schema":"https://json-schema.org/draft/2019-09/schema",
                "type":"array",
                "items":[{"type":"string"}],
                "additionalItems":true,
                "unevaluatedItems":false
            }
JSON
            ,
            Constraint::CHECK_MODE_STRICT,
        ];

        yield 'unevaluated items matching the unevaluatedItems schema are allowed' => [
            '["foo","bar"]',
            <<<'JSON'
            {
                "$schema":"https://json-schema.org/draft/2019-09/schema",
                "type":"array",
                "items":[{"type":"string"}],
                "unevaluatedItems":{"type":"string"}
            }
JSON
            ,
            Constraint::CHECK_MODE_STRICT,
        ];

        yield 'items evaluated by a nested unevaluatedItems are allowed' => [
            '["foo"]',
            <<<'JSON'
            {
                "$schema":"https://json-schema.org/draft/2019-09/schema",
                "type":"array",
                "unevaluatedItems":false,
                "allOf":[{"unevaluatedItems":{"type":"string"}}]
            }
JSON
            ,
            Constraint::CHECK_MODE_STRICT,
        ];
    }
}
