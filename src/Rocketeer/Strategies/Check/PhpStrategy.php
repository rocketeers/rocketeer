<?php
namespace Rocketeer\Strategies\Check;

use Illuminate\Container\Container;
use Illuminate\Support\Arr;
use Rocketeer\Abstracts\Strategies\AbstractCheckStrategy;
use Rocketeer\Abstracts\Strategies\AbstractStrategy;
use Rocketeer\Interfaces\Strategies\CheckStrategyInterface;

class PhpStrategy extends AbstractCheckStrategy implements CheckStrategyInterface
{
	/**
	 * The language of the strategy
	 *
	 * @type string
	 */
	protected $language = 'PHP';

	/**
	 * The PHP extensions loaded on server
	 *
	 * @var array
	 */
	protected $extensions = array();

	/**
	 * @param Container $app
	 */
	public function __construct(Container $app)
	{
		$this->app     = $app;
		$this->manager = $this->builder->buildStrategy('Dependencies', 'Composer');
	}

	//////////////////////////////////////////////////////////////////////
	/////////////////////////////// CHECKS ///////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Check that the language used by the
	 * application is at the required version
	 *
	 * @return boolean
	 */
	public function language()
	{
		$required = null;

		// Get the minimum PHP version of the application
		$composer = $this->app['path.base'].DS.$this->manager->getManifest();
		if ($this->files->exists($composer)) {
			$composer = $this->files->get($composer);
			$composer = json_decode($composer, true);

			// Strip versions of constraints
			$required = (string) Arr::get($composer, 'require.php');
			$required = preg_replace('/>=/', '', $required);
		}

		// Cancel if no PHP version found
		if (!$required) {
			return true;
		}

		$version = $this->php()->runLast('version');

		return version_compare($version, $required, '>=');
	}

	/**
	 * Check for the required extensions
	 *
	 * @return array
	 */
	public function extensions()
	{
		$extensions = array(
			'mcrypt'   => ['checkPhpExtension', 'mcrypt'],
			'database' => ['checkDatabaseDriver', $this->app['config']->get('database.default')],
			'cache'    => ['checkCacheDriver', $this->app['config']->get('cache.driver')],
			'session'  => ['checkCacheDriver', $this->app['config']->get('session.driver')],
		);

		// Check PHP extensions
		$errors = [];
		foreach ($extensions as $check) {
			list ($method, $extension) = $check;

			if (!$this->$method($extension)) {
				$errors[] = $extension;
			}
		}

		return $errors;
	}

	/**
	 * Check for the required drivers
	 *
	 * @return array
	 */
	public function drivers()
	{
		return [];
	}

	//////////////////////////////////////////////////////////////////////
	////////////////////////////// HELPERS ///////////////////////////////
	//////////////////////////////////////////////////////////////////////

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
