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

namespace Rocketeer\Services\Ignition;

use Rocketeer\Services\Config\ConfigurationDefinition;
use Rocketeer\Traits\ContainerAwareTrait;

/**
 * Creates files related to the .rocketeer folder
 * Configuration, stubs, etc.
 */
class RocketeerIgniter
{
    use ContainerAwareTrait;

    /**
     * @return string
     */
    public function getIntroductionScreen()
    {
        return $this->files->read(__DIR__.'/../../../../bin/intro.txt');
    }

    /**
     * Export credentials to a dotenv file.
     *
     * @param array $credentials
     */
    public function exportDotenv(array $credentials)
    {
        // Build dotenv file
        $dotenv = '';
        foreach ($credentials as $credential => $value) {
            $value = str_replace('"', '\\"', $value);
            $dotenv .= sprintf('%s="%s"', $credential, $value);
            $dotenv .= PHP_EOL;
        }

        // Write to disk
        $this->files->append($this->paths->getDotenvPath(), $dotenv);
    }

    /**
     * Export Rocketeer's configuration in a given format.
     *
     * @param string $format
     * @param bool   $consolidated
     */
    public function exportConfiguration($format, $consolidated)
    {
        $definition = new ConfigurationDefinition();
        $definition->setValues($this->config->all());

        $this->configurationPublisher->setDefinition($definition);
        $this->configurationPublisher->publish($format, $consolidated);
    }

    /**
     * Export the provided type of stubs to a certain folder
     * Optionally replace a namespace with a given one.
     *
     * @param string      $type
     * @param string      $destination
     * @param string|null $namespace
     */
    public function exportStubs($type, $destination, $namespace = null)
    {
        // If we have no stubs for this type, cancel
        $source = __DIR__.'/../../../stubs/'.$type;
        if (!$this->files->has($source)) {
            return;
        }

        $this->files->createDir($destination);
        $files = $this->files->listFiles($source, true);
        foreach ($files as $file) {
            $contents = $this->files->read($file['path']);
            $basename = $file['basename'];
            $fileDestination = $destination.DS.$basename;

            if ($namespace) {
                $namespace = preg_replace("/[^\w]/", '', $namespace); // only words allowed
                $contents = str_replace('namespace App', 'namespace '.$namespace, $contents);
                $contents = str_replace('AppServiceProvider', $namespace.'ServiceProvider', $contents);
                $fileDestination = mb_strpos($basename, 'ServiceProvider') === false
                    ? $destination.DS.basename(dirname($file['path'])).DS.$basename
                    : $destination.DS.$namespace.'ServiceProvider.php';
            }

            $this->files->put($fileDestination, $contents);
        }
    }

    /**
     * @param string|null $namespace
     */
    public function exportComposerFile($namespace = null)
    {
        // Compose manifest contents
        $manifestPath = $this->paths->getRocketeerPath().'/composer.json';
        $manifest = [
            'minimum-stability' => 'dev',
            'prefer-stable' => true,
            'config' => [
                'preferred-install' => 'dist',
                'sort-packages' => true,
            ],
        ];

        if ($namespace) {
            $manifest['autoload'] = [
                'psr4' => [$namespace.'\\' => 'app'],
            ];
        }

        // Create manifest
        $contents = json_encode($manifest, JSON_PRETTY_PRINT);
        $this->files->put($manifestPath, $contents);
    }
}
