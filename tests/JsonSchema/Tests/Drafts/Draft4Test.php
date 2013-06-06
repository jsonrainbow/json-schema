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
            'allOf.json',
            'anyOf.json',
            'definitions.json',
            'multipleOf.json',
            'not.json',
            'oneOf.json',
            // Partially Implemented
            'ref.json',
            'refRemote.json',
            // Optional
            'bignum.json',
            'zeroTerminatedFloats.json'
        );
    }

}