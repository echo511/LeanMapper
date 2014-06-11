<?php

namespace Echo511\LeanMapper\Repository;

use LeanMapper\Connection;
use LeanMapper\IEntityFactory;
use LeanMapper\IMapper;
use LeanMapper\Repository;
use LeanQuery\DomainQueryFactory;

abstract class AbstractRepository extends Repository
{

	/** @var DomainQueryFactory */
	protected $queryFactory;

	public function __construct(Connection $connection, IMapper $mapper, IEntityFactory $entityFactory, DomainQueryFactory $queryFactory)
	{
		parent::__construct($connection, $mapper, $entityFactory);
		$this->queryFactory = $queryFactory;
	}



	protected function createQuery($alias)
	{
		$repositoryClass = (new \Nette\Reflection\ClassType($this))->getName();
		return $this->queryFactory->createQuery()->select($alias)->from($this->mapper->getEntityClass($this->mapper->getTableByRepositoryClass($repositoryClass)), $alias);
	}



}
