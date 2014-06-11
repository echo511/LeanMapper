<?php

namespace Echo511\LeanMapper\DI;

use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;

/**
 * Register extension into DI.
 * @author Nikolas Tsiongas
 */
class LeanMapperExtension extends CompilerExtension
{

	public $config = array(
	    'databaseType' => 'mysql',
	    'host' => '127.0.0.1',
	    'database' => 'testdb',
	    'username' => 'root',
	    'password' => ''
	);

	public function loadConfiguration()
	{
		$config = $this->getConfig($this->config);

		$this->containerBuilder->addDefinition($this->prefix('configurator'))
			->setClass('Echo511\LeanMapper\Configurator', array($config));

		$connection = $this->containerBuilder->addDefinition($this->prefix('connection'))
			->setClass('LeanMapper\Connection')
			->setFactory('@Echo511\LeanMapper\Configurator::getConnection');

		$this->containerBuilder->addDefinition($this->prefix('mapperFactory'))
			->setClass('Echo511\LeanMapper\Mapper\MapperMatrixFactory');

		$this->containerBuilder->addDefinition($this->prefix('mapper'))
			->setClass('Echo511\LeanMapper\Mapper\MapperMatrix')
			->setFactory('@Echo511\LeanMapper\Mapper\MapperMatrixFactory::create');

		$this->containerBuilder->addDefinition($this->prefix('entityFactory'))
			->setClass('Echo511\LeanMapper\EntityFactory\EntityFactory');

		$this->containerBuilder->addDefinition($this->prefix('schemaGenerator'))
			->setClass('Echo511\LeanMapper\Schema\SchemaGenerator');

		$this->containerBuilder->addDefinition($this->prefix('databaseSchemaManipulator'))
			->setClass('Echo511\LeanMapper\Schema\DatabaseSchemaManipulator');

		$this->containerBuilder->addDefinition($this->prefix('createDatabase'))
			->setClass('Echo511\LeanMapper\Command\CreateDatabaseCommand')
			->addTag('kdyby.console.command');

		$this->containerBuilder->addDefinition($this->prefix('updateDatabase'))
			->setClass('Echo511\LeanMapper\Command\UpdateDatabaseCommand')
			->addTag('kdyby.console.command');

		$this->containerBuilder->addDefinition($this->prefix('dropDatabase'))
			->setClass('Echo511\LeanMapper\Command\DropDatabaseCommand')
			->addTag('kdyby.console.command');
		
		// LeanQuery
		$this->containerBuilder->addDefinition($this->prefix('queryHydrator'))
			->setClass('LeanQuery\Hydrator');
		
		$this->containerBuilder->addDefinition($this->prefix('queryHelper'))
			->setClass('LeanQuery\QueryHelper');
		
		$this->containerBuilder->addDefinition($this->prefix('queryFactory'))
			->setClass('LeanQuery\DomainQueryFactory');

		$useProfiler = isset($config['profiler']) ? $config['profiler'] : $this->containerBuilder->parameters['debugMode'];

		unset($config['profiler']);

		if ($useProfiler) {
			$panel = $this->containerBuilder->addDefinition($this->prefix('panel'))
				->setClass('DibiNettePanel')
				->addSetup('Nette\Diagnostics\Debugger::getBar()->addPanel(?)', array('@self'))
				->addSetup('Nette\Diagnostics\Debugger::getBlueScreen()->addPanel(?)', array('DibiNettePanel::renderException'));

			$connection->addSetup('$service->onEvent[] = ?', array(array($panel, 'logEvent')));
		}
	}



	public function beforeCompile()
	{
		foreach ($this->containerBuilder->findByTag('echo511.leanmapper.mapper') as $name => $attr) {
			$this->containerBuilder->getDefinition($name)->setAutowired(false);
		}
	}



	/**
	 * Register extension in DI.
	 * @param Configurator $configurator
	 * @param string $name
	 */
	public static function register(Configurator $configurator, $name = 'leanmapper')
	{
		$configurator->onCompile[] = function ($config, Compiler $compiler) use ($name) {
			$compiler->addExtension($name, new LeanMapperExtension());
		};
	}



}
