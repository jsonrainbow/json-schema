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
            $tests['type.json / array type matches arrays / an object is not an array'],
            // Arrays must be ignored and in assoc case, these data are arrays and not objects.
            $tests['maxProperties.json / maxProperties validation / too long is invalid'],
            $tests['minProperties.json / minProperties validation / too short is invalid']
        );

        return $tests;
    }

    public function getValidForAssocTests()
    {
        $tests = parent::getValidForAssocTests();
        unset(
            $tests['type.json / object type matches objects / an array is not an object'],
            $tests['type.json / array type matches arrays / an object is not an array'],
            // Arrays must be ignored and in assoc case, these data are arrays and not objects.
            $tests['required.json / required validation / ignores arrays']
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
            'ecmascript-regex.json',
            'format.json',
            'zeroTerminatedFloats.json',
            // Required
            'not.json', // only one test case failing
            'ref.json', // external references can not be found (localhost:1234)
        );
    }
}
