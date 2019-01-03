#!/usr/bin/env php
<?php declare(strict_types = 1);

use League\Container\Container;
use League\Container\ReflectionContainer;
use Symfony\Component\Console\Application;

define('BASEDIR', __DIR__);

require BASEDIR . '/vendor/autoload.php';

// Init App
$app = new Application('senki/gct-converter', 'v1.0.0');

// init Dependecy Container
$container = new Container;
$container->delegate(
    (new ReflectionContainer)->cacheResolutions()
);

// Load Commands to App
$commands = require BASEDIR . '/config/commands.php';
foreach ($commands as $commandName) {
    $app->add($container->get($commandName));
}

// Run Forest, run
$app->run();
