<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class MinLengthMaxLengthMultiByteTest extends ConstraintsDataTest
{
    protected function setUp()
    {
        if (!extension_loaded('mbstring')) {
            $this->markTestSkipped('mbstring extension is not available');
        }
    }

    protected function getTests()
    {
        $tests = array();

        return $this->loadTest(__DIR__ . '/tests/minLengthMaxLengthMultiByte.json', $tests);
    }
}
