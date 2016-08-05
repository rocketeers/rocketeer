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
use League\Flysystem\Plugin\ForcedCopy;
use League\Flysystem\Plugin\ListFiles;
use Rocketeer\Services\Filesystem\Plugins\AppendPlugin;
use Rocketeer\Services\Filesystem\Plugins\CopyDirectoryPlugin;
use Rocketeer\Services\Filesystem\Plugins\IncludePlugin;
use Rocketeer\Services\Filesystem\Plugins\IsDirectoryPlugin;
use Rocketeer\Services\Filesystem\Plugins\UpsertPlugin;

class FilesystemServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        MountManager::class,
        Filesystem::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->share(MountManager::class)->withArgument($this->container);
        $this->container->share(Filesystem::class, function () {
            $local = new Filesystem(new Local('/', LOCK_EX, Local::SKIP_LINKS));
            $local->addPlugin(new AppendPlugin());
            $local->addPlugin(new CopyDirectoryPlugin());
            $local->addPlugin(new ForcedCopy());
            $local->addPlugin(new IncludePlugin());
            $local->addPlugin(new IsDirectoryPlugin());
            $local->addPlugin(new ListFiles());
            $local->addPlugin(new UpsertPlugin());

            return $local;
        });
    }
}
