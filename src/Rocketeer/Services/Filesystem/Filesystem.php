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

namespace Rocketeer\Services\Filesystem;

use League\Flysystem\AdapterInterface;

/**
 * A fork of the Filesystem class that allows you to
 * switch its adapter.
 */
class Filesystem extends \League\Flysystem\Filesystem
{
    /**
     * @param AdapterInterface $adapter
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;
    }
}
