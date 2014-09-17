<?php
namespace Rocketeer\Tasks\Plugins;

use Rocketeer\Abstracts\AbstractTask;

class Installer extends AbstractTask
{
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Installs plugins';

	/**
	 * Whether to run the commands locally
	 * or on the server
	 *
	 * @type boolean
	 */
	protected $local = true;

	/**
	 * Run the task
	 *
	 * @return null
	 */
	public function execute()
	{
		// Get package and destination folder
		$package = $this->command->argument('package');
		$folder  = $this->paths->getRocketeerConfigFolder();

		// Add version if necessary
		if (strpos($package, ':') === false) {
			$package .= ':dev-master';
		}

		$command = $this->composer()->require($package, array(
			'--working-dir' => $folder,
		));

		// Install plugin
		$this->explainer->line('Installing '.$package);
		$this->run($this->shellCommand($command));

		// Prune duplicate Rocketeer
		$this->files->deleteDirectory($folder.'/vendor/anahkiasen/rocketeer');
	}
}
