<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Rocketeer\Services\Releases;

use Illuminate\Support\Arr;
use Rocketeer\Services\Container\Container;
use Rocketeer\Traits\ContainerAwareTrait;

/**
 * Provides informations and actions around releases.
 */
class ReleasesManager
{
    use ContainerAwareTrait;

    /**
     * Cache of the validation file.
     *
     * @var array
     */
    protected $state = [];

    /**
     * Cache of the releases.
     *
     * @var array
     */
    public $releases = [];

    /**
     * The next release to come.
     *
     * @var string
     */
    protected $nextRelease;

    /**
     * Build a new ReleasesManager.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->state = $this->getValidationFile();
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// RELEASES ///////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get all the releases on the server.
     *
     * @return int[]
     */
    public function getReleases()
    {
        // Get releases on server
        $connection = $this->connections->getCurrentConnectionKey()->toHandle();
        if (!array_key_exists($connection, $this->releases)) {
            $releases = $this->paths->getReleasesFolder();
            $releases = (array) $this->bash->listContents($releases);

            // Filter and sort releases
            $releases = array_filter($releases, function ($release) {
                return $this->isRelease($release);
            });

            rsort($releases);

            $this->releases[$connection] = (array) $releases;
        }

        return $this->releases[$connection];
    }

    /**
     * @param string|int $release
     */
    public function addRelease($release)
    {
        $connection = $this->connections->getCurrentConnectionKey()->name;

        $this->releases[$connection][] = $release;
        krsort($this->releases[$connection]);
    }

    /**
     * Get an array of non-current releases.
     *
     * @return int[]
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
     * @return int[]
     */
    public function getDeprecatedReleases($treshold = null)
    {
        $releases = $this->getReleases();
        $treshold = $treshold ?: $this->config->get('remote.keep_releases');

        // Get first X valid releases
        $keep = $this->getValidReleases();
        $keep = array_slice($keep, 0, $treshold);

        // Compute diff
        $deprecated = array_diff($releases, $keep);
        $deprecated = array_values($deprecated);

        return $deprecated;
    }

    /**
     * Get an array of valid releases.
     *
     * @return int[]
     */
    public function getValidReleases()
    {
        $valid = array_filter($this->state);
        $valid = array_keys($valid);

        return $valid;
    }

    /**
     * Get an array of invalid releases.
     *
     * @return int[]
     */
    public function getInvalidReleases()
    {
        $releases = $this->getReleases();
        $invalid = array_diff($this->state, array_filter($this->state));
        $invalid = array_keys($invalid);

        return array_intersect($releases, $invalid);
    }

    ////////////////////////////////////////////////////////////////////
    ////////////////////////////// PATHS ///////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get the path to a release.
     *
     * @param string $release
     *
     * @return string
     */
    public function getPathToRelease($release)
    {
        return $this->paths->getReleasesFolder($release);
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
        $file = (array) $this->remoteStorage->get();

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
     * Assign a state to a release.
     *
     * @param string|null $release
     * @param bool        $state
     */
    public function markRelease($release = null, $state = true)
    {
        $release = $release ?: $this->getCurrentRelease();

        // If the release is not null, mark it as valid
        if ($release) {
            $this->state[$release] = $state;
            $this->remoteStorage->set($release, $state);
            krsort($this->state);
        }
    }

    /**
     * Mark a release as valid.
     *
     * @param string|null $release
     */
    public function markReleaseAsValid($release = null)
    {
        $this->markRelease($release, true);
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
        return Arr::get($this->state, $release, false);
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
        $current = Arr::get($this->getValidReleases(), 0);
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
        $current = $release ?: $this->getCurrentRelease();

        // Get the one before that, or default to current
        $key = array_search($current, $releases, true);
        $key = !is_int($key) ? -1 : $key;
        $next = 1;
        do {
            $release = Arr::get($releases, $key + $next);
            ++$next;
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
            $manual = $this->getOption('release');
            $manual = $this->isRelease($manual) ? $manual : null;

            $this->nextRelease = $manual ?: $this->bash->getTimestamp();
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
     * @return string|null
     */
    protected function sanitizeRelease($release)
    {
        return $this->isRelease($release) ? (string) $release : null;
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
