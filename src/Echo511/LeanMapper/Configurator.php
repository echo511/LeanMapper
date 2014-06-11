<?php

namespace Echo511\LeanMapper;

use DibiPdoDriver;
use LeanMapper\Connection;
use Nette\Object;
use PDO;

/**
 * Configure database connection.
 * @author Nikolas Tsiongas
 */
class Configurator extends Object
{

	/** @var string */
	private $driver;

	/** @var string */
	private $host;

	/** @var string */
	private $user;

	/** @var string */
	private $password;

	/** @var string */
	private $dbname;

	/** @var Connection */
	private $connection;

	/**
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		$this->driver = $config['driver'];
		$this->host = $config['host'];
		$this->user = $config['user'];
		$this->password = $config['password'];
		$this->dbname = $config['dbname'];
	}



	/**
	 * @return Connection
	 */
	public function getConnection()
	{
		if (!$this->connection) {
			if ($this->driver == 'pdo_mysql') {
				$type = 'mysql';
			}
			$config['driver'] = 'pdo';
			$config['dsn'] = $type . ':host=' . $this->getHost() . ';dbname=' . $this->getDbName();
			$config['username'] = $this->getUser();
			$config['password'] = $this->getPassword();
			$this->connection = new Connection($config);
		}
		return $this->connection;
	}



	/** @return string */
	public function getDatabaseType()
	{
		$driver = $this->getConnection()->getDriver();
		if ($driver instanceof DibiPdoDriver) {
			return $driver->getResource()->getAttribute(PDO::ATTR_DRIVER_NAME);
		}
	}



	/** @return string */
	public function getHost()
	{
		return $this->host;
	}



	/** @return string */
	public function getUser()
	{
		return $this->user;
	}



	/** @return string */
	public function getPassword()
	{
		return $this->password;
	}



	/** @return string */
	public function getDbName()
	{
		return $this->dbname;
	}



}
