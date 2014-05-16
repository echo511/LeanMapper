<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/Entities.php';

$configurator = new Nette\Configurator;
$configurator->setDebugMode(true);
$configurator->setTempDirectory(__DIR__ . '/../temp');
Tracy\Debugger::$logDirectory = __DIR__ . '/../log';
$configurator->addConfig(__DIR__ . '/config.neon');
Kdyby\Console\DI\ConsoleExtension::register($configurator);
Echo511\LeanMapper\DI\LeanMapperExtension::register($configurator);
$container = $configurator->createContainer();

$container->application->run();
