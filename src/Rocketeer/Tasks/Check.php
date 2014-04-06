<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Tasks;

use Rocketeer\Traits\Task;

/**
 * Check if the server is ready to receive the application
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Check extends Task
{
	/**
	 * The PHP extensions loaded on server
	 *
	 * @var array
	 */
	protected $extensions = array();

	 /**
	 * A description of what the Task does
	 *
	 * @var string
	 */
	protected $description = 'Check if the server is ready to receive the application';

	/**
	 * Whether the Task needs to be run on each stage or globally
	 *
	 * @var boolean
	 */
	public $usesStages = false;

	/**
	 * Run the Task
	 *
	 * @return  void
	 */
	public function execute()
	{
		$errors = array();
		$checks = $this->getChecks();

		foreach ($checks as $check) {
			list($check, $error) = $check;

			$argument = null;
			if (is_array($error)) {
				$argument = $error[0];
				$error    = $error[1];
			}

			// If the check fail, print an error message
			if (!$this->$check($argument)) {
				$errors[] = $error;
			}
		}

		// Return false if any error
		if (!empty($errors)) {
			return $this->halt(implode(PHP_EOL, $errors));
		}

		// Display confirmation message
		$this->command->info('Your server is ready to deploy');
	}

	/**
	 * Get the checks to execute
	 *
	 * @return array
	 */
	protected function getChecks()
	{
		$extension = 'The %s extension does not seem to be loaded on the server';
		$database  = $this->app['config']->get('database.default');
		$cache     = $this->app['config']->get('cache.driver');
		$session   = $this->app['config']->get('session.driver');

		return array(
			array('checkScm',            $this->scm->binary. ' could not be found'),
			array('checkPhpVersion',     'The version of PHP on the server does not match Laravel\'s requirements'),
			array('checkComposer',       'Composer does not seem to be present on the server'),
			array('checkPhpExtension',   array('mcrypt',  sprintf($extension, 'mcrypt'))),
			array('checkDatabaseDriver', array($database, sprintf($extension, $database))),
			array('checkCacheDriver',    array($cache,    sprintf($extension, $cache))),
			array('checkCacheDriver',    array($session,  sprintf($extension, $session))),
		);
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// CHECKS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Check the presence of an SCM on the server
	 *
	 * @return boolean
	 */
	public function checkScm()
	{
		$this->command->comment('Checking presence of '.$this->scm->binary);
		$this->history[] = $this->scm->execute('check');

		return $this->remote->status() == 0;
	}

	/**
	 * Check if Composer is on the server
	 *
	 * @return boolean
	 */
	public function checkComposer()
	{
		if (!$this->server->usesComposer()) {
			return true;
		}

		$this->command->comment('Checking presence of Composer');

		return $this->composer();
	}

	/**
	 * Check if the server is ready to support PHP
	 *
	 * @return boolean
	 */
	public function checkPhpVersion()
	{
		$required = null;

		// Get the minimum PHP version of the application
		$composer = $this->app['path.base'].'/composer.json';
		if ($this->app['files']->exists($composer)) {
			$composer = $this->app['files']->get($composer);
			$composer = json_decode($composer, true);

			// Strip versions of constraints
			$required = array_get($composer, 'require.php');
			$required = preg_replace('/>=/', '', $required);
		}

		// Cancel if no PHP version found
		if (!$required) {
			return true;
		}

		$this->command->comment('Checking PHP version');
		$version = $this->runLast($this->php('-r "print PHP_VERSION;"'));

		return version_compare($version, $required, '>=');
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Check the presence of the correct database PHP extension
	 *
	 * @param  string $database
	 *
	 * @return boolean
	 */
	public function checkDatabaseDriver($database)
	{
		switch ($database) {
			case 'sqlite':
				return $this->checkPhpExtension('pdo_sqlite');

			case 'mysql':
				return $this->checkPhpExtension('mysql') and $this->checkPhpExtension('pdo_mysql');

			default:
				return true;
		}
	}

	/**
	 * Check the presence of the correct cache PHP extension
	 *
	 * @param  string $cache
	 *
	 * @return boolean
	 */
	public function checkCacheDriver($cache)
	{
		switch ($cache) {
			case 'memcached':
			case 'apc':
				return $this->checkPhpExtension($cache);

			case 'redis':
				return $this->which('redis-server');

			default:
				return true;
		}
	}

	/**
	 * Check the presence of a PHP extension
	 *
	 * @param  string $extension    The extension
	 *
	 * @return boolean
	 */
	public function checkPhpExtension($extension)
	{
		$this->command->comment('Checking presence of '.$extension. ' extension');

		// Get the PHP extensions available
		if (!$this->extensions) {
			$this->extensions = (array) $this->run($this->php('-m'), false, true);
		}

		return in_array($extension, $this->extensions);
	}
}
