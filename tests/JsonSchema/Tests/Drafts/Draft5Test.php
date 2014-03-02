<?php

namespace JsonSchema\Tests\Drafts;

class Draft5Test extends BaseDraftTestCase
{
    protected function getFilePaths()
    {
        return array(
            realpath(__DIR__ . $this->relativeTestsRoot . '/draft5'),
            realpath(__DIR__ . $this->relativeTestsRoot . '/draft5/optional')
        );
    }

    protected function getSkippedTests()
    {
        return array(
            // Not Yet Implemented
            'definitions.json',
            // Partially Implemented
            'ref.json',
            'refRemote.json',
            // Optional
            'bignum.json',
            'zeroTerminatedFloats.json'
        );
    }

}