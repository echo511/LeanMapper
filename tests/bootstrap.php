<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/Entities.php';

if (!class_exists('Tester\Assert')) {
	echo "Install Nette Tester using `composer update --dev`\n";
	exit(1);
}

Tester\Environment::setup();

$configurator = new Nette\Configurator;
$configurator->setDebugMode(FALSE);
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->addConfig(__DIR__ . '/config.neon');
Echo511\LeanMapper\DI\LeanMapperExtension::register($configurator);
return $configurator->createContainer();
