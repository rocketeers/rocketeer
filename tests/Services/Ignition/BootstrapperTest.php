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

use Rocketeer\TestCases\RocketeerTestCase;

class BootstrapperTest extends RocketeerTestCase
{
    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// TESTS /////////////////////////////
    ////////////////////////////////////////////////////////////////////

    public function testDoesntRebindBasePath()
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
        $folder = $this->replicateConfiguration();

        $file = $folder.'/connections/production/scm.php';
        $this->files->write($file, '<?php return ["scm" => "svn"];');

        $this->bootstrapper->bootstrapConfiguration();
        $this->assertEquals('svn', $this->config->getContextually('scm.scm'));
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

    public function testDoesntCrashIfNoSubfolder()
    {
        $this->container->add('path.base', '/foobar');
        $this->files->createDir('/foobar');

        $this->bootstrapper->bootstrapConfiguration();
    }

    public function testCanLoadDotenvFiles()
    {
        $config = $this->replicateConfiguration();

        $path = $this->paths->getDotenvPath();
        $this->files->put($path, 'SCM_REPOSITORY=foobar');
        $this->files->put($config.'/scm.php', '<?php return ["scm" => ["scm" => "git", "repository" => "%%SCM_REPOSITORY%%"]];');

        $this->bootstrapper->bootstrapConfiguration();

        $this->assertEquals('foobar', $this->config->get('scm.repository'));
    }
}
