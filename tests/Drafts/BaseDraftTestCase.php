<?php

declare(strict_types=1);

namespace JsonSchema\Tests\Drafts;

use JsonSchema\Tests\Constraints\BaseTestCase;

abstract class BaseDraftTestCase extends BaseTestCase
{
    /** @var string */
    protected const RELATIVE_TESTS_ROOT = '/../../vendor/json-schema/json-schema-test-suite/tests';

    /**
     * @return array<string, array{string, string}>
     */
    private function setUpTests(bool $isValid): array
    {
        $filePaths = $this->getFilePaths();
        $skippedTests = $this->getSkippedTests();
        $tests = [];

        foreach ($filePaths as $path) {
            foreach (glob($path . '/*.json') as $file) {
                $filename = basename($file);
                if (in_array($filename, $skippedTests)) {
                    continue;
                }

                $suites = json_decode(file_get_contents($file), false);
                foreach ($suites as $suite) {
                    $suiteDescription = $suite->description;
                    foreach ($suite->tests as $test) {
                        $testCaseDescription = $test->description;
                        if ($isValid === $test->valid) {
                            $tests[
                                $this->createDataSetPath($filename, $suiteDescription, $testCaseDescription)
                            ] = [json_encode($test->data), json_encode($suite->schema)];
                        }
                    }
                }
            }
        }

        return $tests;
    }

    public function getInvalidTests(): array
    {
        return $this->setUpTests(false);
    }

    public function getValidTests(): array
    {
        return $this->setUpTests(true);
    }

    /**
     * @return list<string>
     */
    abstract protected function getFilePaths(): array;

    /**
     * @return list<string>
     */
    abstract protected function getSkippedTests(): array;

    /**
     * Generates a readable path to Json Schema Test Suite data set under test
     */
    private function createDataSetPath(string $filename, string $suiteDesc, string $testCaseDesc): string
    {
        $separator = ' / ';

        return $filename . $separator . $suiteDesc . $separator . $testCaseDesc;
    }
}
