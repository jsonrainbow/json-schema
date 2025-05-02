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
