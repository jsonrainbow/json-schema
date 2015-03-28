<?php

use Symfony\CS\Config\Config;
use Symfony\CS\FixerInterface;

$config = Config::create()
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->setUsingCache(true)
    ->setUsingLinter(false);

$finder = $config->getFinder()
    ->in(__DIR__);

return $config;
