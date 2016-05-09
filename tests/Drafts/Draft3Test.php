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
class Draft3Test extends BaseDraftTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getFilePaths()
    {
        return array(
            realpath(__DIR__ . $this->relativeTestsRoot . '/draft3'),
            realpath(__DIR__ . $this->relativeTestsRoot . '/draft3/optional')
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
            'jsregex.json',
            'zeroTerminatedFloats.json'
        );
    }
}
