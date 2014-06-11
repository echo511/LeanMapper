<?php

namespace Echo511\LeanMapper\Schema;

use DateTime;
use Doctrine\DBAL\Schema\Schema;
use Echo511\LeanMapper\Mapper\MapperMatrix;
use Exception;
use LeanMapper\Exception\InvalidAnnotationException;
use LeanMapper\Reflection\EntityReflection;
use LeanMapper\Reflection\Property;
use LeanMapper\Relationship\HasMany;
use LeanMapper\Relationship\HasOne;
use Nette\Object;
use ReflectionClass;

/**
 * Doctrine schema generator.
 * @author jasir (https://github.com/jasir)
 * @author Nikolas Tsiongas
 */
class SchemaGenerator extends Object
{

	/** @var MapperMatrix */
	private $mapperMatrix;

	/**
	 * @param MapperMatrix $mapper
	 */
	public function __construct(MapperMatrix $mapper)
	{
		$this->mapperMatrix = $mapper;
	}



	/**
	 * 
	 * @param EntityReflection[] $reflections
	 * @return Schema
	 * @throws Exception
	 * @throws InvalidAnnotationException
	 */
	public function createSchema(array $reflections)
	{
		$schema = new Schema();

		foreach ($reflections as $reflection) {
			$properties = $reflection->getEntityProperties();
			$onEnd = array();

			if (count($properties) === 0) {
				continue;
			}

			$table = $schema->createTable($this->mapperMatrix->getTable($reflection->getName()));

			foreach ($properties as $property) {
				if (!$property->hasRelationship()) {
					$type = $this->getType($property);

					if ($type === NULL) {
						throw new Exception('Unknown type');
					}

					$column = $table->addColumn($property->getColumn(), $type);

					if ($property->getColumn() == $this->mapperMatrix->getPrimaryKey($table->getName())) {
						$table->setPrimaryKey([$property->getColumn()]);
						if ($property->hasCustomFlag('unique')) {
							throw new InvalidAnnotationException(
							"Entity {$reflection->name}:{$property->getName()} - m:unique can not be used together with primary key."
							);
						}
					}

					if ($property->hasCustomFlag('autoincrement')) {
						$column->setAutoincrement(true);
					}

					/*
					  if ($property->containsEnumeration()) {
					  $column->getType()->setEnumeration($property->getEnumValues());
					  }
					 */

					if ($property->hasCustomFlag('size')) {
						$column->setLength($property->getCustomFlagValue('size'));
					}
				} else {
					$relationship = $property->getRelationship();

					if ($relationship instanceof HasMany) {
						$relationshipTable = $schema->createTable($relationship->getRelationshipTable());

						$relationshipTable->addColumn($relationship->getColumnReferencingSourceTable(), 'integer');
						$relationshipTable->addColumn($relationship->getColumnReferencingTargetTable(), 'integer');

						$relationshipTable->addForeignKeyConstraint(
							$table, [$relationship->getColumnReferencingSourceTable()], [$this->mapperMatrix->getPrimaryKey($relationship->getRelationshipTable())], array('onDelete' => 'CASCADE')
						);

						$relationshipTable->addForeignKeyConstraint(
							$relationship->getTargetTable(), [$relationship->getColumnReferencingTargetTable()], [$this->mapperMatrix->getPrimaryKey($relationship->getRelationshipTable())], array('onDelete' => 'CASCADE')
						);
					} elseif ($relationship instanceof HasOne) {
						$column = $table->addColumn($relationship->getColumnReferencingTargetTable(), 'integer');
						if (!$property->hasCustomFlag('nofk')) {
							$cascade = $property->isNullable() ? 'SET NULL' : 'CASCADE';
							$table->addForeignKeyConstraint(
								$relationship->getTargetTable(), [$column->getName()], [$this->mapperMatrix->getPrimaryKey($relationship->getTargetTable())], array('onDelete' => $cascade)
							);
						}
					}
				}

				if ($property->hasCustomFlag('unique')) {
					$indexColumns = $this->parseColumns($property->getCustomFlagValue('unique'), array($column->getName()));
					$onEnd[] = $this->createIndexClosure($table, $indexColumns, TRUE);
				}

				if ($property->hasCustomFlag('index')) {
					$indexColumns = $this->parseColumns($property->getCustomFlagValue('index'), array($column->getName()));
					$onEnd[] = $this->createIndexClosure($table, $indexColumns, FALSE);
				}

				if ($property->hasCustomFlag('comment')) {
					$column->setComment($property->getCustomFlagValue('comment'));
				}


				if (isset($column)) {
					if ($property->isNullable()) {
						$column->setNotnull(false);
					}

					if ($property->hasDefaultValue()) {
						$column->setDefault($property->getDefaultValue());
					}
				}
			}
			foreach ($onEnd as $cb) {
				$cb();
			}
		}

		return $schema;
	}



	private function createIndexClosure($table, $columns, $unique)
	{
		return function() use ($table, $columns, $unique) {
			if ($unique) {
				$table->addUniqueIndex($columns);
			} else {
				$table->addIndex($columns);
			}
		};
	}



	private function parseColumns($flag, $columns)
	{
		foreach (explode(',', $flag) as $c) {
			$c = trim($c);
			if (!empty($c)) {
				$columns[] = $c;
			}
		}
		return $columns;
	}



	private function getType(Property $property)
	{
		$type = NULL;

		if ($property->isBasicType()) {
			$type = $property->getType();

			if ($type == 'string') {
				if (!$property->hasCustomFlag('size')) {
					$type = 'text';
				}
			}

			/* if ($property->containsEnumeration()) {
			  $type = 'enum';
			  } */
		} else {
			// Objects
			$class = new ReflectionClass($property->getType());
			$class = $class->newInstance();

			if ($class instanceof DateTime) {
				if ($property->hasCustomFlag('format')) {
					$type = $property->getCustomFlagValue('format');
				} else {
					$type = 'datetime';
				}
			}
		}

		return $type;
	}



}
