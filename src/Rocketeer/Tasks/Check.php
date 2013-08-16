<?php
namespace Rocketeer\Tasks;

use Rocketeer\Traits\Task;

/**
 * Check if the server is ready to receive the application
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
	 * Run the Task
	 *
	 * @return  void
	 */
	public function execute()
	{
		$errors    = array();
		$extension = 'The %s extension does not seem to be loaded on the server';

		// Check SCM
		if (!$this->checkScm()) {
			$errors[] = $this->command->error($this->scm->binary . ' could not be found on the server');
		}

		// Check PHP
		if (!$this->checkPhpVersion()) {
			$errors[] = $this->command->error('The version of PHP on the server does not match Laravel\'s requirements');
		}

		// Check MCrypt
		if (!$this->checkPhpExtension('mcrypt')) {
			$errors[] = $this->command->error(sprintf($extension, 'mcrypt'));
		}

		// Check Composer
		if (!$this->checkComposer()) {
			$errors[] = $this->command->error('Composer does not seem to be present on the server');
		}

		// Check database
		$database = $this->app['config']->get('database.default');
		if (!$this->checkDatabaseExtension($database)) {
			$errors[] = $this->command->error(sprintf($extension, $database));
		}

		// Check Cache/Session driver
		$cache   = $this->app['config']->get('cache.driver');
		$session = $this->app['config']->get('session.driver');
		if (!$this->checkCacheDriver($cache) or !$this->checkCacheDriver($session)) {
			$errors[] = $this->command->error(sprintf($extension, $cache));
		}

		// Return false if any error
		if (!empty($errors)) {
			return false;
		}

		// Display confirmation message
		$this->command->info('Your server is ready to deploy');

		return true;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Check the presence of an SCM on the server
	 *
	 * @return boolean
	 */
	public function checkScm()
	{
		$this->command->comment('Checking presence of '.$this->scm->binary);
		$this->scm->execute('check');

		return $this->remote->status() == 0;
	}

	/**
	 * Check if Composer is on the server
	 *
	 * @return boolean
	 */
	public function checkComposer()
	{
		$this->command->comment('Checking presence of Composer');

		return $this->getComposer();
	}

	/**
	 * Check if the server is ready to support PHP
	 *
	 * @return boolean
	 */
	public function checkPhpVersion()
	{
		$this->command->comment('Checking PHP version');
		$version = $this->run('php -r "print PHP_VERSION;"');

		return version_compare($version, '5.3.7', '>=');
	}

	/**
	 * Check the presence of the correct database PHP extension
	 *
	 * @param  string $database
	 *
	 * @return boolean
	 */
	public function checkDatabaseExtension($database)
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
			case 'redis':
				return $this->checkPhpExtension($cache);

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

		if (!$this->extensions) {
			$this->extensions = $this->run('php -m', true, true);
		}

		return in_array($extension, $this->extensions);
	}
}
