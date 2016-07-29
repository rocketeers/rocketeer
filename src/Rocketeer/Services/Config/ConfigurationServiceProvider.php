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

namespace Rocketeer\Services\Config;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Rocketeer\Services\Config\Files\ConfigurationCache;
use Rocketeer\Services\Config\Files\ConfigurationPublisher;
use Rocketeer\Services\Config\Files\Loaders\CachedConfigurationLoader;
use Rocketeer\Services\Config\Files\Loaders\ConfigurationLoader;
use Rocketeer\Services\Config\Files\Loaders\ConfigurationLoaderInterface;
use Rocketeer\Services\Config\Loaders\JsonLoader;
use Rocketeer\Services\Config\Loaders\PhpLoader;
use Rocketeer\Services\Config\Loaders\YamlLoader;
use Rocketeer\Traits\HasLocatorTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;

class ConfigurationServiceProvider extends AbstractServiceProvider
{
    use HasLocatorTrait;

    /**
     * @var array
     */
    protected $provides = [
        ConfigurationCache::class,
        ConfigurationLoaderInterface::class,
        ConfigurationPublisher::class,
        ContextualConfiguration::class,
        LoaderInterface::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->add(LoaderInterface::class, function () {
            $locator = new FileLocator();
            $resolver = new LoaderResolver([
                new PhpLoader($locator),
                new JsonLoader($locator),
                new YamlLoader($locator),
            ]);

            $loader = new DelegatingLoader($resolver);

            return $loader;
        });

        $this->container->share(ConfigurationCache::class, function () {
            return new ConfigurationCache($this->paths->getConfigurationCachePath(), true);
        });

        $this->container->share(ConfigurationLoaderInterface::class, function () {
            $cache = $this->container->get(ConfigurationCache::class);
            $loader = $this->container->get(ConfigurationLoader::class);
            $loader->setFolders([
                realpath(__DIR__.'/../../../config'),
                $this->paths->getConfigurationPath(),
            ]);

            return new CachedConfigurationLoader($cache, $loader);
        });

        $this->container->share(ConfigurationPublisher::class)->withArgument($this->container);
        $this->container->share(ContextualConfiguration::class, function () {
            $configuration = $this->configurationLoader->getConfiguration();
            $configuration = new Configuration($configuration);

            return new ContextualConfiguration($this->container, $configuration);
        });
    }
}
