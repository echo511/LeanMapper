<?php

namespace Echo511\LeanMapper;

use LeanMapper\Caller;
use LeanMapper\IMapper;
use LeanMapper\Row;
use Nette\Object;

/**
 * Predecessor for all LeanMapper in project.
 * @author Nikolas Tsiongas
 */
abstract class AbstractMapper extends Object implements IMapper
{

	/** @var MapperMatrix */
	protected $matrix;

	/**
	 * @param MapperMatrix $matrix
	 */
	public function setMatrix(MapperMatrix $matrix)
	{
		$this->matrix = $matrix;
	}



	////////// IMapper //////////

	/** @inheritdoc */
	public function getPrimaryKey($table)
	{
		return 'id';
	}



	/** @inheritdoc */
	public function getTable($entityClass)
	{
		foreach ($this->getMappingMetadata() as $table => $classes) {
			if ($classes['entity'] == $entityClass) {
				return $table;
			}
		}
	}



	/** @inheritdoc */
	public function getEntityClass($table, Row $row = null)
	{
		return $this->getMappingMetadata()[$table]['entity'];
	}



	/** @inheritdoc */
	public function getColumn($entityClass, $field)
	{
		return $field;
	}



	/** @inheritdoc */
	public function getEntityField($table, $column)
	{
		return $column;
	}



	/** @inheritdoc */
	public function getRelationshipTable($sourceTable, $targetTable)
	{
		return $sourceTable . '_' . $targetTable;
	}



	/** @inheritdoc */
	public function getRelationshipColumn($sourceTable, $targetTable)
	{
		return $targetTable . '_' . $this->matrix->getPrimaryKey($targetTable);
	}



	/** @inheritdoc */
	public function getTableByRepositoryClass($repositoryClass)
	{
		foreach ($this->getMappingMetadata() as $table => $classes) {
			if ($classes['repository'] == $repositoryClass) {
				return $table;
			}
		}
	}



	/** @inheritdoc */
	public function getImplicitFilters($entityClass, Caller $caller = null)
	{
		return array();
	}



	////////// Mapping //////////

	/**
	 * Get mapping metadata.
	 * @return array [table => [entity => ..., repository => ...]]
	 */
	abstract public function getMappingMetadata();

	/**
	 * Get tables this mapper handles.
	 * @return array
	 */
	public function getMappedTables()
	{
		return array_keys($this->getMappingMetadata());
	}



	/**
	 * Get entities this mapper handles.
	 * @return array
	 */
	public function getMappedEntities()
	{
		$entities = array();
		foreach ($this->getMappingMetadata() as $metadata) {
			$entities[] = $metadata['entity'];
		}
		return $entities;
	}



	/**
	 * Get entities namespaces this mapper handles.
	 * @return array
	 */
	public function getMappedNamespaces()
	{
		return array();
	}



	/**
	 * Get repositories this mapper handles.
	 * @return array
	 */
	public function getMappedRepositories()
	{
		$repositories = array();
		foreach ($this->getMappingMetadata() as $metadata) {
			$repositories[] = $metadata['repository'];
		}
		return $repositories;
	}



	/**
	 * Mapping metadata helper.
	 * @param array $bases Base name of entities ex.: User, Category etc.
	 * @param string $namespace Default namespace common for repositories and entities ex.: Foo\Bar
	 * @param string $tablePrefix Prefix of DB tables
	 * @return array
	 */
	protected function buildMetadata(array $bases, $namespace, $tablePrefix)
	{
		$metadata = array();
		foreach ($bases as $base) {
			$metadata[$tablePrefix . $base] = array(
			    'entity' => $namespace . '\\Entity\\' . ucfirst($base),
			    'repository' => $namespace . '\\Repository\\' . ucfirst($base) . 'Repository'
			);
		}
		return $metadata;
	}



}
