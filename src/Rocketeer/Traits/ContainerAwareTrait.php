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

namespace Rocketeer\Traits;

use League\Container\ContainerAwareTrait as LeagueContainerAwareTrait;
use Rocketeer\Services\Container\Container;

trait ContainerAwareTrait
{
    use HasLocatorTrait;
    use LeagueContainerAwareTrait;

    /**
     * @param Container $container
     */
    public function __construct(Container $container = null)
    {
        $this->container = $container;
    }
}
