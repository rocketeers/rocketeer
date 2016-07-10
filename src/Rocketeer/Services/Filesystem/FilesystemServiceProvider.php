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

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Rocketeer\Services\Filesystem\Plugins\IncludePlugin;
use Rocketeer\Services\Filesystem\Plugins\IsDirectoryPlugin;
use Rocketeer\Services\Filesystem\Plugins\RequirePlugin;
use Rocketeer\Services\Filesystem\Plugins\UpsertPlugin;

class FilesystemServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        'flysystem',
        'files',
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->add('flysystem', function () {
            return (new MountManagerFactory($this->container))->getMountManager();
        });

        $this->container->share('files', function () {
            $local = new Filesystem(new Local('/', LOCK_EX, Local::SKIP_LINKS));
            $local->addPlugin(new RequirePlugin());
            $local->addPlugin(new IsDirectoryPlugin());
            $local->addPlugin(new IncludePlugin());
            $local->addPlugin(new UpsertPlugin());

            return $local;
        });
    }
}
