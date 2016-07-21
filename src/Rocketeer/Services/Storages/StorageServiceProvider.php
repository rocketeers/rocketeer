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

use League\Container\ServiceProvider\AbstractServiceProvider;
use Rocketeer\Services\Config\ContextualConfiguration;
use Rocketeer\Services\Environment\Pathfinder;

class StorageServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        'storage.local',
        'storage.remote',
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->share('storage.remote', function () {
            return new ServerStorage($this->container);
        });

        $this->container->share('storage.local', function () {
            /** @var Pathfinder $pathfinder */
            $pathfinder = $this->container->get(Pathfinder::class);

            // Get filename and folder
            $folder = $pathfinder->getRocketeerConfigFolder();
            $default = $pathfinder->getBasePath();
            $filename = $this->container->get(ContextualConfiguration::class)->get('application_name');
            $filename = $filename === '{application_name}' ? $default : $filename;

            return new Storage($this->container, 'local', $folder, $filename);
        });
    }
}
