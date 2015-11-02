<?php

namespace JsonSchema\Tests\Drafts;

class Draft4Test extends BaseDraftTestCase
{
    protected function getFilePaths()
    {
        return array(
            realpath(__DIR__ . $this->relativeTestsRoot . '/draft4'),
            realpath(__DIR__ . $this->relativeTestsRoot . '/draft4/optional')
        );
    }

    protected function getSkippedTests()
    {
        return array(
            // Not Yet Implemented
            'definitions.json',
            // Partially Implemented
            'refRemote.json',
            // Optional
            'bignum.json',
            'zeroTerminatedFloats.json'
        );
    }

}
