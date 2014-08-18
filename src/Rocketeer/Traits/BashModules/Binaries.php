<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Traits\BashModules;

use Rocketeer\Binaries\AnonymousBinary;

/**
 * Handles finding and calling binaries
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
trait Binaries
{
	////////////////////////////////////////////////////////////////////
	/////////////////////////////// BINARIES ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get an AnonymousBinary instance
	 *
	 * @param string $binary
	 *
	 * @return \Rocketeer\Abstracts\AbstractBinary
	 */
	public function binary($binary)
	{
		// Check for an existing Binary
		$core = sprintf('Rocketeer\Binaries\%s', ucfirst($binary));
		if (class_exists($core)) {
			return new $core($this->app);
		} elseif (class_exists($binary)) {
			return new $binary($this->app);
		}

		// Else wrap the command in an AnonymousBinary
		$anonymous = new AnonymousBinary($this->app);
		$anonymous->setBinary($binary);

		return $anonymous;
	}

	/**
	 * Prefix a command with the right path to PHP
	 *
	 * @return \Rocketeer\Binaries\Php
	 */
	public function php()
	{
		return $this->binary('php');
	}

	/**
	 * Prefix a command with the right path to Composer
	 *
	 * @return \Rocketeer\Binaries\Composer
	 */
	public function composer()
	{
		return $this->binary('composer');
	}

	/**
	 * @return \Rocketeer\Binaries\Phpunit
	 */
	public function phpunit()
	{
		return $this->binary('phpunit');
	}

	/**
	 * @return \Rocketeer\Binaries\Artisan
	 */
	public function artisan()
	{
		return $this->binary('artisan');
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the path to a binary
	 *
	 * @param string         $binary   The name of the binary
	 * @param string|null    $fallback A fallback location
	 * @param string|boolean $default  A last resort place to use as binary
	 *
	 * @return string
	 */
	public function which($binary, $fallback = null, $default = false)
	{
		$location  = false;
		$locations = array(
			[$this->localStorage, 'get', 'paths.'.$binary],
			[$this->rocketeer, 'getPath', $binary],
			[$this, 'runSilently', 'which '.$binary],
		);

		// Add fallback if provided
		if ($fallback) {
			$locations[] = array($this, 'runSilently', 'which '.$fallback);
		}

		// Add command prompt if possible
		if ($this->hasCommand()) {
			$prompt      = $binary.' could not be found, please enter the path to it';
			$locations[] = array($this->command, 'ask', $prompt);
		}

		// Look in all the locations
		$tryout = 0;
		while (!$location && array_key_exists($tryout, $locations)) {
			list($object, $method, $argument) = $locations[$tryout];

			$location = $object->$method($argument);
			$location = strpos($location, 'not found') !== false ? null : $location;
			$tryout++;
		}

		// Store found location
		if (!$this->local) {
			$this->localStorage->set('paths.'.$binary, $location);
		}

		return $location ?: $default;
	}

	/**
	 * Check the Laravel version
	 *
	 * @param string $version  The version to check against
	 * @param string $operator The operator (default: '>=')
	 *
	 * @return bool
	 */
	protected function versionCheck($version, $operator = '>=')
	{
		$app = $this->app;
		if (is_a($app, 'Illuminate\Foundation\Application')) {
			return version_compare($app::VERSION, $version, $operator);
		}

		return false;
	}
}
