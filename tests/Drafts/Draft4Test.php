<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Drafts;

class Draft4Test extends BaseDraftTestCase
{
    /** @var bool */
    protected $validateSchema = true;

    /**
     * {@inheritdoc}
     */
    protected function getFilePaths(): array
    {
        return [
            realpath(__DIR__ . self::RELATIVE_TESTS_ROOT . '/draft4'),
            realpath(__DIR__ . self::RELATIVE_TESTS_ROOT . '/draft4/optional')
        ];
    }

    public function getInvalidTests(): \Generator
    {
        $skip = [
            'id.json / id inside an enum is not a real identifier / no match on enum or $ref to id',
            'ref.json / $ref prevents a sibling id from changing the base uri / $ref resolves to /definitions/base_foo, data does not validate',
            'ref.json / Recursive references between schemas / invalid tree',
            'ref.json / refs with quote / object with strings is invalid',
            'ref.json / Location-independent identifier / mismatch',
            'ref.json / Location-independent identifier with base URI change in subschema / mismatch',
            'ref.json / empty tokens in $ref json-pointer / non-number is invalid',
            'ref.json / id must be resolved against nearest parent, not just immediate parent / non-number is invalid',
            'refRemote.json / Location-independent identifier in remote ref / string is invalid',
            'refRemote.json / base URI change - change folder / string is invalid'
        ];

        foreach (parent::getInvalidTests() as $name => $testcase) {
            if (in_array($name, $skip, true)) {
                continue;
            }
            yield $name => $testcase;
        }
    }

    public function getInvalidForAssocTests(): \Generator
    {
        $skip = [
            'ref.json / Recursive references between schemas / valid tree',
            'type.json / object type matches objects / an array is not an object',
            'type.json / array type matches arrays / an object is not an array',
        ];

        foreach (parent::getInvalidForAssocTests() as $name => $testcase) {
            if (in_array($name, $skip, true)) {
                continue;
            }
            yield $name => $testcase;
        }
    }

    public function getValidTests(): \Generator
    {
        $skip = [
            'ref.json / $ref prevents a sibling id from changing the base uri / $ref resolves to /definitions/base_foo, data validates',
            'ref.json / Recursive references between schemas / valid tree',
            'ref.json / refs with quote / object with numbers is valid',
            'ref.json / Location-independent identifier / match',
            'ref.json / Location-independent identifier with base URI change in subschema / match',
            'ref.json / empty tokens in $ref json-pointer / number is valid',
            'ref.json / naive replacement of $ref with its destination is not correct / match the enum exactly',
            'ref.json / id must be resolved against nearest parent, not just immediate parent / number is valid',
            'refRemote.json / Location-independent identifier in remote ref / integer is valid',
            'refRemote.json / base URI change - change folder / number is valid',
        ];

        foreach (parent::getValidTests() as $name => $testcase) {
            if (in_array($name, $skip, true)) {
                continue;
            }
            yield $name => $testcase;
        }
    }

    public function getValidForAssocTests(): \Generator
    {
        $skip = [
            'minProperties.json / minProperties validation / ignores arrays',
            'required.json / required properties whose names are Javascript object property names / ignores arrays',
            'required.json / required validation / ignores arrays',
            'type.json / object type matches objects / an array is not an object',
            'type.json / array type matches arrays / an object is not an array',
        ];

        foreach (parent::getValidForAssocTests() as $name => $testcase) {
            if (in_array($name, $skip, true)) {
                continue;
            }
            yield $name => $testcase;
        }
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
