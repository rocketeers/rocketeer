<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer;

use Exception;
use Illuminate\Support\Str;
use Rocketeer\Traits\HasLocator;

/**
 * Handles interaction between the User provided informations
 * and the various Rocketeer components
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Rocketeer
{
	use HasLocator;

	/**
	 * The Rocketeer version
	 *
	 * @var string
	 */
	const VERSION = '2.0.0';

	/**
	 * Returns what stage Rocketeer thinks he's in
	 *
	 * @param string      $application
	 * @param string|null $path
	 *
	 * @return string|false
	 */
	public static function getDetectedStage($application = 'application', $path = null)
	{
		$current = $path ?: realpath(__DIR__);
		preg_match('/'.$application.'\/([a-zA-Z0-9_-]+)\/releases\/([0-9]{14})/', $current, $matches);

		return isset($matches[1]) ? $matches[1] : false;
	}

	//////////////////////////////////////////////////////////////////////
	//////////////////////////// CONFIGURATION ///////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Get the name of the application to deploy
	 *
	 * @return string
	 */
	public function getApplicationName()
	{
		return $this->config->get('rocketeer::application_name');
	}

	/**
	 * Get an option from Rocketeer's config file
	 *
	 * @param string $option
	 *
	 * @return string
	 */
	public function getOption($option)
	{
		$original = $this->config->get('rocketeer::'.$option);

		if ($contextual = $this->getContextualOption($option, 'stages', $original)) {
			return $contextual;
		}

		if ($contextual = $this->getContextualOption($option, 'connections', $original)) {
			return $contextual;
		}

		return $original;
	}

	/**
	 * Get a contextual option
	 *
	 * @param string            $option
	 * @param string            $type [stage,connection]
	 * @param string|array|null $original
	 *
	 * @return string|array|\Closure
	 */
	protected function getContextualOption($option, $type, $original = null)
	{
		// Switch context
		switch ($type) {
			case 'stages':
				$contextual = sprintf('rocketeer::on.stages.%s.%s', $this->connections->getStage(), $option);
				break;

			case 'connections':
				$contextual = sprintf('rocketeer::on.connections.%s.%s', $this->connections->getConnection(), $option);
				break;

			default:
				$contextual = sprintf('rocketeer::%s', $option);
				break;
		}

		// Merge with defaults
		$value = $this->config->get($contextual);
		if (is_array($value) && $original) {
			$value = array_replace($original, $value);
		}

		return $value;
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// PATHS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get a configured path
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public function getPath($path)
	{
		return $this->getOption('paths.'.$path);
	}

	/**
	 * Replace patterns in a folder path
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public function replacePatterns($path)
	{
		$app = $this->app;

		// Replace folder patterns
		return preg_replace_callback('/\{[a-z\.]+\}/', function ($match) use ($app) {
			$folder = substr($match[0], 1, -1);

			if ($app->bound($folder)) {
				return str_replace($app['path.base'].'/', null, $app->make($folder));
			}

			return false;
		}, $path);
	}

	/**
	 * Get the path to a folder, taking into account application name and stage
	 *
	 * @param string|null $folder
	 *
	 * @return string
	 */
	public function getFolder($folder = null)
	{
		$folder = $this->replacePatterns($folder);

		$base  = $this->getHomeFolder().'/';
		$stage = $this->connections->getStage();
		if ($folder && $stage) {
			$base .= $stage.'/';
		}
		$folder = str_replace($base, null, $folder);

		return $base.$folder;
	}

	/**
	 * Get the path to the root folder of the application
	 *
	 * @return string
	 */
	public function getHomeFolder()
	{
		$rootDirectory = $this->getOption('remote.root_directory');
		$rootDirectory = Str::finish($rootDirectory, '/');
		$appDirectory  = $this->getOption('remote.app_directory') ?: $this->getApplicationName();

		return $rootDirectory.$appDirectory;
	}

	/**
	 * Get the default path for the SSH key
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function getDefaultKeyPath()
	{
		return $this->getUserHomeFolder().'/.ssh/id_rsa';
	}

	/**
	 * Get the path to the Rocketeer config folder in the users home
	 *
	 * @return string
	 */
	public function getRocketeerConfigFolder()
	{
		return $this->getUserHomeFolder().'/.rocketeer';
	}

	/**
	 * Get the path to the users home folder
	 *
	 * @throws Exception
	 * @return string
	 */
	public function getUserHomeFolder()
	{
		// Get home folder if available (Unix)
		if (!empty($_SERVER['HOME'])) {
			return $_SERVER['HOME'];
			// Else use the home drive (Windows)
		} elseif (!empty($_SERVER['HOMEDRIVE']) && !empty($_SERVER['HOMEPATH'])) {
			return $_SERVER['HOMEDRIVE'].$_SERVER['HOMEPATH'];
		} else {
			throw new Exception('Cannot determine user home directory.');
		}
	}
}
