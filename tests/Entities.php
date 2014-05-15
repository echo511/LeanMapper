<?php

namespace Echo511Tests\LeanMapper\Entity;

use LeanMapper\Entity;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Role $role
 */
class User extends Entity
{

	private $dummy = false;

	public function __construct($arg = null, Dummy $dummy)
	{
		parent::__construct($arg);
		$this->dummy = true;
	}



	public function hasDummy()
	{
		return $this->dummy;
	}



}

interface IUserFactory
{

	/** @return User */
	function create($arg);
}

class Dummy
{
	
}

/**
 * @property int $id
 * @property string $title
 */
class Role extends Entity
{
	
}

interface IRoleFactory
{

	/** @return Role */
	function create();
}
