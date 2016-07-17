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
use Rocketeer\TestCases\RocketeerTestCase;

class BootstrapperTest extends RocketeerTestCase
{
    /**
     * The igniter instance.
     *
     * @var Bootstrapper
     */
    protected $igniter;

    /**
     * Setup the tests.
     */
    public function setUp()
    {
        parent::setUp();

        $this->container->remove('path.base');
        $this->usesLaravel(false);
    }

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
        $this->bootstrapper->bootstrapPaths();

        $this->assertEquals(realpath(__DIR__.'/../../..'), $this->container->get('path.base'));
    }

    public function testCanBindConfigurationPaths()
    {
        $this->bootstrapper->bootstrapPaths();

        $root = realpath(__DIR__.'/../../..');
        $this->assertEquals($root.'/.rocketeer', $this->paths->getRocketeerPath());
    }

    public function testCanExportConfiguration()
    {
        $this->bootstrapper->bootstrapPaths();
        $this->configurationPublisher->publish();

        $this->assertVirtualFileExists(__DIR__.'/../../../.rocketeer');
    }

    public function testCanLoadFilesOrFolder()
    {
        $config = $this->customConfig;
        $this->container->add('path.base', dirname($config));

        $this->files->createDir($config.'/events');
        $this->files->put($config.'/tasks.php', '<?php Rocketeer\Facades\Rocketeer::task("DisplayFiles", ["ls", "ls"]);');
        $this->files->put($config.'/events/some-event.php', '<?php Rocketeer\Facades\Rocketeer::before("DisplayFiles", "whoami");');

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

    public function testCanLoadCustomStrategies()
    {
        $config = $this->customConfig;
        $this->container->add('path.base', dirname($config));

        $this->files->createDir($config.'/strategies');
        $this->files->put($config.'/strategies/Foobar.php', '<?php namespace Lol; class Foobar extends \Rocketeer\Strategies\AbstractStrategy { public function fire() { $this->runForCurrentRelease("ls"); } }');

        $this->bootstrapper->bootstrapPaths();
        $this->bootstrapper->bootstrapUserCode();
        $this->tasks->registerConfiguredEvents();

        $strategy = $this->builder->buildStrategy('test', 'Lol\Foobar');
        $this->assertInstanceOf('Lol\Foobar', $strategy);
    }

    public function testCanUseFilesAndFoldersForContextualConfig()
    {
        $this->markTestSkipped('Until I find a solution to replicating Finder on a VFS');

        $this->files->createDir($this->customConfig);
        $this->configurationLoader->addFolder($this->customConfig);

        $file = $this->customConfig.'/connections/production/scm.php';
        $this->files->createDir(dirname($file));
        $this->files->write($file, '<?php return ["scm" => "svn"];');

        $this->bootstrapper->bootstrapContextualConfiguration();
        $this->assertEquals('svn', $this->config->getContextually('scm.scm'));
    }

    public function testDoesntCrashIfNoSubfolder()
    {
        $this->files->createDir($this->customConfig);

        $this->bootstrapper->bootstrapContextualConfiguration();
    }
}
