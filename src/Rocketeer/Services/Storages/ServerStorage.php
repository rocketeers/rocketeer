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

use Rocketeer\Services\Container\Container;
use League\Flysystem\FilesystemInterface;
use Rocketeer\Traits\ContainerAwareTrait;

/**
 * Stores information about the current state of the application
 * on a specific server.
 */
class ServerStorage extends Storage
{
    use ContainerAwareTrait;

    /**
     * @param Container           $container
     * @param FilesystemInterface $filesystem
     */
    public function __construct(Container $container, FilesystemInterface $filesystem)
    {
        $this->container = $container;

        parent::__construct($filesystem, $this->paths->getFolder());
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
