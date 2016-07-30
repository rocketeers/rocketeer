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

namespace Rocketeer\Strategies\Check;

use Illuminate\Support\Arr;
use Rocketeer\Binaries\PackageManagers\AbstractPackageManager;
use Rocketeer\Strategies\AbstractStrategy;
use Rocketeer\Strategies\HasBinaryTrait;

/**
 * An abstract for check strategies leveraging
 * a package manager.
 */
abstract class AbstractCheckStrategy extends AbstractStrategy
{
    use HasBinaryTrait;

    /**
     * The language of the strategy.
     *
     * @var string
     */
    protected $language;

    /**
     * @var string
     */
    protected $manager;

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @return AbstractPackageManager
     */
    public function getManager()
    {
        if (is_string($this->manager)) {
            $this->manager = $this->binary($this->manager);
        }

        return $this->manager;
    }

    /**
     * @param string|AbstractPackageManager $manager
     */
    public function setManager($manager)
    {
        $this->manager = $manager;
    }

    //////////////////////////////////////////////////////////////////////
    /////////////////////////////// CHECKS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Check that the PM that'll install
     * the app's dependencies is present.
     *
     * @return bool
     */
    public function manager()
    {
        $manager = $this->getManager();
        if (!$manager) {
            return true;
        }

        // Check if we have a manifest to go from
        // Else assume we're not using it on this project
        $managerName = $manager->getName();
        $hasManifest = $manager->hasManifest();
        if (!$hasManifest) {
            return true;
        }

        // Check if the server has the package manager
        $hasManager = $manager->isExecutable();
        if (!$hasManager) {
            $this->explainer->error($managerName.' is not present (or executable)');
        }

        return $hasManager && $hasManifest;
    }

    /**
     * Check that the language used by the
     * application is at the required version.
     *
     * @return bool
     */
    public function language()
    {
        $required = null;

        // Get the minimum version of the application
        if ($this->getManager() && $manifest = $this->getManager()->getManifestContents()) {
            $required = $this->getLanguageConstraint($manifest);
        }

        // Cancel if no version constraint
        if (!$required) {
            return true;
        }

        $version = $this->getCurrentVersion();
        $hasRequiredVersion = version_compare($version, $required, '>=');
        if (!$hasRequiredVersion) {
            $this->explainer->error(sprintf('%s is not at required version: %s instead of %s', $this->getLanguage(), $version, $required));
        }

        return $hasRequiredVersion;
    }

    /**
     * Check for required extensions on the server.
     *
     * @return bool
     */
    public function extensions()
    {
        $extensions = $this->getRequiredExtensions();
        $missing = array_filter($extensions, function ($extension) {
            return !$this->checkExtension($extension);
        });

        if ($missing) {
            $this->explainer->error('Extensions mission from server: '.implode(', ', $missing));
        }

        return count($missing) === 0;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// LANGUAGE //////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the version constraint which should be checked against.
     *
     * @param string $manifest
     *
     * @return string
     */
    abstract protected function getLanguageConstraint($manifest);

    /**
     * Get the current version in use.
     *
     * @return string
     */
    abstract protected function getCurrentVersion();

    ////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////// EXTENSIONS //////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * @return string[]
     */
    protected function getRequiredExtensions()
    {
        return [];
    }

    /**
     * @param string $extension
     *
     * @return bool
     */
    protected function checkExtension($extension)
    {
        return $extension;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param string $manifest
     * @param string $handle
     *
     * @return string
     */
    protected function getLanguageConstraintFromJson($manifest, $handle)
    {
        $manifest = json_decode($manifest, true);
        $constraint = (string) Arr::get($manifest, $handle);
        $constraint = preg_replace('/[~>= ]+ ?(.+)/', '$1', $constraint);

        return $constraint;
    }
}
