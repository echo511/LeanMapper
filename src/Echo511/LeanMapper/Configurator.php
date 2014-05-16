<?php

namespace Echo511\LeanMapper;

use LeanMapper\Connection;
use Nette\Object;

/**
 * Configure database connection.
 * @author Nikolas Tsiongas
 */
class Configurator extends Object
{

	/** @var string */
	private $databaseType;

	/** @var string */
	private $host;

	/** @var string */
	private $username;

	/** @var string */
	private $password;

	/** @var string */
	private $database;

	/** @var Connection */
	private $connection;

	/**
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		$this->databaseType = $config['databaseType'];
		$this->host = $config['host'];
		$this->username = $config['username'];
		$this->password = $config['password'];
		$this->database = $config['database'];
	}



	/**
	 * @return Connection
	 */
	public function getConnection()
	{
		if (!$this->connection) {
			$config['driver'] = 'pdo';
			$config['dsn'] = $this->getDatabaseType() . ':host=' . $this->getHost() . ';dbname=' . $this->getDatabase();
			$config['username'] = $this->getUsername();
			$config['password'] = $this->getPassword();
			$this->connection = new Connection($config);
		}
		return $this->connection;
	}



	/** @return string */
	public function getDatabaseType()
	{
		return $this->databaseType;
	}



	/** @return string */
	public function getHost()
	{
		return $this->host;
	}



	/** @return string */
	public function getUsername()
	{
		return $this->username;
	}



	/** @return string */
	public function getPassword()
	{
		return $this->password;
	}



	/** @return string */
	public function getDatabase()
	{
		return $this->database;
	}



}
