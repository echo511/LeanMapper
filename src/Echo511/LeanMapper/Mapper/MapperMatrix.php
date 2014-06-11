<?php

namespace Echo511\LeanMapper\Mapper;

use LeanMapper\Caller;
use LeanMapper\DefaultMapper;
use LeanMapper\IMapper;
use LeanMapper\Reflection\EntityReflection;
use LeanMapper\Row;
use Nette\Object;

/**
 * Matrix for mappers. Allow use of multiple mappers in one project.
 * @author Nikolas Tsiongas
 */
class MapperMatrix extends Object implements IMapper
{

	/** @var DefaultMapper */
	protected $defaultMapper;

	/** @var AbstractMapper[] */
	protected $mappers = array();

	/** @var bool */
	private $init = false;

	/** @var AbstractMapper[] */
	private $tableToMapper = array();

	/** @var AbstractMapper[] */
	private $entityClassToMapper = array();

	/** @var AbstractMapper[] */
	private $entityNamespaceToMapper = array();

	/** @var AbstractMapper[] */
	private $repositoryClassToMapper = array();

	/**
	 * Add mapper to matrix.
	 * @param AbstractMapper $mapper
	 */
	public function addMapper(AbstractMapper $mapper)
	{
		$mapper->setMatrix($this);
		$this->mappers[] = $mapper;
	}



	/**
	 * Get reflections of all entities in project.
	 * @return EntityReflection[]
	 */
	public function getAllReflections()
	{
		$this->init();
		$reflections = array();
		foreach ($this->entityClassToMapper as $entityClass => $mapper) {
			$reflections[] = new EntityReflection($entityClass, $this);
		}
		return $reflections;
	}



	////////// IMapper //////////

	/** @inheritdoc */
	public function getPrimaryKey($table)
	{
		$this->init();
		return $this->getMapperByTable($table)->getPrimaryKey($table);
	}



	/** @inheritdoc */
	public function getTable($entityClass)
	{
		$this->init();
		return $this->getMapperByEntityClass($entityClass)->getTable($entityClass);
	}



	/** @inheritdoc */
	public function getEntityClass($table, Row $row = null)
	{
		$this->init();
		return $this->getMapperByTable($table)->getEntityClass($table, $row);
	}



	/** @inheritdoc */
	public function getColumn($entityClass, $field)
	{
		$this->init();
		return $this->getMapperByEntityClass($entityClass)->getColumn($entityClass, $field);
	}



	/** @inheritdoc */
	public function getEntityField($table, $column)
	{
		$this->init();
		return $this->getMapperByTable($table)->getEntityField($table, $column);
	}



	/** @inheritdoc */
	public function getRelationshipTable($sourceTable, $targetTable)
	{
		$this->init();
		$table = $this->getMapperByTable($sourceTable)->getRelationshipTable($sourceTable, $targetTable);
		$this->tableToMapper[$table] = $this->defaultMapper;
		return $table;
	}



	/** @inheritdoc */
	public function getRelationshipColumn($sourceTable, $targetTable)
	{
		$this->init();
		return $this->getMapperByTable($sourceTable)->getRelationshipColumn($sourceTable, $targetTable);
	}



	/** @inheritdoc */
	public function getImplicitFilters($entityClass, Caller $caller = null)
	{
		$this->init();
		return $this->getMapperByEntityClass($entityClass)->getImplicitFilters($entityClass, $caller);
	}



	/** @inheritdoc */
	public function getTableByRepositoryClass($repositoryClass)
	{
		$this->init();
		return $this->getMapperByRepositoryClass($repositoryClass)->getTableByRepositoryClass($repositoryClass);
	}



	////////// Internals //////////

	/**
	 * Get mapper handling specific table
	 * @param string $table
	 * @return AbstractMapper
	 * @throws MapperMatrixException
	 */
	protected function getMapperByTable($table)
	{
		if (!isset($this->tableToMapper[$table])) {
			throw new MapperMatrixException("Table $table has no mapper attached.");
		}
		return $this->tableToMapper[$table];
	}



