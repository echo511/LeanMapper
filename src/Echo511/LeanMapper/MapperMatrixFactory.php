<?php

namespace Echo511\LeanMapper;

use Nette\DI\Container;
use Nette\Object;

/**
 * Create MapperMatrix and add all mappers base on tag 'echo511.leanmapper.mapper'.
 * @author Nikolas Tsiongas
 */
class MapperMatrixFactory extends Object
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



	/**
	 * @return MapperMatrix
	 */
	public function create()
	{
		$matrix = new MapperMatrix;
		foreach ($this->container->findByTag('echo511.leanmapper.mapper') as $serviceName => $tagAttributes) {
			$matrix->addMapper($this->container->getService($serviceName));
		}
		return $matrix;
	}



}
