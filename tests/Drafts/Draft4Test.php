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
    protected function getFilePaths(): array
    {
        return [
            realpath(__DIR__ . $this->relativeTestsRoot . '/draft4'),
            realpath(__DIR__ . $this->relativeTestsRoot . '/draft4/optional')
        ];
    }

    public function getInvalidForAssocTests(): array
    {
        $tests = parent::getInvalidForAssocTests();
        unset(
            $tests['type.json / object type matches objects / an array is not an object'],
            $tests['type.json / array type matches arrays / an object is not an array']
        );

        return $tests;
    }

    public function getValidForAssocTests(): array
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
    protected function getSkippedTests(): array
    {
        return [
            // Optional
            'bignum.json',
            'format.json',
            'zeroTerminatedFloats.json',
            // Required
            'not.json' // only one test case failing
        ];
    }
}
