<?php

namespace Echo511\LeanMapper\Schema;

use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\DBAL\Driver\PDOMySql\Driver;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\MySqlSchemaManager;
use Doctrine\DBAL\Schema\Schema;
use Echo511\LeanMapper\Configurator;
use Echo511\LeanMapper\Mapper\MapperMatrix;
use Nette\InvalidStateException;
use Nette\Object;

/**
 * Create, update, drop table schema based on all entities regiestered in all projects mappers.
 * @author Nikolas Tsiongas
 */
class DatabaseSchemaManipulator extends Object
{

	/** @var Configurator */
	private $configurator;

	/** @var SchemaGenerator */
	private $schemaGenerator;

	/** @var MapperMatrix */
	private $mapperMatrix;

	/** @var DBALConnection */
	private $connection;

	/** @var AbstractSchemaManager */
	private $schemaManager;

	/**
	 * @param Configurator $configurator
	 * @param SchemaGenerator $schemaGenerator
	 * @param MapperMatrix $mapperMatrix
	 */
	public function __construct(Configurator $configurator, SchemaGenerator $schemaGenerator, MapperMatrix $mapperMatrix)
	{
		$this->configurator = $configurator;
		$this->schemaGenerator = $schemaGenerator;
		$this->mapperMatrix = $mapperMatrix;

		$this->connection = new DBALConnection(array(
		    'dbname' => $this->configurator->getDatabase(),
		    'user' => $this->configurator->getUsername(),
		    'password' => $this->configurator->getPassword(),
		    'host' => $this->configurator->getHost(),
		    'driver' => 'pdo_' . $this->configurator->getDatabaseType(),
			), $this->getDBALDriver());
		$this->schemaManager = $this->getDBALSchemaManager();
	}



	/**
	 * Create database schema.
	 * @param bool $sqlOnly
	 * @return array|null
	 * @throws InvalidStateException
	 */
	public function createSchema($sqlOnly = false)
	{
		if (count($this->getCurrentSchema()->getTables()) > 0 && $sqlOnly === false) {
			throw new InvalidStateException("Cannot create schema. Database is not empty.");
		}
		$sqls = $this->getDesiredSchema()->toSql($this->getDBALPlatform());
		if ($sqlOnly) {
			return $sqls;
		}
		foreach ($sqls as $sql) {
			$this->connection->exec($sql);
		}
	}



	/**
	 * Update database schema
	 * @param bool $sqlOnly
	 * @return array|null
	 */
	public function updateSchema($sqlOnly = false)
	{
		$comparator = new Comparator();
		$schemaDiff = $comparator->compare($this->getCurrentSchema(), $this->getDesiredSchema());
		$sqls = $schemaDiff->toSql($this->getDBALPlatform());
		if ($sqlOnly) {
			return $sqls;
		}
		foreach ($sqls as $sql) {
			$this->connection->exec($sql);
		}
	}



	/**
	 * Drop database schema.
	 * @param bool $sqlOnly
	 * @return array|null
	 */
	public function dropSchema($sqlOnly = false)
	{
		$sqls = $this->getCurrentSchema()->toDropSql($this->getDBALPlatform());
		if ($sqlOnly) {
			return $sqls;
		}
		foreach ($sqls as $sql) {
			$this->connection->exec($sql);
		}
	}



	/**
	 * Get schema the database is currently in.
	 * @return Schema
	 */
	public function getCurrentSchema()
	{
		return $this->schemaManager->createSchema();
	}



	/**
	 * Get schema the database should be in based on entities.
	 * @return Schema
	 */
	public function getDesiredSchema()
	{
		return $this->schemaGenerator->createSchema($this->mapperMatrix->getAllReflections());
	}



	protected function getDBALSchemaManager()
	{
		if ($this->configurator->getDatabaseType() == 'mysql') {
			return new MySqlSchemaManager($this->connection, $this->getDBALPlatform());
		}
	}



	protected function getDBALDriver()
	{
		if ($this->configurator->getDatabaseType() == 'mysql') {
			return new Driver;
		}
	}



	protected function getDBALPlatform()
	{
		if ($this->configurator->getDatabaseType() == 'mysql') {
			return new MySqlPlatform();
		}
	}



}
