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

use Illuminate\Support\Arr;
use Rocketeer\Abstracts\AbstractTask;

/**
 * Check if the server is ready to receive the application
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Check extends AbstractTask
{
	/**
	 * The PHP extensions loaded on server
	 *
	 * @var array
	 */
	protected $extensions = array();

	/**
	 * A description of what the task does
	 *
	 * @var string
	 */
	protected $description = 'Check if the server is ready to receive the application';

	/**
	 * Whether the task needs to be run on each stage or globally
	 *
	 * @var boolean
	 */
	public $usesStages = false;

	/**
	 * Run the task
	 *
	 * @return boolean|null
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
		$this->explainer->line('Your server is ready to deploy');
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
			['checkScm', $this->scm->getBinary().' could not be found'],
			['checkPhpVersion', 'The version of PHP on the server does not match Laravel\'s requirements'],
			['checkComposer', 'Composer does not seem to be present on the server'],
			['checkPhpExtension', ['mcrypt', sprintf($extension, 'mcrypt')]],
			['checkDatabaseDriver', [$database, sprintf($extension, $database)]],
			['checkCacheDriver', [$cache, sprintf($extension, $cache)]],
			['checkCacheDriver', [$session, sprintf($extension, $session)]],
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
		$this->explainer->line('Checking presence of '.$this->scm->getBinary());
		$results = $this->scm->run('check');
		$this->toOutput($results);

		return $this->getConnection()->status() == 0;
	}

	/**
	 * Check if Composer is on the server
	 *
	 * @return boolean|string
	 */
	public function checkComposer()
	{
		$composer = $this->app['path.base'].DIRECTORY_SEPARATOR.'composer.json';
		if (!file_exists($composer)) {
			return true;
		}

		$this->explainer->line('Checking presence of Composer');

		return $this->composer()->getBinary();
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
			$required = (string) Arr::get($composer, 'require.php');
			$required = preg_replace('/>=/', '', $required);
		}

		// Cancel if no PHP version found
		if (!$required) {
			return true;
		}

		$this->command->info('Checking PHP version');
		$version = $this->bash->runLast($this->php()->version());

		return version_compare($version, $required, '>=');
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Check the presence of the correct database PHP extension
	 *
	 * @param string $database
	 *
	 * @return boolean
	 */
	public function checkDatabaseDriver($database)
	{
		switch ($database) {
			case 'sqlite':
				return $this->checkPhpExtension('pdo_sqlite');

			case 'mysql':
				return $this->checkPhpExtension('mysql') && $this->checkPhpExtension('pdo_mysql');

			default:
				return true;
		}
	}

	/**
	 * Check the presence of the correct cache PHP extension
	 *
	 * @param string $cache
	 *
	 * @return boolean|string
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
	 * @param string $extension The extension
	 *
	 * @return boolean
	 */
	public function checkPhpExtension($extension)
	{
		$this->explainer->line('Checking presence of '.$extension.' extension');

		// Check for HHVM and built-in extensions
		if ($this->php()->isHhvm()) {
			$this->extensions = array(
				'_hhvm',
				'apache',
				'asio',
				'bcmath',
				'bz2',
				'ctype',
				'curl',
				'debugger',
				'fileinfo',
				'filter',
				'gd',
				'hash',
				'hh',
				'iconv',
				'icu',
				'imagick',
				'imap',
				'json',
				'mailparse',
				'mcrypt',
				'memcache',
				'memcached',
				'mysql',
				'odbc',
				'openssl',
				'pcre',
				'phar',
				'reflection',
				'session',
				'soap',
				'std',
				'stream',
				'thrift',
				'url',
				'wddx',
				'xdebug',
				'zip',
				'zlib',
			);
		}

		// Get the PHP extensions available
		if (!$this->extensions) {
			$this->extensions = (array) $this->bash->run($this->php()->extensions(), false, true);
		}

		return in_array($extension, $this->extensions);
	}
}
