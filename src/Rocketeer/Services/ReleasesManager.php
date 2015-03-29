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

use Illuminate\Container\Container;
use Illuminate\Support\Arr;
use Rocketeer\Services\Storages\ServerStorage;
use Rocketeer\Traits\HasLocator;

/**
 * Provides informations and actions around releases.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class ReleasesManager
{
    use HasLocator;

    /**
     * Cache of the validation file.
     *
     * @type array
     */
    protected $state = [];

    /**
     * Cache of the releases.
     *
     * @type array
     */
    public $releases;

    /**
     * The next release to come.
     *
     * @type string
     */
    protected $nextRelease;

    /**
     * The storage.
     *
     * @type ServerStorage
     */
    protected $storage;

    /**
     * Build a new ReleasesManager.
     *
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app     = $app;
        $this->storage = new ServerStorage($app, 'state');
        $this->state   = $this->getValidationFile();
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// RELEASES ///////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get all the releases on the server.
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
                return $this->isRelease($release);
            });

            rsort($releases);

            $this->releases = (array) $releases;
        }

        return $this->releases;
    }

    /**
     * Get an array of non-current releases.
     *
     * @return integer[]
     */
    public function getNonCurrentReleases()
    {
        return $this->getDeprecatedReleases(1);
    }

    /**
     * Get an array of deprecated releases.
     *
     * @param int|null $treshold
     *
     * @return integer[]
     */
    public function getDeprecatedReleases($treshold = null)
    {
        $releases = $this->getReleases();
        $treshold = $treshold ?: $this->config->get('rocketeer::remote.keep_releases');

        return array_slice($releases, $treshold);
    }

    /**
     * Get an array of invalid releases.
     *
     * @return integer[]
     */
    public function getInvalidReleases()
    {
        $releases = $this->getReleases();
        $invalid  = array_diff($this->state, array_filter($this->state));
        $invalid  = array_keys($invalid);

        return array_intersect($releases, $invalid);
    }

    ////////////////////////////////////////////////////////////////////
    ////////////////////////////// PATHS ///////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get the path to the releases folder.
     *
     * @return string
     */
    public function getReleasesPath()
    {
        return $this->paths->getFolder('releases');
    }

    /**
     * Get the path to a release.
     *
     * @param string $release
     *
     * @return string
     */
    public function getPathToRelease($release)
    {
        return $this->paths->getFolder('releases/'.$release);
    }

    /**
     * Get the path to the current release.
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
     * Get the validation file.
     *
     * @return array
     */
    public function getValidationFile()
    {
        $file = $this->storage->get();

        // Fill the missing releases
        $releases = $this->getReleases();
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
     * Mark a release as valid.
     *
     * @param string|null $release
     */
    public function markReleaseAsValid($release = null)
    {
        $release = $release ?: $this->getCurrentRelease();

        // If the release is not null, mark it as valid
        if ($release) {
            $this->state[$release] = true;
            $this->storage->set($release, true);
        }
    }

    /**
     * Get the state of a release.
     *
     * @param int $release
     *
     * @return bool
     */
    public function checkReleaseState($release)
    {
        return Arr::get($this->state, $release, true);
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////// CURRENT RELEASE ////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get the current release.
     *
     * @return string|int|null
     */
    public function getCurrentRelease()
    {
        $current = Arr::get($this->getReleases(), 0);
        $current = $this->sanitizeRelease($current);

        return $this->nextRelease ?: $current;
    }

    /**
     * Get the release before the current one.
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
        $key  = array_search($current, $releases, true);
        $key  = !is_int($key) ? -1 : $key;
        $next = 1;
        do {
            $release = Arr::get($releases, $key + $next);
            $next++;
        } while (!$this->checkReleaseState($release) && isset($this->state[$release]));

        return $release ?: $current;
    }

    /**
     * Get the next release to come.
     *
     * @return string
     */
    public function getNextRelease()
    {
        if (!$this->nextRelease) {
            $this->nextRelease = $this->bash->getTimestamp();
        }

        return $this->nextRelease;
    }

    /**
     * Change the release to come.
     *
     * @param string $release
     */
    public function setNextRelease($release)
    {
        $this->nextRelease = $release;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Sanitize a possible release.
     *
     * @param string|int $release
     *
     * @return string|int|null
     */
    protected function sanitizeRelease($release)
    {
        return $this->isRelease($release) ? $release : null;
    }

    /**
     * Check if it quacks like a duck.
     *
     * @param string|int $release
     *
     * @return bool
     */
    protected function isRelease($release)
    {
        $release = (string) $release;

        return (bool) preg_match('#[0-9]{14}#', $release);
    }
}
