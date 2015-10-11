<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Drafts;

use JsonSchema\Tests\ValidatorTestCase;

abstract class DraftTestCase extends ValidatorTestCase
{
    protected function getTests()
    {
        $draftTestDir = realpath(__DIR__ . '/../../../../vendor/json-schema/JSON-Schema-Test-Suite/tests');
        $tests = array();

        foreach ($this->getDirectories() as $relativeDir) {
            foreach (new \DirectoryIterator("{$draftTestDir}/{$relativeDir}") as $item) {
                if ($item->isFile()) {
                    $suites = $this->decodeJsonFromFile($item->getPathname());

                    foreach ($suites as $suite) {
                        foreach ($suite->tests as $test) {
                            $tests[] = array(
                                $item->getFilename(),
                                "{$suite->description} - {$test->description}",
                                $test->data,
                                $suite->schema,
                                $test->valid
                            );
                        }
                    }
                }
            }
        }

        return $tests;
    }

    abstract protected function getDirectories();
}
