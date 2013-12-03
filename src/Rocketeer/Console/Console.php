<?php
namespace Rocketeer\Console;

use Illuminate\Console\Application;

/**
 * A standalone Rocketeer CLI
 */
class Console extends Application
{
	/**
	 * Display the application's help
	 *
	 * @return string
	 */
	public function getHelp()
	{
		$help = str_replace($this->getLongVersion(), null, parent::getHelp());

		return
			$this->getLongVersion().
			PHP_EOL.PHP_EOL.
			$this->buildBlock('Current state', $this->getCurrentState()).
			$help;
	}

	/**
	 * Build an help block
	 *
	 * @param string $title
	 * @param array  $informations
	 *
	 * @return string
	 */
	protected function buildBlock($title, $informations)
	{
		$message = '<comment>' .$title. '</comment>';
		foreach ($informations as $name => $info) {
			$message .= PHP_EOL.sprintf('  <info>%-15s</info> %s', $name, $info);
		}

		return $message;
	}

	/**
	 * Get current state of the CLI
	 *
	 * @return array
	 */
	protected function getCurrentState()
	{
		return array(
			'application'   => $this->laravel['rocketeer.rocketeer']->getApplicationName(),
			'configuration' => $this->laravel['path.rocketeer.config'],
			'tasks'         => $this->laravel['path.rocketeer.tasks'],
		);
	}
}
