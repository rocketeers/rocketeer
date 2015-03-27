<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Strategies\Check;

use Illuminate\Container\Container;
use Illuminate\Support\Arr;
use Rocketeer\Abstracts\Strategies\AbstractCheckStrategy;
use Rocketeer\Interfaces\Strategies\CheckStrategyInterface;

class RubyStrategy extends AbstractCheckStrategy implements CheckStrategyInterface
{
    /**
     * @type string
     */
    protected $description = 'Checks if the server is ready to receive a Ruby application';

    /**
     * The language of the strategy.
     *
     * @type string
     */
    protected $language = 'Ruby';

    /**
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app     = $app;
        $this->manager = $this->binary('bundler');
    }

    //////////////////////////////////////////////////////////////////////
    /////////////////////////////// CHECKS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the version constraint which should be checked against.
     *
     * @param string $manifest
     *
     * @return string
     */
    protected function getLanguageConstraint($manifest)
    {
        preg_match('/ruby \'(.+)\'/', $manifest, $matches);
        $required = Arr::get((array) $matches, 1);

        return $required;
    }

    /**
     * Get the current version in use.
     *
     * @return string
     */
    protected function getCurrentVersion()
    {
        $version = $this->binary('ruby')->run('--version');
        $version = preg_replace('/ruby ([0-9\.]+)p?.+/', '$1', $version);

        return $version;
    }

    /**
     * Check for the required extensions.
     *
     * @return array
     */
    public function extensions()
    {
        return [];
    }

    /**
     * Check for the required drivers.
     *
     * @return array
     */
    public function drivers()
    {
        return [];
    }
}
