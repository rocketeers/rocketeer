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

use Rocketeer\Traits\HasLocator;

/**
 * Handles interaction between the User provided informations
 * and the various Rocketeer components.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Rocketeer
{
    use HasLocator;

    /**
     * The specific commit built.
     */
    const COMMIT = '@commit@';

    /**
     * Global local mode.
     *
     * @var bool
     */
    protected $local = false;

    /**
     * Returns what stage Rocketeer thinks he's in.
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
    /////////////////////////////// LOCAL ////////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @return bool
     */
    public function isLocal()
    {
        return $this->local;
    }

    /**
     * @param bool $local
     */
    public function setLocal($local)
    {
        $this->local = $local;
    }

    //////////////////////////////////////////////////////////////////////
    //////////////////////////// CONFIGURATION ///////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the name of the application to deploy.
     *
     * @return string
     */
    public function getApplicationName()
    {
        return $this->config->get('application_name');
    }
}
