<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Services\History;

use Rocketeer\Traits\HasLocator;

/**
 * Handles rotation of logs
 */
class LogsHandler
{
	use HasLocator;

	/**
	 * Create logs from the History
	 */
	public function fromHistory()
	{
		$file = $this->getCurrentLogsFile();
		$history = $this->history->getLogs();

		$this->files->put($file, implode(PHP_EOL, $history));
	}

	/**
	 * Get the logs file being currently used
	 *
	 * @return string|false
	 */
	public function getCurrentLogsFile()
	{
		/** @type \Callable $logs */
		$logs = $this->config->get('rocketeer::logs');
		if (!$logs) {
			return false;
		}

		$file = $logs($this->connections);
		$file = $this->app['path.rocketeer.logs'].'/'.$file;
		if (!$this->files->exists($file)) {
			$this->createLogsFile($file);
		}

		return $file;
	}

	/**
	 * Create a logs file if it doesn't exist
	 *
	 * @param string $file
	 */
	protected function createLogsFile($file)
	{
		$directory = dirname($file);

		// Create directory
		if (!is_dir($directory)) {
			$this->files->makeDirectory($directory, 0777, true);
		}

		// Create file
		if (!file_exists($file)) {
			$this->files->put($file, '');
		}
	}
}
