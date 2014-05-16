<?php

namespace Echo511\LeanMapper\EntityFactory;

use LeanMapper\IEntityFactory;
use Nette\DI\Container;
use Nette\Object;

/**
 * Create entities instances on demand using either Nette generated factories or classis way.
 * @author Nikolas Tsiongas
 */
class EntityFactory extends Object implements IEntityFactory
{

	/** @var Container */
	private $container;

	/**
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}



	/** @inheritdoc */
	public function createCollection(array $entities)
	{
		return $entities;
	}



	/** @inheritdoc */
	public function createEntity($entityClass, $arg = null)
	{
		if ($factory = $this->container->getByType($this->resolveEntityFactory($entityClass))) {
			return $factory->create($arg);
		}
		return new $entityClass($arg);
	}



	/**
	 * @param string $entityClass
	 * @return string
	 */
	private function resolveEntityFactory($entityClass)
	{
		$p = explode("\\", $entityClass);
		$p[count($p) - 1] = 'I' . $p[count($p) - 1] . 'Factory';
		return implode("\\", $p);
	}



}
