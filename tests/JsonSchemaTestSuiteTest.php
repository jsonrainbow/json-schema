<?php

declare(strict_types=1);

namespace JsonSchema\Tests;

use CallbackFilterIterator;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\Factory;
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
        bool $expectedValidationResult,
        bool $optional
    ): void {
        $schemaStorage = new SchemaStorage();
        $id = is_object($schema) && property_exists($schema, 'id') ? $schema->id : SchemaStorage::INTERNAL_PROVIDED_SCHEMA_URI;
        $schemaStorage->addSchema($id, $schema);
        $this->loadRemotesIntoStorage($schemaStorage);
        $validator = new Validator(new Factory($schemaStorage));

        try {
            $validator->validate($data, $schema, Constraint::CHECK_MODE_NORMAL | Constraint::CHECK_MODE_STRICT);
        } catch (\Exception $e) {
            if ($optional) {
                $this->markTestSkipped('Optional test case would during validate() invocation');
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
        $skippedDrafts = ['draft3', 'draft4', 'draft7', 'draft2019-09', 'draft2020-12', 'draft-next', 'latest'];

        foreach ($drafts as $draft) {
            if (in_array(basename($draft), $skippedDrafts, true)) {
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
                    if (is_object($testCase->schema)) {
                        $testCase->schema->{'$schema'} = 'http://json-schema.org/draft-06/schema#'; // Hardcode $schema property in schema
                    }
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
}
