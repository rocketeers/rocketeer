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

use Rocketeer\Tasks\Closure;
use Rocketeer\Tasks\Ignite;
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

    public function testCanLoadFilesOrFolder()
    {
        $this->files->createDir('/src/.rocketeer/events');
        $this->files->put('/src/.rocketeer/tasks.php', '<?php Rocketeer::task("DisplayFiles", ["ls", "ls"]);');
        $this->files->put('/src/.rocketeer/events/some-event.php', '<?php Rocketeer::before("DisplayFiles", "whoami");');

        $this->bootstrapper->bootstrapPaths();
        $this->bootstrapper->bootstrapUserCode();
        $this->tasks->registerConfiguredEvents();

        $task = $this->task('DisplayFiles');
        $this->assertInstanceOf(Closure::class, $task);
        $this->assertEquals('DisplayFiles', $task->getName());
        $this->assertEquals(['ls', 'ls'], $task->getStringTask());

        $events = $this->tasks->getTasksListeners($task, 'before');
        $this->assertCount(1, $events);
        $this->assertEquals('whoami', $events[0]->getStringTask());
    }

    public function testDoesntLoadAppNamespaceFiles()
    {
        $this->expectOutputString('');

        $this->files->put('/src/.rocketeer/Foobar/Tasks/Ignite.php', '<?php echo "foobar";');
        $this->bootstrapper->bootstrapUserCode();
    }

    public function testCanLoadCustomStrategies()
    {
        $this->files->createDir('/src/.rocketeer/config/strategies');
        $this->files->put('/src/.rocketeer/config/strategies/Foobar.php', '<?php namespace Lol; class Foobar extends \Rocketeer\Strategies\AbstractStrategy { public function fire() { $this->runForCurrentRelease("ls"); } }');

        $this->bootstrapper->bootstrapPaths();
        $this->bootstrapper->bootstrapUserCode();
        $this->tasks->registerConfiguredEvents();

        $strategy = $this->builder->buildStrategy('test', 'Lol\Foobar');
        $this->assertInstanceOf('Lol\Foobar', $strategy);
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

        $this->bootstrapper->bootstrapConfiguration();
        $this->assertEquals('bar', $this->config->get('plugins.laravel.foo'));
    }

    public function testDoesntCrashIfNoSubfolder()
    {
        $this->container->add('path.base', '/foobar');
        $this->files->createDir('/foobar');

        $this->bootstrapper->bootstrapConfiguration();
    }
}
