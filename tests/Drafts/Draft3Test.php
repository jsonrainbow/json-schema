<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Drafts;

use JsonSchema\Constraints\Factory;
use JsonSchema\SchemaStorage;
use JsonSchema\Validator;

/**
 * @package JsonSchema\Tests\Drafts
 */
class Draft3Test extends BaseDraftTestCase
{
    protected $schemaSpec = 'http://json-schema.org/draft-03/schema#';
    protected $validateSchema = true;

    /**
     * This test is a copy of https://github.com/json-schema-org/JSON-Schema-Test-Suite/blob/main/tests/draft3/ref.json#L203-L225
     *
     * @todo cleanup when #821 gets merged
     *
     * @param mixed $data
     * @dataProvider refPreventsASiblingIdFromChangingTheBaseUriProvider
     */
    public function testRefPreventsASiblingIdFromChangingTheBaseUriProvider($data, bool $expectedResult): void
    {
        $schema = json_decode(<<<'JSON'
            {
                "id": "http://localhost:1234/sibling_id/base/",
                "definitions": {
                    "foo": {
                        "id": "http://localhost:1234/sibling_id/foo.json",
                        "type": "string"
                    },
                    "base_foo": {
                        "$comment": "this canonical uri is http://localhost:1234/sibling_id/base/foo.json",
                        "id": "foo.json",
                        "type": "number"
                    }
                },
                "extends": [
                    {
                        "$comment": "$ref resolves to http://localhost:1234/sibling_id/base/foo.json, not http://localhost:1234/sibling_id/foo.json",
                        "id": "http://localhost:1234/sibling_id/",
                        "$ref": "foo.json"
                    }
                ]
            }
JSON
        , false);

        $schemaStorage = new SchemaStorage();
        $schemaStorage->addSchema(property_exists($schema, 'id') ? $schema->id : 'internal://mySchema', $schema);
        $validator = new Validator(new Factory($schemaStorage));
        $validator->validate($data, $schema);

        self::assertEquals($expectedResult, $validator->isValid());
    }

    public function refPreventsASiblingIdFromChangingTheBaseUriProvider(): \Generator
    {
        yield '$ref resolves to /definitions/base_foo, data does not validate' => ['data' => 'a', 'valid' => false];
        yield '$ref resolves to /definitions/base_foo, data validate' => ['data' => 1, 'valid' => true];
    }

    /**
     * {@inheritdoc}
     */
    protected function getFilePaths(): array
    {
        return [
            realpath(__DIR__ . self::RELATIVE_TESTS_ROOT . '/draft3'),
            realpath(__DIR__ . self::RELATIVE_TESTS_ROOT . '/draft3/optional')
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
            'format.json',
            'jsregex.json',
            'zeroTerminatedFloats.json'
        ];
    }
}