	/**
	 * Get mapper handling specific entity
	 * @param string $entityClass
	 * @return AbstractMapper
	 * @throws MapperMatrixException
	 */
	protected function getMapperByEntityClass($entityClass)
	{
		if (isset($this->entityClassToMapper[$entityClass])) {
			return $this->entityClassToMapper[$entityClass];
		} else {
			if (isset($this->entityNamespaceToMapper[$this->extractNamespace($entityClass)])) {
				return $this->entityNamespaceToMapper[$this->extractNamespace($entityClass)];
			}
		}
		throw new MapperMatrixException("Entity $entityClass has no mapper attached.");
	}



	/**
	 * Get mapper handling specific repository
	 * @param string $repositoryClass
	 * @return AbstractMapper
	 * @throws MapperMatrixException
	 */
	protected function getMapperByRepositoryClass($repositoryClass)
	{
		if (!isset($this->repositoryClassToMapper[$repositoryClass])) {
			throw new MapperMatrixException("Repository $repositoryClass has no mapper attached.");
		}
		return $this->repositoryClassToMapper[$repositoryClass];
	}



	/**
	 * Load information from mappers and learn which attached mapper handles which entities, repositories, tables.
	 * Perform check of conflict between mappers.
	 */
	private function init()
	{
		if (!$this->init) {
			$this->init = true;
			$this->defaultMapper = new DefaultMapper;
			$this->checkConflicts();
			foreach ($this->mappers as $mapper) {
				foreach ($mapper->getMappedTables() as $table) {
					$this->tableToMapper[$table] = $mapper;
				}

				foreach ($mapper->getMappedEntities() as $entity) {
					$this->entityClassToMapper[$entity] = $mapper;
				}

				foreach ($mapper->getMappedNamespaces() as $namespace) {
					$this->entityNamespaceToMapper[$namespace] = $mapper;
				}

				foreach ($mapper->getMappedRepositories() as $repository) {
					$this->repositoryClassToMapper[$repository] = $mapper;
				}
			}
		}
	}



	/**
	 * Check if every mapper handles only its specific tables, entities, repositories.
	 * @throws MapperMatrixException
	 */
	private function checkConflicts()
	{
		$mappedTables = array();
		$mappedEntityClasses = array();
		$mappedEntityNamespaces = array();
		$mappedRepositories = array();
		foreach ($this->mappers as $mapper) {
			foreach ($mapper->getMappedTables() as $table) {
				if (isset($mappedTables[$table])) {
					throw new MapperMatrixException("Table $table is handled by two connected mappers: " . get_class($mapper) . " and " . $mappedTables[$table]);
				}
				$mappedTables[$table] = get_class($mapper);
			}

			foreach ($mapper->getMappedEntities() as $entity) {
				if (isset($mappedEntityClasses[$entity])) {
					throw new MapperMatrixException("Entity $entity is handled by two connected mappers: " . get_class($mapper) . " and " . $mappedEntityClasses[$entity]);
				}
				$mappedEntityClasses[$entity] = get_class($mapper);
			}

			foreach ($mapper->getMappedNamespaces() as $namespace) {
				if (isset($mappedEntityNamespaces[$namespace])) {
					throw new MapperMatrixException("Entity namespace $namespace is handled by two connected mappers: " . get_class($mapper) . " and " . $mappedEntityNamespaces[$namespace]);
				}
				$mappedEntityNamespaces[$namespace] = get_class($mapper);
			}

			foreach ($mapper->getMappedRepositories() as $repository) {
				if (isset($mappedRepositories[$repository])) {
					throw new MapperMatrixException("Repository $repository is handled by two connected mappers: " . get_class($mapper) . " and " . $mappedRepositories[$repository]);
				}
				$mappedRepositories[$repository] = get_class($mapper);
			}
		}
	}



	/**
	 * @param string $class
	 * @return string
	 */
	private function extractNamespace($class)
	{
		$p = explode("\\", $class);
		unset($p[count($p)]);
		return implode("\\", $p);
	}



}
