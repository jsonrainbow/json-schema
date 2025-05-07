<?php

declare(strict_types=1);

namespace JsonSchema\Tests;

use CallbackFilterIterator;
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
     */
    public function testTestCaseValidatesCorrectly(
        string $testCaseDescription,
        string $testDescription,
        \stdClass $schema,
        mixed $data,
        bool $expectedValidationResult,
        bool $optional
    ): void
    {
        $schemaStorage = new SchemaStorage();
        $schemaStorage->addSchema(property_exists($schema, 'id') ? $schema->id : SchemaStorage::INTERNAL_PROVIDED_SCHEMA_URI, $schema);
        $this->loadRemotesIntoStorage($schemaStorage);
        $validator = new Validator(new Factory($schemaStorage));

        $validator->validate($data, $schema);

        if ($optional && $expectedValidationResult !== (count($validator->getErrors()) === 0)) {
            $this->markTestSkipped('Optional test case would fail');
            return;
        }

        self::assertEquals($expectedValidationResult, count($validator->getErrors()) === 0);
    }

    public function casesDataProvider(): \Generator
    {
        $testDir = __DIR__ . '/../vendor/json-schema/json-schema-test-suite/tests';
        $drafts = array_filter(glob($testDir . '/*'), static function (string $filename) {
            return is_dir($filename);
        });
        $skippedDrafts = ['draft6', 'draft7', 'draft2019-09', 'draft2020-12', 'draft-next', 'latest'];

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

}
