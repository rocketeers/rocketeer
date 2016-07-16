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
use Rocketeer\Traits\HasLocatorTrait;

class StorageServiceProvider extends AbstractServiceProvider
{
    use HasLocatorTrait;

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
        $this->container->add('storage.remote', function () {
            $filesystem = $this->rocketeer->isLocal()
                ? $this->files
                : $this->filesystems->getFilesystem('remote');

            return new ServerStorage($this->container, $filesystem);
        });

        $this->container->add('storage.local', function () {
            return new Storage($this->files, $this->paths->getRocketeerConfigFolder());
        });
    }
}
