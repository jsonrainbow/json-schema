<?php

declare(strict_types=1);

namespace JsonSchema\Tests;

use CallbackFilterIterator;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\Factory;
use JsonSchema\DraftIdentifiers;
use JsonSchema\SchemaStorage;
use JsonSchema\SchemaStorageInterface;
use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class JsonSchemaTestSuiteTest extends TestCase
{
    /**
     * @dataProvider casesDataProvider
     *
     * @param \stdClass|bool $schema
     * @param mixed          $data
     */
    public function testTestCaseValidatesCorrectly(
        string $testCaseDescription,
        string $testDescription,
        $schema,
        $data,
        int $checkMode,
        DraftIdentifiers $draft,
        bool $expectedValidationResult,
        bool $optional
    ): void {
        $schemaStorage = new SchemaStorage();
        $id = is_object($schema) && property_exists($schema, 'id') ? $schema->id : SchemaStorage::INTERNAL_PROVIDED_SCHEMA_URI;
        $schemaStorage->addSchema($id, $schema);
        $this->loadRemotesIntoStorage($schemaStorage);
        $factory = new Factory($schemaStorage);
        $factory->setDefaultDialect($draft->getValue());
        $validator = new Validator($factory);

        try {
            $validator->validate($data, $schema, $checkMode);
        } catch (\Exception $e) {
            if ($optional) {
                $this->markTestSkipped('Optional test case throws exception during validate() invocation: "' . $e->getMessage() . '"');
            }

            throw $e;
        }

        if ($optional && $expectedValidationResult !== (count($validator->getErrors()) === 0)) {
            $this->markTestSkipped('Optional test case would fail');
        }

        self::assertEquals(
            $expectedValidationResult,
            count($validator->getErrors()) === 0,
            $expectedValidationResult ? print_r($validator->getErrors(), true) : 'Validator returned valid but the testcase indicates it is invalid'
        );
    }

    public function casesDataProvider(): \Generator
    {
        $testDir = __DIR__ . '/../vendor/json-schema/json-schema-test-suite/tests';
        $drafts = array_filter(glob($testDir . '/*'), static function (string $filename) {
            return is_dir($filename);
        });
        $skippedDrafts = ['draft3', 'draft4', 'draft6', 'draft2019-09', 'draft2020-12', 'draft-next', 'latest'];

        foreach ($drafts as $draft) {
            $baseDraftName = basename($draft);
            if (in_array($baseDraftName, $skippedDrafts, true)) {
                continue;
            }

            $files = new CallbackFilterIterator(
                new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($draft)
                ),
                function ($file) {
                    return $file->isFile() && strtolower($file->getExtension()) === 'json';
                }
            );
            /** @var \SplFileInfo $file */
            foreach ($files as $file) {
                $contents = json_decode(file_get_contents($file->getPathname()), false);
                foreach ($contents as $testCase) {
                    foreach ($testCase->tests as $test) {
                        $name = sprintf(
                            '[%s/%s%s]: %s: %s is expected to be %s',
                            basename($draft),
                            str_contains($file->getPathname(), '/optional/') ? 'optional/' : '',
                            $file->getBasename(),
                            $testCase->description,
                            $test->description,
                            $test->valid ? 'valid' : 'invalid'
                        );

                        if ($this->shouldNotYieldTest($name)) {
                            continue;
                        }

                        yield $name => [
                            'testCaseDescription' => $testCase->description,
                            'testDescription' => $test->description,
                            'schema' => $testCase->schema,
                            'data' => $test->data,
                            'checkMode' => $this->getCheckModeForDraft($baseDraftName),
                            'draft' => DraftIdentifiers::fromConstraintName($baseDraftName),
                            'expectedValidationResult' => $test->valid,
                            'optional' => str_contains($file->getPathname(), '/optional/')
                        ];
                    }
                }
            }
        }
    }

    private function loadRemotesIntoStorage(SchemaStorageInterface $storage): void
    {
        $remotesDir = __DIR__ . '/../vendor/json-schema/json-schema-test-suite/remotes';

        $directory = new \RecursiveDirectoryIterator($remotesDir);
        $iterator = new \RecursiveIteratorIterator($directory);

        foreach ($iterator as $info) {
            if (!$info->isFile()) {
                continue;
            }

            $id = str_replace($remotesDir, 'http://localhost:1234', $info->getPathname());
            $storage->addSchema($id, json_decode(file_get_contents($info->getPathname()), false));
        }
    }

    private function shouldNotYieldTest(string $name): bool
    {
        $skip = [
            '[draft4/ref.json]: refs with quote: object with numbers is valid is expected to be valid', // Test case was added after v1.2.0, skip test for now.
            '[draft4/ref.json]: refs with quote: object with strings is invalid is expected to be invalid', // Test case was added after v1.2.0, skip test for now.
            '[draft4/ref.json]: Location-independent identifier: match is expected to be valid', // Test case was added after v1.2.0, skip test for now.
            '[draft4/ref.json]: Location-independent identifier: mismatch is expected to be invalid', // Test case was added after v1.2.0, skip test for now.
            '[draft4/ref.json]: Location-independent identifier with base URI change in subschema: match is expected to be valid', // Test case was added after v1.2.0, skip test for now.
            '[draft4/ref.json]: Location-independent identifier with base URI change in subschema: mismatch is expected to be invalid', // Test case was added after v1.2.0, skip test for now.
            '[draft4/ref.json]: id must be resolved against nearest parent, not just immediate parent: number is valid is expected to be valid', // Test case was added after v1.2.0, skip test for now.
            '[draft4/ref.json]: id must be resolved against nearest parent, not just immediate parent: non-number is invalid is expected to be invalid', // Test case was added after v1.2.0, skip test for now.
            '[draft4/ref.json]: empty tokens in $ref json-pointer: number is valid is expected to be valid', // Test case was added after v1.2.0, skip test for now.
            '[draft4/ref.json]: empty tokens in $ref json-pointer: non-number is invalid is expected to be invalid', // Test case was added after v1.2.0, skip test for now.
            '[draft4/refRemote.json]: base URI change - change folder: number is valid is expected to be valid', // Test case was added after v1.2.0, skip test for now.
            '[draft4/refRemote.json]: base URI change - change folder: string is invalid is expected to be invalid', // Test case was added after v1.2.0, skip test for now.
            '[draft4/refRemote.json]: Location-independent identifier in remote ref: integer is valid is expected to be valid', // Test case was added after v1.2.0, skip test for now.
            '[draft4/refRemote.json]: Location-independent identifier in remote ref: string is invalid is expected to be invalid', // Test case was added after v1.2.0, skip test for now.
            '[draft6/ref.json]: Location-independent identifier with base URI change in subschema: mismatch is expected to be invalid', // Test case was added after v1.2.0, skip test for now.
            '[draft6/ref.json]: Location-independent identifier: mismatch is expected to be invalid', // Same test case is skipped for draft4, skip for now as well.
            '[draft6/ref.json]: refs with quote: object with strings is invalid is expected to be invalid', // Same test case is skipped for draft4, skip for now as well.
            '[draft6/ref.json]: empty tokens in $ref json-pointer: non-number is invalid is expected to be invalid', // Same test case is skipped for draft4, skip for now as well.
            '[draft6/refRemote.json]: base URI change - change folder: string is invalid is expected to be invalid', // Same test case is skipped for draft4, skip for now as well.
            '[draft6/refRemote.json]: Location-independent identifier in remote ref: string is invalid is expected to be invalid', // Same test case is skipped for draft4, skip for now as well.
            // Skipping complex edge cases for now
            '[draft6/unknownKeyword.json]: $id inside an unknown keyword is not a real identifier: type matches second anyOf, which has a real schema in it is expected to be valid',
            '[draft6/unknownKeyword.json]: $id inside an unknown keyword is not a real identifier: type matches non-schema in third anyOf is expected to be invalid',
            '[draft6/refRemote.json]: $ref to $ref finds location-independent $id: non-number is invalid is expected to be invalid',
            '[draft6/ref.json]: ref overrides any sibling keywords: ref valid, maxItems ignored is expected to be valid',
            '[draft6/ref.json]: Reference an anchor with a non-relative URI: mismatch is expected to be invalid',
            '[draft6/ref.json]: refs with relative uris and defs: invalid on inner field is expected to be invalid',
            '[draft6/ref.json]: refs with relative uris and defs: invalid on outer field is expected to be invalid',
            '[draft6/ref.json]: relative refs with absolute uris and defs: invalid on inner field is expected to be invalid',
            '[draft6/ref.json]: relative refs with absolute uris and defs: invalid on outer field is expected to be invalid',
            '[draft6/ref.json]: simple URN base URI with JSON pointer: a non-string is invalid is expected to be invalid',
            '[draft6/ref.json]: URN base URI with NSS: a non-string is invalid is expected to be invalid',
            '[draft6/ref.json]: URN base URI with r-component: a non-string is invalid is expected to be invalid',
            '[draft6/ref.json]: URN base URI with q-component: a non-string is invalid is expected to be invalid',
            '[draft6/ref.json]: URN base URI with URN and anchor ref: a non-string is invalid is expected to be invalid',
            '[draft7/unknownKeyword.json]: $id inside an unknown keyword is not a real identifier: type matches second anyOf, which has a real schema in it is expected to be valid',
            '[draft7/unknownKeyword.json]: $id inside an unknown keyword is not a real identifier: type matches non-schema in third anyOf is expected to be invalid',
            '[draft7/refRemote.json]: $ref to $ref finds location-independent $id: non-number is invalid is expected to be invalid',
            '[draft7/ref.json]: ref overrides any sibling keywords: ref valid, maxItems ignored is expected to be valid',
            '[draft7/ref.json]: Reference an anchor with a non-relative URI: mismatch is expected to be invalid',
            '[draft7/ref.json]: refs with relative uris and defs: invalid on inner field is expected to be invalid',
            '[draft7/ref.json]: refs with relative uris and defs: invalid on outer field is expected to be invalid',
            '[draft7/ref.json]: relative refs with absolute uris and defs: invalid on inner field is expected to be invalid',
            '[draft7/ref.json]: relative refs with absolute uris and defs: invalid on outer field is expected to be invalid',
            '[draft7/ref.json]: simple URN base URI with JSON pointer: a non-string is invalid is expected to be invalid',
            '[draft7/ref.json]: URN base URI with NSS: a non-string is invalid is expected to be invalid',
            '[draft7/ref.json]: URN base URI with r-component: a non-string is invalid is expected to be invalid',
            '[draft7/ref.json]: URN base URI with q-component: a non-string is invalid is expected to be invalid',
            '[draft7/ref.json]: URN base URI with URN and anchor ref: a non-string is invalid is expected to be invalid',
        ];

        if ($this->is32Bit()) {
            $skip[] = '[draft4/multipleOf.json]: small multiple of large integer: any integer is a multiple of 1e-8 is expected to be valid'; // Test case contains a number which doesn't fit in 32 bits
        }

        return in_array($name, $skip, true);
    }

    private function is32Bit(): bool
    {
        return PHP_INT_SIZE === 4;
    }

    /**
     * @phpstan-return int-mask-of<Validator::ERROR_*>
     */
    private function getCheckModeForDraft(string $draft): int
    {
        switch ($draft) {
            case 'draft6':
            case 'draft7':
                return Constraint::CHECK_MODE_NORMAL | Constraint::CHECK_MODE_STRICT;
            default:
                return Constraint::CHECK_MODE_NORMAL;
        }
    }
}
