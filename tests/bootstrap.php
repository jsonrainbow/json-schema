<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$autoloadFile = dirname(__DIR__) . '/vendor/autoload.php';
if (! is_readable($autoloadFile)) {
    echo <<<EOT
You must run `composer.phar install` to install the dependencies
before running the test suite.

EOT;
    exit(1);
}

// Include the Composer generated autoloader
require_once $autoloadFile;

spl_autoload_register(function ($class)
{
    if (0 === strpos($class, 'JsonSchema\\Tests')) {
        $classFile = str_replace('\\', '/', $class) . '.php';
        require __DIR__ . '/' . $classFile;
    }
});

// to avoid the following warning incl. fail of all date-using tests:
//
// DateTime::createFromFormat(): It is not safe to rely on the system's timezone settings.
// You are *required* to use the date.timezone setting or the date_default_timezone_set() function.
//
date_default_timezone_set('UTC');
