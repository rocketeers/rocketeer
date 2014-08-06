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

use Illuminate\Container\Container;
use Rocketeer\Traits\HasLocator;

/**
 * Provides informations and actions around releases
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class ReleasesManager
{
	use HasLocator;

	/**
	 * Cache of the validation file
	 *
	 * @var array
	 */
	protected $state = array();

	/**
	 * Cache of the releases
	 *
	 * @type array
	 */
	public $releases;

	/**
	 * Build a new ReleasesManager
	 *
	 * @param Container $app
	 */
	public function __construct(Container $app)
	{
		$this->app   = $app;
		$this->state = $this->getValidationFile();
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// RELEASES ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get all the releases on the server
	 *
	 * @return integer[]
	 */
	public function getReleases()
	{
		// Get releases on server
		if (is_null($this->releases)) {
			$releases = $this->getReleasesPath();
			$releases = (array) $this->bash->listContents($releases);

			// Filter and sort releases
			$releases = array_filter($releases, function ($release) {
				return preg_match('#[0-9]{14}#', $release);
			});

			rsort($releases);

			$this->releases = $releases;
		}

		return $this->releases;
	}

	/**
	 * Get an array of deprecated releases
	 *
	 * @return integer[]
	 */
	public function getDeprecatedReleases()
	{
		$releases    = (array) $this->getReleases();
		$maxReleases = $this->config->get('rocketeer::remote.keep_releases');

		return array_slice($releases, $maxReleases);
	}

	/**
	 * Get an array of invalid releases
	 *
	 * @return integer[]
	 */
	public function getInvalidReleases()
	{
		$releases = (array) $this->getReleases();
		$invalid  = array_diff($this->state, array_filter($this->state));
		$invalid  = array_keys($invalid);

		return array_intersect($releases, $invalid);
	}

	/**
	 * Get an array of non-current releases
	 *
	 * @return integer[]
	 */
	public function getNonCurrentReleases()
	{
		$releases = (array) $this->getReleases();

		return array_slice($releases, 1);
	}

	////////////////////////////////////////////////////////////////////
	////////////////////////////// PATHS ///////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the path to the releases folder
	 *
	 * @return string
	 */
	public function getReleasesPath()
	{
		return $this->rocketeer->getFolder('releases');
	}

	/**
	 * Get the path to a release
	 *
	 * @param  integer $release
	 *
	 * @return string
	 */
	public function getPathToRelease($release)
	{
		return $this->rocketeer->getFolder('releases/'.$release);
	}

	/**
	 * Get the path to the current release
	 *
	 * @param string|null $folder A folder in the release
	 *
	 * @return string
	 */
	public function getCurrentReleasePath($folder = null)
	{
		if ($folder) {
			$folder = '/'.$folder;
		}

		return $this->getPathToRelease($this->getCurrentRelease().$folder);
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////// VALIDATION ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the validation file
	 *
	 * @return array
	 */
	public function getValidationFile()
	{
		// Get the contents of the validation file
		$file = $this->rocketeer->getFolder('state.json');
		$file = $this->bash->getFile($file) ?: '{}';
		$file = (array) json_decode($file, true);

		// Fill the missing releases
		$releases = (array) $this->getReleases();
		$releases = array_fill_keys($releases, false);

		// Sort entries
		ksort($file);
		ksort($releases);

		// Replace and resort
		$releases = array_replace($releases, $file);
		krsort($releases);

		return $releases;
	}

	/**
	 * Update the contents of the validation file
	 *
	 * @param array $validation
	 */
	public function saveValidationFile(array $validation)
	{
		$file = $this->rocketeer->getFolder('state.json');
		$this->bash->putFile($file, json_encode($validation));

		$this->state = $validation;
	}

	/**
	 * Mark a release as valid
	 *
	 * @param integer|null $release
	 */
	public function markReleaseAsValid($release = null)
	{
		$release              = $release ?: $this->getCurrentRelease();
		$validation           = $this->getValidationFile();
		$validation[$release] = true;

		$this->saveValidationFile($validation);
	}

	/**
	 * Get the state of a release
	 *
	 * @param integer $release
	 *
	 * @return boolean
	 */
	public function checkReleaseState($release)
	{
		return array_get($this->state, $release, true);
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////// CURRENT RELEASE ////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Sanitize a possible release
	 *
	 * @param string $release
	 *
	 * @return string|null
	 */
	protected function sanitizeRelease($release)
	{
		return strlen($release) === 14 ? $release : null;
	}

	/**
	 * Get where to store the current release
	 *
	 * @return string
	 */
	protected function getCurrentReleaseKey()
	{
		$key = 'current_release';

		// Get the scopes
		$connection = $this->connections->getConnection();
		$stage      = $this->connections->getStage();
		$scopes     = array($connection, $stage);
		foreach ($scopes as $scope) {
			$key .= $scope ? '.'.$scope : '';
		}

		return $key;
	}

	/**
	 * Get the current release
	 *
	 * @return string|null
	 */
	public function getCurrentRelease()
	{
		// If we have saved the last deployed release, return that
		$cached = $this->server->getValue($this->getCurrentReleaseKey());
		if ($cached) {
			return $this->sanitizeRelease($cached);
		}

		// Else get and save last deployed release
		$lastDeployed = array_get($this->getReleases(), 0);
		$this->updateCurrentRelease($lastDeployed);

		return $this->sanitizeRelease($lastDeployed);
	}

	/**
	 * Get the release before the current one
	 *
	 * @param string|null $release A release name
	 *
	 * @return string
	 */
	public function getPreviousRelease($release = null)
	{
		// Get all releases and the current one
		$releases = $this->getReleases();
		$current  = $release ?: $this->getCurrentRelease();

		// Get the one before that, or default to current
		$key  = array_search($current, $releases);
		$key = !is_int($key) ? -1 : $key;
		$next = 1;
		do {
			$release = array_get($releases, $key + $next);
			$next++;
		} while (!$this->checkReleaseState($release));

		return $release ?: $current;
	}

	/**
	 * Update the current release
	 *
	 * @param  string|null $release A release name
	 *
	 * @return string
	 */
	public function updateCurrentRelease($release = null)
	{
		if (!$release) {
			$release = $this->bash->getTimestamp();
		}

		$this->server->setValue($this->getCurrentReleaseKey(), $release);

		return $release;
	}
}
