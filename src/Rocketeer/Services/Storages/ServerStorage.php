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

namespace Rocketeer\Services\Storages;

use Rocketeer\Container;
use Rocketeer\Traits\ContainerAwareTrait;

class ServerStorage extends Storage
{
    use ContainerAwareTrait;

    public function __construct(Container $container, $filesystem, $folder)
    {
        $this->container = $container;

        parent::__construct($filesystem, $folder);
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value = null)
    {
        if (!$this->getOption('pretend')) {
            return parent::set($key, $value);
        }
    }
}
