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

/**
 * Stores information about the current state of the application
 * on a specific server.
 */
class ServerStorage extends Storage
{
    /**
     * {@inheritdoc}
     */
    public function __construct(Container $container)
    {
        $this->container = $container;

        parent::__construct($container, 'remote', $this->paths->getFolder(), 'state');
    }

    /**
     * {@inheritdoc}
     */
    public function getFilepath()
    {
        return $this->paths->getFolder().DS.$this->getFilename();
    }

    /**
     * {@inheritdoc}
     */
    protected function saveContents($contents)
    {
        if (!$this->getOption('pretend')) {
            parent::saveContents($contents);
        }
    }
}
