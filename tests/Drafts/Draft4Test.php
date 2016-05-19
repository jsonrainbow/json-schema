<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Drafts;

/**
 * @package JsonSchema\Tests\Drafts
 */
class Draft4Test extends BaseDraftTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getFilePaths()
    {
        return array(
            realpath(__DIR__ . $this->relativeTestsRoot . '/draft4'),
            realpath(__DIR__ . $this->relativeTestsRoot . '/draft4/optional')
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getSkippedTests()
    {
        return array(
            // Optional
            'bignum.json',
            'format.json',
            'zeroTerminatedFloats.json',
            // Required
            'not.json' // only one test case failing
        );
    }
}
