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

/**
 * Checks if the server is ready to receive a Ruby application.
 */
class RubyStrategy extends AbstractCheckStrategy implements CheckStrategyInterface
{
    /**
     * The language of the strategy.
     *
     * @var string
     */
    protected $language = 'Ruby';

    /**
     * @var string
     */
    protected $binary = 'ruby';

    /**
     * @var string
     */
    protected $manager = 'bundler';

    /**
     * @var string
     */
    protected $description = 'Checks if the server is ready to receive a Ruby application';

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
        $version = $this->getBinary()->run('--version');
        $version = preg_replace('/ruby ([0-9\.]+)p?.+/', '$1', $version);

        return $version;
    }
}
