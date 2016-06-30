<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Services\Config;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Rocketeer\Services\Config\Files\ConfigurationCache;
use Rocketeer\Services\Config\Files\ConfigurationLoader;
use Rocketeer\Services\Config\Files\ConfigurationPublisher;
use Symfony\Component\Config\Definition\Loaders\PhpLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;

class ConfigurationServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        LoaderInterface::class,
        ConfigurationCache::class,
        'config.loader',
        'config.publisher',
        'config',
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->add(LoaderInterface::class, function () {
            $locator = new FileLocator();
            $loader = new LoaderResolver([new PhpLoader($locator)]);
            $loader = new DelegatingLoader($loader);

            return $loader;
        });

        $this->container->share(ConfigurationCache::class, function () {
            return new ConfigurationCache($this->container->get('paths')->getConfigurationCachePath(), false);
        });

        $this->container->share('config.loader', function () {
            $loader = $this->container->get(ConfigurationLoader::class);
            $loader->setFolders([
                __DIR__.'/../../../config',
                $this->container->get('paths')->getConfigurationPath(),
            ]);

            return $loader;
        });

        $this->container->add('config.publisher', function () {
            return new ConfigurationPublisher(new ConfigurationDefinition(), $this->container->get('files'));
        });

        $this->container->share('config', function () {
            $configuration = new Configuration($this->container->get('config.loader')->getConfiguration());

            return new ContextualConfiguration($this->container, $configuration);
        });
    }
}
