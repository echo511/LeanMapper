<?php

namespace Echo511Tests\LeanMapper;

use Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';
$matrix = $container->getByType('Echo511\LeanMapper\Mapper\MapperMatrix');
$gen = $container->getByType('Echo511\LeanMapper\Schema\SchemaGenerator');
$schema = $gen->createSchema($matrix->getAllReflections());

Assert::equal(true, $schema->hasTable('prefix_user'));
Assert::equal(true, $schema->hasTable('prefix_role'));
