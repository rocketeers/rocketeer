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

namespace Rocketeer\Services\Bootstrapper;

use Rocketeer\TestCases\RocketeerTestCase;

class BootstrapperTest extends RocketeerTestCase
{
    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// TESTS /////////////////////////////
    ////////////////////////////////////////////////////////////////////

    public function testDoesNotRebindBasePath()
    {
        $base = 'src';
        $this->container->add('path.base', $base);
        $this->bootstrapper->bootstrapPaths();

        $this->assertEquals($base, $this->container->get('path.base'));
    }

    public function testCanBindBasePath()
    {
        $this->container->remove('path.base');
        $this->bootstrapper->bootstrapPaths();

        $this->assertEquals(realpath(__DIR__.'/../../..'), $this->container->get('path.base'));
    }

    public function testCanExportConfiguration()
    {
        $this->bootstrapper->bootstrapPaths();
        $this->configurationPublisher->publish();

        $this->assertVirtualFileExists('src/.rocketeer');
    }

    public function testCanUseFilesAndFoldersForContextualConfig()
    {
        $this->swapConfigWithEvents();
        $folder = $this->replicateConfiguration();

        $file = $folder.'/connections/production/vcs.php';
        $this->files->write($file, '<?php return ["vcs" => "svn"];');

        $this->bootstrapper->bootstrapConfiguration();
        $this->assertEquals('svn', $this->config->getContextually('vcs.vcs'));
    }

    public function testCanUseFilesAndFoldersForPluginsConfig()
    {
        $folder = $this->replicateConfiguration();

        $file = $folder.'/plugins/laravel.php';
        $this->files->write($file, '<?php return ["foo" => "bar"];');
        $this->files->write($folder.'/plugins/foobar.php', '<?php return ["bar" => "baz"];');

        $this->bootstrapper->bootstrapConfiguration();
        $this->assertEquals('bar', $this->config->get('plugins.config.laravel.foo'));
    }

    public function testDoesNotCrashIfNoSubFolder()
    {
        $this->container->add('path.base', '/foobar');
        $this->files->createDir('/foobar');

        $this->bootstrapper->bootstrapConfiguration();
    }

    public function testCanLoadDotEnvFiles()
    {
        $config = $this->replicateConfiguration();

        $path = $this->paths->getDotenvPath();
        $this->files->put($path, 'VCS_REPOSITORY=foobar');
        $this->files->put($config.'/vcs.php', '<?php return ["vcs" => ["vcs" => "git", "repository" => "%%VCS_REPOSITORY%%"]];');

        $this->bootstrapper->bootstrapConfiguration();

        $this->assertEquals('foobar', $this->config->get('vcs.repository'));
    }
}
