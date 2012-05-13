<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!is_readable(__DIR__.'/../vendor/autoload.php')) {
    echo <<<EOT
You must run `composer.phar install` to install the dependencies
before running the test suite.

EOT;
    exit(1);
}

//composer
$loader = require_once(__DIR__.'/../vendor/autoload.php');
$loader->add('JsonSchema\Tests', __DIR__);
$loader->register();
