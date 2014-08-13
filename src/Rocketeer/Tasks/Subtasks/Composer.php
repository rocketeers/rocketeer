<?php
namespace Rocketeer\Tasks\Subtasks;

use Rocketeer\Abstracts\AbstractTask;

class Composer extends AbstractTask
{
	/**
	 * A description of what the task does
	 *
	 * @var string
	 */
	protected $description = 'Installs Composer dependencies';
	/**
	 * Run the task
	 *
	 * @return string
	 */
	public function execute()
	{
		// Find Composer
		$composer = $this->composer();
		if (!$this->force and (!$composer->getBinary() or !$this->localStorage->usesComposer())) {
			return true;
		}

		// Get the Composer commands to run
		$tasks = $this->rocketeer->getOption('remote.composer');
		if (!is_callable($tasks)) {
			return true;
		}

		// Cancel if no tasks to execute
		$tasks = (array) $tasks($composer, $this);
		if (empty($tasks)) {
			return true;
		}

		// Run commands
		$this->runForCurrentRelease($tasks);

		return $this->checkStatus('Composer could not install dependencies');
	}
}
