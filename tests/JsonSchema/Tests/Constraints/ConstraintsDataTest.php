<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

use JsonSchema\Tests\ValidatorTestCase;

class ConstraintsDataTest extends ValidatorTestCase
{
    protected function getTests()
    {
        $tests = array();

        foreach (new \DirectoryIterator(__DIR__ . '/tests') as $item) {
            if ($item->isFile()) {
                $this->loadTest($item->getPathname(), $tests);
            }
        }

        return $tests;
    }

    protected function loadTest($file, array &$tests)
    {
        $content = $this->decodeJsonFromFile($file);

        foreach ($content->valid as $i => $test) {
            $tests[] = array(
                basename($file),
                "valid test #{$i} (check mode: {$test->checkMode})",
                $test->data,
                $test->schema,
                true,
                $test->checkMode
            );
        }

        foreach ($content->invalid as $i => $test) {
            $tests[] = array(
                basename($file),
                "invalid test #{$i} (check mode: {$test->checkMode})",
                $test->data,
                $test->schema,
                false,
                $test->checkMode,
                array_map(function ($error) {
                    return (array) $error;
                }, $test->errors)
            );
        }

        return $tests;
    }
}
