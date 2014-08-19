<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Services;

use Exception;
use Illuminate\Support\Str;
use Rocketeer\Traits\HasLocator;

/**
 * Locates folders and paths on the server and locally
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Pathfinder
{
	use HasLocator;

	/**
	 * Get a configured path
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public function getPath($path)
	{
		return $this->rocketeer->getOption('paths.'.$path);
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
		$rootDirectory = $this->rocketeer->getOption('remote.root_directory');
		$rootDirectory = Str::finish($rootDirectory, '/');
		$appDirectory  = $this->rocketeer->getOption('remote.app_directory') ?: $this->rocketeer->getApplicationName();

		return $rootDirectory.$appDirectory;
	}

	/**
	 * Get the default path for the SSH key
	 *
	 * @return string
	 * @throws Exception
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
