<?php

if(is_readable(__DIR__.'/../vendor/.composer/autoload.php')) {
    //composer
    $loader = require_once(__DIR__.'/../vendor/.composer/autoload.php');
    $loader->add('JsonSchema\Tests', __DIR__);
    $loader->register();

} elseif(is_readable(__DIR__.'/../vendor/symfony/Component/ClassLoader/UniversalClassLoader.php')) {
    //submodule
    require_once __DIR__.'/../vendor/symfony/Component/ClassLoader/UniversalClassLoader.php';

    $loader = new Symfony\Component\ClassLoader\UniversalClassLoader();
    $loader->registerNamespace('JsonSchema', __DIR__.'/../src');
    $loader->registerNamespace('JsonSchema\Tests', __DIR__);
    $loader->register();
}