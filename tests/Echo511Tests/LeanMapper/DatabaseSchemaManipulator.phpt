<?php

namespace Echo511Tests\LeanMapper;

use Doctrine\DBAL\Platforms\MySqlPlatform;
use Echo511\LeanMapper\Configurator;
use Echo511\LeanMapper\Mapper\MapperMatrix;
use Echo511\LeanMapper\Schema\DatabaseSchemaManipulator;
use Echo511\LeanMapper\Schema\SchemaGenerator;
use Echo511Tests\LeanMapper\Entity\Mapper;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

if (!extension_loaded('pdo_mysql')) {
	echo "pdo_mysql extension is not loaded.";
	exit(1);
}

$configurator = new Configurator(array(
    'databaseType' => 'mysql',
    'host' => '127.0.0.1',
    'username' => 'root',
    'password' => '',
    'database' => 'testdb'
	));
$connection = $configurator->getConnection();
$mapper = new MapperMatrix();
$mapper->addMapper(new Mapper);
$schemaGenerator = new SchemaGenerator($mapper);
$schemaManipulator = new DatabaseSchemaManipulator($configurator, $schemaGenerator, $mapper);


Assert::equal($schemaGenerator->createSchema($mapper->getAllReflections())->toSql(new MySqlPlatform), $schemaManipulator->createSchema(true));
//Assert::equal($schemaGenerator->createSchema($mapper->getAllReflections())->toDropSql(new MySqlPlatform), $schemaManipulator->dropSchema(true));
