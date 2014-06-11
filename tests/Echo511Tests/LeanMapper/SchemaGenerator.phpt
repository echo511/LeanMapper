<?php

namespace Echo511Tests\LeanMapper;

use Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';
$matrix = $container->getByType('Echo511\LeanMapper\Mapper\MapperMatrix');
/* @var $gen \Echo511\LeanMapper\Schema\SchemaGenerator */
$gen = $container->getByType('Echo511\LeanMapper\Schema\SchemaGenerator');
$schema = $gen->createSchema($matrix->getAllReflections());

Assert::equal(true, $schema->hasTable('prefix_user'));
Assert::equal(true, $schema->hasTable('prefix_role'));

$sql = $schema->toSql(new \Doctrine\DBAL\Platforms\MySqlPlatform);
Assert::equal(
	"CREATE TABLE prefix_user (id INT AUTO_INCREMENT NOT NULL, prefix_role_id INT NOT NULL, username VARCHAR(255) NOT NULL, name LONGTEXT NOT NULL, email LONGTEXT NOT NULL, created DATETIME NOT NULL COMMENT 'Date of user creation', customCreatedFormat VARCHAR(255) NOT NULL, INDEX IDX_88A20CB8480C16E1 (prefix_role_id), UNIQUE INDEX UNIQ_88A20CB8E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
	, $sql[0]
);
