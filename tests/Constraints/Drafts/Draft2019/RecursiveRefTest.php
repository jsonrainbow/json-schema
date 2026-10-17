<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Constraints\Drafts\Draft2019;

use JsonSchema\Constraints\Constraint;
use JsonSchema\DraftIdentifiers;
use JsonSchema\Tests\Constraints\BaseTestCase;

class RecursiveRefTest extends BaseTestCase
{
    protected $schemaSpec = DraftIdentifiers::DRAFT_2019_09;

    public function getInvalidTests(): \Generator
    {
        yield 'without $recursiveAnchor it works like $ref' => [
            '{"foo":{"bar":false}}',
            '{
                "$schema":"' . DraftIdentifiers::DRAFT_2019_09 . '",
                "properties":{"foo":{"$recursiveRef":"#"}},
                "additionalProperties":false
            }',
            Constraint::CHECK_MODE_STRICT,
        ];

        yield 'recursion uses the outermost schema with $recursiveAnchor' => [
            '{"name":"root","children":[{"children":[]}]}',
            $this->getExtendedTreeSchema(true, true),
            Constraint::CHECK_MODE_STRICT,
        ];
    }

    public function getValidTests(): \Generator
    {
        yield 'without $recursiveAnchor it works like $ref' => [
            '{"foo":{"foo":false}}',
            '{
                "$schema":"' . DraftIdentifiers::DRAFT_2019_09 . '",
                "properties":{"foo":{"$recursiveRef":"#"}},
                "additionalProperties":false
            }',
            Constraint::CHECK_MODE_STRICT,
        ];

        yield 'recursion uses the outermost schema with $recursiveAnchor' => [
            '{"name":"root","children":[{"name":"child","children":[]}]}',
            $this->getExtendedTreeSchema(true, true),
            Constraint::CHECK_MODE_STRICT,
        ];

        yield 'recursion stays in the initial target without $recursiveAnchor' => [
            '{"name":"root","children":[{"children":[]}]}',
            $this->getExtendedTreeSchema(true, false),
            Constraint::CHECK_MODE_STRICT,
        ];

        yield 'recursion stays in the initial target when the outer schema has no $recursiveAnchor' => [
            '{"name":"root","children":[{"children":[]}]}',
            $this->getExtendedTreeSchema(false, true),
            Constraint::CHECK_MODE_STRICT,
        ];
    }

    /**
     * A tree whose children are evaluated through $recursiveRef, extended by an outer schema
     * which requires a name.
     */
    private function getExtendedTreeSchema(bool $outerAnchor, bool $treeAnchor): string
    {
        return '{
            "$schema":"' . DraftIdentifiers::DRAFT_2019_09 . '",
            "$id":"http://example.com/recursive-ref/strict-tree.json",
            "$recursiveAnchor":' . json_encode($outerAnchor) . ',
            "allOf":[{"$ref":"tree.json"}],
            "required":["name"],
            "$defs":{
                "tree":{
                    "$id":"tree.json",
                    "$recursiveAnchor":' . json_encode($treeAnchor) . ',
                    "type":"object",
                    "properties":{
                        "children":{"type":"array","items":{"$recursiveRef":"#"}}
                    }
                }
            }
        }';
    }
}
