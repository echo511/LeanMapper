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
	    'host' => '127.0.0.1',
	    'username' => 'root',
	    'password' => '',
	    'database' => '',
	);

	public function loadConfiguration()
	{
		$config = $this->getConfig($this->config);

		$connection = $this->containerBuilder->addDefinition($this->prefix('connection'))
			->setClass('LeanMapper\Connection', array(array(
			'host' => $config['host'],
			'username' => $config['username'],
			'password' => $config['password'],
			'database' => $config['database']
		)));

		$this->containerBuilder->addDefinition($this->prefix('mapper'))
			->setClass('Echo511\LeanMapper\MapperMatrix')
			->setFactory('Echo511\LeanMapper\MapperMatrixFactory::create');

		$this->containerBuilder->addDefinition($this->prefix('entityFactory'))
			->setClass('Echo511\LeanMapper\EntityFactory');

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



	/**
	 * Register extension in DI.
	 * @param Configurator $configurator
	 * @param string $name
	 */
	public static function register(Configurator $configurator, $name = 'leanMapper')
	{
		$configurator->onCompile[] = function ($config, Compiler $compiler) use ($name) {
			$compiler->addExtension($name, new LeanMapperExtension());
		};
	}



}
