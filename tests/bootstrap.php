<?php

require_once __DIR__.'/../vendor/symfony/Component/ClassLoader/UniversalClassLoader.php';

$loader = new Symfony\Component\ClassLoader\UniversalClassLoader();
$loader->registerNamespace('JsonSchema', __DIR__.'/../src');
$loader->registerNamespace('JsonSchema\Tests', __DIR__);
$loader->register();
