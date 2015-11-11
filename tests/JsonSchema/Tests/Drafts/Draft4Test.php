<?php

namespace JsonSchema\Tests\Drafts;

class Draft4Test extends DraftTestCase
{
    protected function getDirectories()
    {
        return array('draft4', 'draft4/optional');
    }

    protected function getBlackList()
    {
        return array(
            // Not Yet Implemented
            'definitions.json',
            // Partially Implemented
            'ref.json',
            'refRemote.json',
            // Optional
            'bignum.json'
        );
    }
}
