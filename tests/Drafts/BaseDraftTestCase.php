<?php

namespace JsonSchema\Tests\Drafts;

use JsonSchema\Tests\Constraints\BaseTestCase;

/**
 * @package JsonSchema\Tests\Drafts
 */
abstract class BaseDraftTestCase extends BaseTestCase
{
    /** @var string */
    protected $relativeTestsRoot = '/../../vendor/json-schema/JSON-Schema-Test-Suite/tests';

    private function setUpTests($isValid)
    {
        $filePaths = $this->getFilePaths();
        $skippedTests = $this->getSkippedTests();
        $tests = array();

        foreach ($filePaths as $path) {
            foreach (glob($path . '/*.json') as $file) {
                $filename = basename($file);
                if (in_array($filename, $skippedTests)) {
                    continue;
                }

                $suites = json_decode(file_get_contents($file));
                foreach ($suites as $suite) {
                    $suiteDescription = $suite->description;
                    foreach ($suite->tests as $test) {
                        $testCaseDescription = $test->description;
                        if ($isValid === $test->valid) {
                            $tests[
                                $this->createDataSetPath($filename, $suiteDescription, $testCaseDescription)
                            ] = array(json_encode($test->data), json_encode($suite->schema));
                        }
                    }
                }
            }
        }

        return $tests;
    }

    /**
     * {@inheritdoc}
     */
    public function getInvalidTests()
    {
        return $this->setUpTests(false);
    }

    /**
     * {@inheritdoc}
     */
    public function getValidTests()
    {
        return $this->setUpTests(true);
    }

    /**
     * @return string[]
     */
    abstract protected function getFilePaths();

    /**
     * @return string[]
     */
    abstract protected function getSkippedTests();

    /**
     * Generates a readable path to Json Schema Test Suite data set under test
     *
     * @param string $filename
     * @param string $suiteDesc
     * @param string $testCaseDesc
     *
     * @return string
     */
    private function createDataSetPath($filename, $suiteDesc, $testCaseDesc)
    {
        $separator = ' / ';

        return $filename . $separator . $suiteDesc . $separator . $testCaseDesc;
    }
}
