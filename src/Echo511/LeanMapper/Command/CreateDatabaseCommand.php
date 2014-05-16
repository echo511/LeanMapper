<?php

namespace Echo511\LeanMapper\Command;

use Echo511\LeanMapper\Mapper\MapperMatrix;
use Echo511\LeanMapper\Schema\DatabaseSchemaManipulator;
use Echo511\LeanMapper\Schema\SchemaGenerator;
use LeanMapper\Connection;
use Nette\InvalidStateException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Create database schema.
 * @author Nikolas Tsiongas
 */
class CreateDatabaseCommand extends Command
{

	/** @var DatabaseSchemaManipulator */
	private $databaseSchemaManipulator;

	/**
	 * @param Connection $connection
	 * @param MapperMatrix $matrix
	 * @param SchemaGenerator $schemaGenerator
	 */
	public function __construct(DatabaseSchemaManipulator $databaseSchemaManipulator)
	{
		parent::__construct();
		$this->databaseSchemaManipulator = $databaseSchemaManipulator;
	}



	public function configure()
	{
		$this->setName('leanmapper:create')
			->setDescription('Create database tables.');
	}



	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	public function execute(InputInterface $input, OutputInterface $output)
	{
		try {
			$this->databaseSchemaManipulator->createSchema();
			$output->writeln('Database created.');
		} catch (InvalidStateException $e) {
			$output->writeln($e->getMessage());
		}
	}



}
