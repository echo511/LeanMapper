<?php

namespace Echo511Tests\LeanMapper;

$container = require __DIR__ . '/../../bootstrap.php';

use Echo511\LeanMapper\Mapper\AbstractMapper;
use Echo511\LeanMapper\Mapper\MapperMatrix;
use Tester\Assert;
use Tester\TestCase;

class MapperMatrixTest extends TestCase
{

	public function testMapper()
	{
		$matrix = new MapperMatrix();
		$matrix->addMapper(new OneMapper());
		$matrix->addMapper(new SecondMapper());

		Assert::equal('id', $matrix->getPrimaryKey('one_foo_user'));
		Assert::equal('one_foo_user', $matrix->getTable('One\Foo\Entity\User'));
		Assert::equal('One\Foo\Entity\User', $matrix->getEntityClass('one_foo_user'));
		Assert::equal('name', $matrix->getColumn('One\Foo\Entity\User', 'name'));
		Assert::equal('name', $matrix->getEntityField('one_foo_user', 'name'));
		Assert::equal('one_foo_user_one_foo_role', $matrix->getRelationshipTable('one_foo_user', 'one_foo_role'));
		Assert::equal('one_foo_role_id', $matrix->getRelationshipColumn('one_foo_user', 'one_foo_role'));
		Assert::equal('one_foo_user', $matrix->getTableByRepositoryClass('One\Foo\Repository\UserRepository'));
		Assert::equal(array(), $matrix->getImplicitFilters('One\Foo\Entity\User'));
	}



	public function testMapperSynergy()
	{
		$matrix = new MapperMatrix();
		$matrix->addMapper(new OneMapper());
		$matrix->addMapper(new SecondMapper());

		Assert::equal('one_foo_user_id', $matrix->getRelationshipColumn('two_foo_article', 'one_foo_user'));
	}



	/** @todo Test for all possible conflict */
	public function testMapperConflict()
	{
		$matrix = new MapperMatrix();
		$matrix->addMapper(new OneMapper($matrix));
		$matrix->addMapper(new ConflictMapper($matrix));

		Assert::exception(function() use ($matrix) {
			$matrix->getPrimaryKey('one_foo_user');
		}, 'Echo511\LeanMapper\Mapper\MapperMatrixException');
	}



}

class OneMapper extends AbstractMapper
{

	public function getMappingMetadata()
	{
		return $this->buildMetadata(array('user', 'role'), 'One\Foo', 'one_foo_');
	}



}

class SecondMapper extends AbstractMapper
{

	public function getPrimaryKey($table)
	{
		return 'primary';
	}



	public function getMappingMetadata()
	{
		return $this->buildMetadata(array('article', 'category'), 'Two\Foo', 'two_foo_');
	}



}

class ConflictMapper extends AbstractMapper
{

	public function getMappingMetadata()
	{
		return $this->buildMetadata(array('user', 'role'), 'One\Foo', 'one_foo_');
	}



}

$test = new MapperMatrixTest;
$test->run();
