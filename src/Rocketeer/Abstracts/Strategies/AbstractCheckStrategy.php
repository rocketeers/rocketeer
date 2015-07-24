<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Abstracts\Strategies;

use Illuminate\Support\Arr;

abstract class AbstractCheckStrategy extends AbstractStrategy
{
    /**
     * @type \Rocketeer\Abstracts\AbstractPackageManager
     */
    protected $manager;

    /**
     * The language of the strategy.
     *
     * @type string
     */
    protected $language;

    /**
     * @return \Rocketeer\Abstracts\AbstractPackageManager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @param \Rocketeer\Abstracts\AbstractPackageManager $manager
     */
    public function setManager($manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
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
        return $this->manager && $this->manager->isExecutable();
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
        if ($this->manager && $manifest = $this->manager->getManifestContents()) {
            $required = $this->getLanguageConstraint($manifest);
        }

        // Cancel if no version constraint
        if (!$required) {
            return true;
        }

        $version = $this->getCurrentVersion();

        return version_compare($version, $required, '>=');
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
        $manifest   = json_decode($manifest, true);
        $constraint = (string) Arr::get($manifest, $handle);
        $constraint = preg_replace('/[~>= ]+ ?(.+)/', '$1', $constraint);

        return $constraint;
    }
}
