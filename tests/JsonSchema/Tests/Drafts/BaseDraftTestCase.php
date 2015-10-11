<?php

namespace JsonSchema\Tests\Drafts;

use JsonSchema\Tests\Constraints\BaseTestCase;

abstract class BaseDraftTestCase extends BaseTestCase
{
    protected $relativeTestsRoot = '/../../../../vendor/json-schema/JSON-Schema-Test-Suite/tests';

    private function setUpTests($isValid)
    {
        $filePaths = $this->getFilePaths();
        $whiteList = $this->getWhiteList();
        $blackList = $this->getBlackList();
        $tests = array();

        foreach ($filePaths as $path) {
            foreach (glob($path . '/*.json') as $file) {
                $name = basename($file);
                $whiteListed = $whiteList && in_array($name, $whiteList);
                $blackListed = !$whiteList && $blackList && in_array($name, $blackList);
                $mustSkip = $whiteList && !$whiteListed || $blackListed;

                if (!$mustSkip) {
                    $suites = json_decode(file_get_contents($file));

                    foreach ($suites as $suite) {
                        foreach ($suite->tests as $test) {
                            if ($isValid === $test->valid) {
                                $tests[] = array(
                                    json_encode($test->data),
                                    json_encode($suite->schema)
                                );
                           }
                        }
                    }
                }
            }
        }

        return $tests;
    }

    public function getInvalidTests()
    {
        return $this->setUpTests(false);
    }

    public function getValidTests()
    {
        return $this->setUpTests(true);
    }

    protected abstract function getFilePaths();

    /**
     * Returns the list of tests to run, or false, if all
     * the tests must be included in the suite by default.
     *
     * @return false|string[]
     */
    protected function getWhiteList()
    {
        return false;
    }

    /**
     * Returns the list of tests not to run, or false, if no test
     * should be excluded from the suite by default. Note that
     * this list is ignored if a white list has been provided.
     *
     * @return false|string[]
     */
    protected function getBlackList()
    {
        return false;
    }
}
