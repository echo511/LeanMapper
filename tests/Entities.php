<?php

namespace Echo511Tests\LeanMapper\Entity;

use Echo511\LeanMapper\Mapper\AbstractMapper;
use LeanMapper\Entity;

/**
 * @property int $id m:autoincrement
 * @property string $username m:size(255)
 * @property string $name
 * @property string $email m:unique
 * @property Role $role m:hasOne
 * @property Role[] $roles m:hasMany
 * @property \DateTime $created m:comment(Date of user creation)
 * @property \DateTime $customCreatedFormat m:format(string)
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

class Mapper extends AbstractMapper
{

	public function getMappingMetadata()
	{
		return $this->buildMetadata(array('user', 'role'), 'Echo511Tests\LeanMapper', 'prefix_');
	}



}
