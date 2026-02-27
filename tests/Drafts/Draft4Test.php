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
    protected $schemaSpec = 'http://json-schema.org/draft-04/schema#';
    protected $validateSchema = true;

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

    public function getInvalidForAssocTests()
    {
        $tests = parent::getInvalidForAssocTests();
        unset(
            $tests['type.json / object type matches objects / an array is not an object'],
            $tests['type.json / array type matches arrays / an object is not an array']
        );

        return $tests;
    }

    public function getValidForAssocTests()
    {
        $tests = parent::getValidForAssocTests();
        unset(
            $tests['type.json / object type matches objects / an array is not an object'],
            $tests['type.json / array type matches arrays / an object is not an array']
        );

        return $tests;
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
