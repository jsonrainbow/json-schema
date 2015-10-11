<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests;

use JsonSchema\RefResolver;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator;

abstract class ValidatorTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider caseProvider
     *
     * @param string    $file
     * @param string    $title
     * @param mixed     $instance
     * @param \stdClass $schema
     * @param bool      $isInstanceValid
     * @param int       $checkMode
     * @param array     $expectedErrors
     */
    public function test(
        $file,
        $title,
        $instance,
        \stdClass $schema,
        $isInstanceValid,
        $checkMode = Validator::CHECK_MODE_NORMAL,
        array $expectedErrors = array()
    )
    {
        $refResolver = new RefResolver(new UriRetriever());
        $refResolver->resolve($schema);
        $validator = new Validator($checkMode);
        $validator->check($instance, $schema);

        $actualErrors = $validator->getErrors();
        $reportParameters = array(
            $file,
            $title,
            $this->dump($schema),
            $this->dump($instance),
            count($expectedErrors) > 0 ? $this->dump($expectedErrors) : 'no error',
            count($actualErrors) > 0 ? $this->dump($actualErrors) : 'no error'
        );

        if (!$isInstanceValid && count($expectedErrors) === 0) {
            $reportParameters[4] = 'at least one error';
            $this->assertHasError($actualErrors, $reportParameters);
        } else {
            $this->assertErrorsAreEqual($expectedErrors, $actualErrors, $reportParameters);
        }
    }

    /**
     * Main data provider, delegating to #getTests().
     *
     * @return array
     */
    public function caseProvider()
    {
        $whiteList = $this->getWhiteList();
        $blackList = $this->getBlackList();

        return array_filter($this->getTests(), function ($test) use ($whiteList, $blackList) {
            $name = basename($test[0]);
            $whiteListed = $whiteList && in_array($name, $whiteList);
            $blackListed = !$whiteList && $blackList && in_array($name, $blackList);
            $mustSkip = $whiteList && !$whiteListed || $blackListed;

            return !$mustSkip;
        });
    }

    /**
     * Returns test data sets suitable for #test().
     *
     * @return array
     */
    abstract protected function getTests();

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

    /**
     * Returns the JSON-decoded content of a file.
     *
     * @param string $file
     * @return mixed
     * @throws \Exception
     */
    protected function decodeJsonFromFile($file)
    {
        $content = json_decode(file_get_contents($file));

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception(sprintf(
                'json_encode error in file %s -> Error: %s',
                $file,
                json_last_error_msg()
            ));
        }

        return $content;
    }

    private function dump($variable)
    {
        if (defined('JSON_PRETTY_PRINT')) {
            $options = JSON_PRETTY_PRINT;

            if (defined('JSON_PRESERVE_ZERO_FRACTION')) {
                $options |= JSON_PRESERVE_ZERO_FRACTION;
            }

            return json_encode($variable, $options);
        }

        return print_r($variable, true);
    }

    private function assertHasError(array $errors, array $reportParameters)
    {
        if (count($errors) === 0) {
            $this->assertTrue(false, vsprintf($this->getFailureReportMask(), $reportParameters));
        }
    }

    private function assertErrorsAreEqual(array $actual, array $expected, array $reportParameters)
    {
        $report = vsprintf($this->getFailureReportMask(), $reportParameters);

        if (count($actual) !== count($expected)) {
            $this->assertTrue(false, $report);
        }

        foreach ($expected as $error) {
            if (!in_array($error, $actual)) {
                $this->assertTrue(false, $report);
            }
        }
    }

    private function getFailureReportMask()
    {
        return <<<MSG
**********************************************************************

File: %s

Test: %s

Schema: %s

Instance: %s

Expected: %s

Actual: %s

**********************************************************************
MSG;
    }
}
