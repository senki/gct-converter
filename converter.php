#!/usr/bin/env php
<?php declare(strict_types = 1);

use League\Container\Container;
use League\Container\ReflectionContainer;
use Symfony\Component\Console\Application;

require __DIR__ . '/vendor/autoload.php';

// Init App
$app = new Application('senki/gct-converter', 'v1.1.0');

// init Dependecy Container
$container = new Container;
$container->delegate(
    (new ReflectionContainer)->cacheResolutions()
);

// Load Commands to App
$commands = require __DIR__ . '/config/commands.php';
foreach ($commands as $commandName) {
    $app->add($container->get($commandName));
}

// Run Forest, run
$app->run();
