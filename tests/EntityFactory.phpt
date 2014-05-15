<?php

namespace Echo511Tests\LeanMapper;

$container = require __DIR__ . '/bootstrap.php';

use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

class EntityFactoryTest extends TestCase
{

	/** @var Container */
	public $container;

	public function testCreateInstance()
	{
		$factory = $this->container->getByType('Echo511\LeanMapper\EntityFactory');
		$user = $factory->createEntity('Echo511Tests\LeanMapper\Entity\User');

		Assert::equal(true, $user->hasDummy());
	}



}

$test = new EntityFactoryTest();
$test->container = $container;
$test->run();
