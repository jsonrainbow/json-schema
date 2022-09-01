<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests;

if (version_compare(PHP_VERSION, '7.4.0') < 0) {
    /**
     * Inspired by https://github.com/PHPUnitGoodPractices/polyfill
     *
     * @license MIT
     */
    trait PolyfillTrait
    {
        public function expectException($exception)
        {
            if (\is_callable(array('PHPUnit\Framework\TestCase', 'expectException'))) {
                parent::expectException($exception);
            } else {
                $this->setExpectedException($exception);
            }
        }

        public static function assertIsArray($actual, $message = '')
        {
            if (\is_callable(array('PHPUnit\Framework\TestCase', 'assertIsArray'))) {
                parent::assertIsArray($actual, $message);
            } else {
                static::assertInternalType('array', $actual, $message);
            }
        }
    }
} else {
    trait PolyfillTrait
    {
    }
}
