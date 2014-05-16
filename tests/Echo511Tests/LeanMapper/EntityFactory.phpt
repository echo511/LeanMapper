<?php

namespace Echo511Tests\LeanMapper;

use Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';
$factory = $container->getByType('Echo511\LeanMapper\EntityFactory\EntityFactory');
$user = $factory->createEntity('Echo511Tests\LeanMapper\Entity\User');

Assert::equal(true, $user->hasDummy());
