<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Drafts;

use JsonSchema\Constraints\Factory;
use JsonSchema\DraftIdentifiers;
use JsonSchema\SchemaStorage;
use JsonSchema\Validator;

class Draft3Test extends BaseDraftTestCase
{
    /** @var string */
    protected $schemaSpec = DraftIdentifiers::DRAFT_3;
    /** @var bool */
    protected $validateSchema = true;

    /**
     * This test is a copy of https://github.com/json-schema-org/JSON-Schema-Test-Suite/blob/main/tests/draft3/ref.json#L203-L225
     *
     * @todo cleanup when #821 gets merged
     *
     * @param mixed $data
     * @dataProvider refPreventsASiblingIdFromChangingTheBaseUriProvider
     */
    public function testRefPreventsASiblingIdFromChangingTheBaseUriProvider($data, bool $expectedResult, string $documentUri): void
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
        $schemaStorage->addSchema($documentUri, $schema);
        $validator = new Validator(new Factory($schemaStorage));
        $validator->validate($data, $schema);

        self::assertEquals($expectedResult, $validator->isValid());
    }

    public function refPreventsASiblingIdFromChangingTheBaseUriProvider(): \Generator
    {
        // the document uri the schema is registered under must not affect how the nested,
        // relative id is resolved; 'internal://mySchema' is what the Bowtie harness uses
        $documentUris = [
            'registered under its own id' => 'http://localhost:1234/sibling_id/base/',
            'registered under an internal uri' => SchemaStorage::INTERNAL_PROVIDED_SCHEMA_URI,
            'registered under an internal uri without a path' => 'internal://mySchema',
        ];

        foreach ($documentUris as $uriDescription => $documentUri) {
            yield sprintf('$ref resolves to /definitions/base_foo, data does not validate, %s', $uriDescription) => [
                'data' => 'a',
                'valid' => false,
                'documentUri' => $documentUri,
            ];
            yield sprintf('$ref resolves to /definitions/base_foo, data validates, %s', $uriDescription) => [
                'data' => 1,
                'valid' => true,
                'documentUri' => $documentUri,
            ];
        }
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

    public function getInvalidTests(): \Generator
    {
        foreach (parent::getInvalidTests() as $name => $testcase) {
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

    public function getValidForAssocTests(): \Generator
    {
        foreach (parent::getValidForAssocTests() as $name => $testcase) {
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
            'zeroTerminatedFloats.json'
        ];
    }
}
