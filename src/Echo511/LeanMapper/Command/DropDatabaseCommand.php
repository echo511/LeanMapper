<?php

namespace Echo511\LeanMapper\Command;

use Echo511\LeanMapper\Mapper\MapperMatrix;
use Echo511\LeanMapper\Schema\DatabaseSchemaManipulator;
use Echo511\LeanMapper\Schema\SchemaGenerator;
use LeanMapper\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Drop database schema.
 * @author Nikolas Tsiongas
 */
class DropDatabaseCommand extends Command
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
		$this->setName('leanmapper:drop')
			->setDescription('Drop database tables.');
	}



	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	public function execute(InputInterface $input, OutputInterface $output)
	{
		$this->databaseSchemaManipulator->dropSchema();
		$output->writeln('Database tables has been dropped.');
	}



}
