<?php
namespace Rocketeer\Console\Commands\Plugins;

use Rocketeer\Abstracts\AbstractCommand;
use Rocketeer\Services\Ignition\Plugins;
use Symfony\Component\Console\Input\InputArgument;

class PublishCommand extends AbstractCommand
{
	/**
	 * The default name
	 *
	 * @var string
	 */
	protected $name = 'deploy:plugin-config';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Publishes the configuration of a plugin';

	/**
	 * Whether the command's task should be built
	 * into a pipeline or run straight
	 *
	 * @type boolean
	 */
	protected $straight = true;

	/**
	 * Run the tasks
	 *
	 * @return void
	 */
	public function fire()
	{
		$publisher = new Plugins($this->laravel);
		$publisher->publish($this->argument('package'));
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return string[][]
	 */
	protected function getArguments()
	{
		return array(
			['package', InputArgument::REQUIRED, 'The package to publish the configuration for'],
		);
	}
}
