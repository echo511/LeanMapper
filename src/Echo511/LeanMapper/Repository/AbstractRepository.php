<?php

namespace Echo511\LeanMapper\Repository;

use LeanMapper\Connection;
use LeanMapper\IEntityFactory;
use LeanMapper\IMapper;
use LeanMapper\Repository;
use LeanQuery\DomainQuery;
use LeanQuery\DomainQueryFactory;
use Nette\Reflection\ClassType;

/**
 * Repository enhanced with LeanQuery.
 * @author Nikolas Tsiongas
 */
abstract class AbstractRepository extends Repository
{

	/** @var DomainQueryFactory */
	protected $queryFactory;

	public function __construct(Connection $connection, IMapper $mapper, IEntityFactory $entityFactory, DomainQueryFactory $queryFactory)
	{
		parent::__construct($connection, $mapper, $entityFactory);
		$this->queryFactory = $queryFactory;
	}



	/**
	 * Create domain query, select entity of repository with alias
	 * @param string $alias
	 * @return DomainQuery
	 */
	protected function createQuery($alias)
	{
		$repositoryClass = (new ClassType($this))->getName();
		return $this->queryFactory->createQuery()->select($alias)->from($this->mapper->getEntityClass($this->mapper->getTableByRepositoryClass($repositoryClass)), $alias);
	}



}
