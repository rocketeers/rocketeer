<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Services\Storages;

use League\Container\ServiceProvider\AbstractServiceProvider;

class StorageServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        'storage.local',
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     */
    public function register()
    {
        $this->container->share('storage.local', function () {
            $folder = $this->container->get('paths')->getRocketeerConfigFolder();
            $filename = $this->container->get('rocketeer.rocketeer')->getApplicationName();
            $filename = $filename === '{application_name}' ? 'deployments' : $filename;

            return new Storage($this->container, 'local', $folder, $filename);
        });
    }
}
