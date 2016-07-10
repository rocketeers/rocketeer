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

use League\Container\ContainerAwareTrait;
use Rocketeer\Container;

trait ContainerAware
{
    use HasLocator;
    use ContainerAwareTrait;

    /**
     * Build a new AbstractTask.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
}
