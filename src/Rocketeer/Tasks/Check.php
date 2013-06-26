<?php
namespace Rocketeer\Tasks;

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
	public $description = 'Check if the server is ready to receive the application';

	/**
	 * Run the Task
	 *
	 * @return  void
	 */
	public function execute()
	{
		$errors    = array();
		$extension = 'The %s extension does not seem to be loaded on the server';

		// Check PHP
		if (!$this->checkPhpVersion()) {
			$errors[] = $this->command->error('The version of PHP on the server does not match Larvel\'s requirements');
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
	 * Check if Composer is on the server
	 *
	 * @return boolean
	 */
	public function checkComposer()
	{
		return $this->getComposer();
	}

	/**
	 * Check if the server is ready to support PHP
	 *
	 * @return boolean
	 */
	public function checkPhpVersion()
	{
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
				return $this->checkPhpExtension('memcached');

			case 'apc':
				return $this->checkPhpExtension('apc');

			case 'redis':
				return $this->checkPhpExtension('redis');

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
		if (!$this->extensions) {
			$extensions       = $this->run('php -m');
			$this->extensions = explode(PHP_EOL, $extensions);
		}

		return in_array($extension, $this->extensions);
	}

}
