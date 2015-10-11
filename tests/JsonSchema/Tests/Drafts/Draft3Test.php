<?php

namespace JsonSchema\Tests\Drafts;

class Draft3Test extends DraftTestCase
{
    protected function getDirectories()
    {
        return array('/draft3', '/draft3/optional');
    }

    protected function getBlackList()
    {
        return array(
            'ref.json',
            'refRemote.json',
            'bignum.json',
            'jsregex.json',
            'zeroTerminatedFloats.json'
        );
    }
}
