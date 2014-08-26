<?php
namespace Rocketeer\Console\Commands\Plugins;

use Rocketeer\Abstracts\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;

class InstallCommand extends AbstractCommand
{
	/**
	 * The default name
	 *
	 * @var string
	 */
	protected $name = 'deploy:plugin-install';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Install a plugin';

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
	 * @return integer
	 */
	public function fire()
	{
		return $this->fireTasksQueue('Plugins\Installer');
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
