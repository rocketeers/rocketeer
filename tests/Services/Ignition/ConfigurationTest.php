<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Services\Ignition;

use Mockery\MockInterface;
use Rocketeer\TestCases\RocketeerTestCase;

class ConfigurationTest extends RocketeerTestCase
{
    /**
     * The igniter instance.
     *
     * @type Configuration
     */
    protected $igniter;

    /**
     * Setup the tests.
     */
    public function setUp()
    {
        parent::setUp();

        $this->igniter = new Configuration($this->app);
        unset($this->app['path.base']);
        $this->usesLaravel(false);
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// TESTS /////////////////////////////
    ////////////////////////////////////////////////////////////////////

    public function testDoesntRebindBasePath()
    {
        $base = 'src';
        $this->app->instance('path.base', $base);
        $this->igniter->bindPaths();

        $this->assertEquals($base, $this->app['path.base']);
    }

    public function testCanBindBasePath()
    {
        $this->igniter->bindPaths();

        $this->assertEquals(realpath(__DIR__.'/../../..'), $this->app['path.base']);
    }

    public function testCanBindConfigurationPaths()
    {
        $this->igniter->bindPaths();

        $root = realpath(__DIR__.'/../../..');
        $this->assertEquals($root.'/.rocketeer', $this->app['path.rocketeer.config']);
    }

    public function testCanBindTasksAndEventsPaths()
    {
        $this->igniter->bindPaths();
        $this->igniter->exportConfiguration();

        // Create some fake files
        $root = realpath(__DIR__.'/../../../').'/.rocketeer';
        $this->files->put($root.'/events.php', '');
        $this->files->createDir($root.'/tasks');

        $this->igniter->bindPaths();

        $this->assertEquals($root.'/tasks', $this->app['path.rocketeer.tasks']);
        $this->assertEquals($root.'/events.php', $this->app['path.rocketeer.events']);
    }

    public function testCanExportConfiguration()
    {
        $this->igniter->bindPaths();
        $this->igniter->exportConfiguration();

        $this->assertVirtualFileExists(__DIR__.'/../../../.rocketeer');
    }

    public function testCanReplaceStubsInConfigurationFile()
    {
        $this->igniter->bindPaths();
        $path = $this->igniter->exportConfiguration();
        $this->igniter->updateConfiguration($path, ['scm_username' => 'foobar']);

        $this->assertVirtualFileExists(__DIR__.'/../../../.rocketeer');
        $this->assertContains('foobar', $this->files->read(__DIR__.'/../../../.rocketeer/scm.php'));
    }

    public function testCanSetCurrentApplication()
    {
        $this->mock('rocketeer.storage.local', 'LocalStorage', function (MockInterface $mock) {
            return $mock->shouldReceive('setFile')->once()->with('foobar');
        });

        $this->igniter->bindPaths();
        $path = $this->igniter->exportConfiguration();
        $this->igniter->updateConfiguration($path, ['application_name' => 'foobar', 'scm_username' => 'foobar']);

        $this->assertVirtualFileExists(__DIR__.'/../../../.rocketeer');
        $this->assertContains('foobar', $this->files->read(__DIR__.'/../../../.rocketeer/config.php'));
    }

    public function testCanLoadFilesOrFolder()
    {
        $config                 = $this->customConfig;
        $this->app['path.base'] = dirname($config);

        $this->files->createDir($config.'/events');
        $this->files->put($config.'/tasks.php', '<?php Rocketeer\Facades\Rocketeer::task("DisplayFiles", ["ls", "ls"]);');
        $this->files->put($config.'/events/some-event.php', '<?php Rocketeer\Facades\Rocketeer::before("DisplayFiles", "whoami");');

        $this->igniter->bindPaths();
        $this->igniter->loadUserConfiguration();
        $this->tasks->registerConfiguredEvents();

        $task = $this->task('DisplayFiles');
        $this->assertInstanceOf('Rocketeer\Tasks\Closure', $task);
        $this->assertEquals('DisplayFiles', $task->getName());

        $events = $this->tasks->getTasksListeners($task, 'before');
        $this->assertCount(1, $events);
        $this->assertEquals('whoami', $events[0]->getStringTask());
    }

    public function testCanLoadCustomStrategies()
    {
        $config                 = $this->customConfig;
        $this->app['path.base'] = dirname($config);

        $this->files->createDir($config.'/strategies');
        $this->files->put($config.'/strategies/Foobar.php', '<?php namespace Lol; class Foobar extends \Rocketeer\Abstracts\Strategies\AbstractStrategy { public function fire() { $this->runForCurrentRelease("ls"); } }');

        $this->igniter->bindPaths();
        $this->igniter->loadUserConfiguration();
        $this->tasks->registerConfiguredEvents();

        $strategy = $this->builder->buildStrategy('test', 'Lol\Foobar');
        $this->assertInstanceOf('Lol\Foobar', $strategy);
    }

    public function testCanUseFilesAndFoldersForContextualConfig()
    {
        $this->app['path.rocketeer.config'] = $this->customConfig;
        $this->configurationLoader->addFolder($this->customConfig);

        $file = $this->customConfig.'/connections/production/scm.php';
        $this->files->createDir(dirname($file));
        $this->files->write($file, '<?php return array("scm" => "svn");');

        $this->igniter->mergeContextualConfigurations();
        $this->assertEquals('svn', $this->rocketeer->getOption('scm.scm'));
    }

    public function testDoesntCrashIfNoSubfolder()
    {
        $this->files->createDir($this->customConfig);
        $this->app['path.rocketeer.config'] = realpath($this->customConfig);

        $this->igniter->mergeContextualConfigurations();
    }

    public function testCanExportConfigurationFromArchive()
    {
        $pharPath = 'phar:///rocketeer/bin/rocketeer.phar/src/Rocketeer/Services/Ignition/../../../config';

        $this->mock('rocketeer.paths', 'Pathfinder', function (MockInterface $mock) use ($pharPath) {
            return $mock
                ->shouldReceive('unifyLocalSlashes')->andReturn($pharPath)
                ->shouldReceive('getConfigurationPath')->andReturn('config');
        });

        $this->mockFiles(function (MockInterface $mock) use ($pharPath) {
            return $mock->shouldReceive('copyDirectory')->with($pharPath, 'config');
        });

        $this->igniter->exportConfiguration();
    }
}
