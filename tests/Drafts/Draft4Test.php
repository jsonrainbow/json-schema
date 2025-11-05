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
            'ref.json / Location-independent identifier / mismatch',
            'ref.json / Location-independent identifier with base URI change in subschema / mismatch',
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
            'ref.json / Location-independent identifier / match',
            'ref.json / Location-independent identifier with base URI change in subschema / match',
            'ref.json / id must be resolved against nearest parent, not just immediate parent / number is valid',
            'refRemote.json / Location-independent identifier in remote ref / integer is valid',
            'refRemote.json / base URI change - change folder / number is valid',
        ];

        if ($this->is32Bit()) {
            $skip[] = 'multipleOf.json / small multiple of large integer / any integer is a multiple of 1e-8'; // Test case contains a number which doesn't fit in 32 bits
        }

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
