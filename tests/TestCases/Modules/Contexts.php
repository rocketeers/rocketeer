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

namespace Rocketeer\TestCases\Modules;

use Rocketeer\Plugins\Laravel\Laravel;

/**
 * @mixin \Rocketeer\TestCases\RocketeerTestCase
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
trait Contexts
{
    /**
     * @param string $path
     */
    protected function mockBasepath($path)
    {
        $this->container->add('path.base', $path);
    }

    /**
     * @param string|null $system
     */
    protected function mockOperatingSystem($system = null)
    {
        $system = $system ?: PHP_OS;

        $this->localStorage->set('production.os', $system);
        $this->localStorage->set('staging.os', $system);
    }

    /**
     * @param bool  $usesHhvm
     * @param array $additional
     */
    protected function mockHhvm($usesHhvm = true, array $additional = [])
    {
        $this->bindDummyConnection(array_merge([
            'which php' => 'php',
            'php -r "print defined(\'HHVM_VERSION\');"' => (int) $usesHhvm,
        ], $additional));
    }

    /**
     * @param array $state
     */
    protected function mockState(array $state)
    {
        $contents = json_encode($state);
        $file = $this->server.'/state.json';

        $this->files->upsert($file, $contents);
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////// PACKAGE MANAGERS //////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Mock usage of Laravel framework.
     *
     * @param bool $uses
     */
    protected function usesLaravel($uses = true)
    {
        if ($uses) {
            $this->container->addServiceProvider(Laravel::class);
            $this->config->set('strategies.migrate', 'Laravel');
        } else {
            $this->container->remove('rocketeer.strategies.framework');
        }
    }

    /**
     * Mock the Composer check.
     *
     * @param bool        $uses
     * @param string|null $stage
     * @param array       $contents
     */
    protected function usesComposer($uses = true, $stage = null, $contents = [])
    {
        $this->mockPackageManagerUsage($uses, 'composer.json', $stage, json_encode($contents));
    }

    /**
     * Mock the Bundler check.
     *
     * @param bool        $uses
     * @param string|null $stage
     * @param string      $contents
     */
    protected function usesBundler($uses = true, $stage = null, $contents = '')
    {
        $this->mockPackageManagerUsage($uses, 'Gemfile', $stage, $contents);
    }

    /**
     * @param bool   $uses
     * @param null   $stage
     * @param string $contents
     */
    protected function usesNpm($uses, $stage, $contents)
    {
        $this->mockPackageManagerUsage($uses, 'package.json', $stage, json_encode($contents));
    }

    /**
     * Mock the use of a package manager.
     *
     * @param bool        $uses
     * @param string      $filename
     * @param string|null $stage
     * @param string|null $contents
     */
    protected function mockPackageManagerUsage($uses, $filename, $stage = null, $contents = null)
    {
        $serverPath = $this->server.'/';
        $serverPath .= $stage ? $stage.'/' : null;
        $serverPath .= 'releases/20000000000000/'.$filename;
        $localPath = $this->paths->getBasePath().'/'.$filename;

        // Create directory if necessary
        $folder = dirname($serverPath);
        if (!$this->files->isDirectory($folder)) {
            $this->files->createDir($folder);
        }

        if ($uses) {
            $this->files->put($localPath, $contents);
            $this->files->put($serverPath, $contents);
        } elseif ($this->files->has($serverPath)) {
            $this->files->delete($serverPath);
        }
    }
}
