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

    public function getInvalidTests(): array
    {
        $tests = parent::getInvalidTests();
        unset(
            $tests['id.json / id inside an enum is not a real identifier / no match on enum or $ref to id'],
            $tests['ref.json / $ref prevents a sibling id from changing the base uri / $ref resolves to /definitions/base_foo, data does not validate'],
            $tests['ref.json / Recursive references between schemas / invalid tree'],
            $tests['ref.json / refs with quote / object with strings is invalid'],
            $tests['ref.json / Location-independent identifier / mismatch'],
            $tests['ref.json / Location-independent identifier with base URI change in subschema / mismatch'],
            $tests['ref.json / empty tokens in $ref json-pointer / non-number is invalid'],
            $tests['ref.json / id must be resolved against nearest parent, not just immediate parent / non-number is invalid'],
            $tests['refRemote.json / Location-independent identifier in remote ref / string is invalid']
        );

        return $tests;
    }

    public function getInvalidForAssocTests(): array
    {
        $tests = parent::getInvalidForAssocTests();
        unset(
            $tests['ref.json / Recursive references between schemas / valid tree'],
            $tests['type.json / object type matches objects / an array is not an object'],
            $tests['type.json / array type matches arrays / an object is not an array']
        );

        return $tests;
    }

    public function getValidTests(): array
    {
        $tests = parent::getValidTests();
        unset(
            $tests['ref.json / $ref prevents a sibling id from changing the base uri / $ref resolves to /definitions/base_foo, data validates'],
            $tests['ref.json / Recursive references between schemas / valid tree'],
            $tests['ref.json / refs with quote / object with numbers is valid'],
            $tests['ref.json / Location-independent identifier / match'],
            $tests['ref.json / Location-independent identifier with base URI change in subschema / match'],
            $tests['ref.json / empty tokens in $ref json-pointer / number is valid'],
            $tests['ref.json / naive replacement of $ref with its destination is not correct / match the enum exactly'],
            $tests['refRemote.json / Location-independent identifier in remote ref / integer is valid']
        );

        return $tests;
    }

    public function getValidForAssocTests(): array
    {
        $tests = parent::getValidForAssocTests();
        unset(
            $tests['minProperties.json / minProperties validation / ignores arrays'],
            $tests['required.json / required properties whose names are Javascript object property names / ignores arrays'],
            $tests['required.json / required validation / ignores arrays'],
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
            'ecmascript-regex.json',
            'format.json',
            'float-overflow.json',
            'zeroTerminatedFloats.json',
            // Required
            'not.json' // only one test case failing
        ];
    }
}
