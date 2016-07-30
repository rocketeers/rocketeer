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

namespace Rocketeer\Services\Bootstrapper\Modules;

use Rocketeer\Dummies\Console\DummyCommand;
use Rocketeer\Dummies\DummyNotifier;
use Rocketeer\Tasks\Closure;
use Rocketeer\TestCases\RocketeerTestCase;

class UserBootstrapperTest extends RocketeerTestCase
{
    public function testCanLoadFilesOrFolder()
    {
        $this->files->put('/src/.rocketeer/app/tasks.php', '<?php Rocketeer::task("DisplayFiles", ["ls", "ls"]);');
        $this->files->put('/src/.rocketeer/app/events/some-event.php', '<?php Rocketeer::before("DisplayFiles", "whoami");');

        $this->bootstrapper->bootstrapPaths();
        $this->bootstrapper->bootstrapUserFiles();
        $this->bootstrapper->bootstrapUserCode();

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
        $this->files->createDir('/src/.rocketeer/config/strategies');
        $this->files->put('/src/.rocketeer/app/strategies/Foobar.php', '<?php namespace Lol; class Foobar extends \Rocketeer\Strategies\AbstractStrategy { public function fire() { $this->runForCurrentRelease("ls"); } }');

        $this->bootstrapper->bootstrapPaths();
        $this->bootstrapper->bootstrapUserFiles();
        $this->bootstrapper->bootstrapUserCode();

        $strategy = $this->builder->buildStrategy('test', 'Lol\Foobar');
        $this->assertInstanceOf('Lol\Foobar', $strategy);
    }

    public function testCanProperlyPascalCaseApplicationName()
    {
        $this->config->set('application_name', 'foo-bar');

        $this->assertEquals('FooBar', $this->bootstrapper->getUserNamespace());
    }

    public function testDoesntRegisterPluginsTwice()
    {
        $this->disableTestEvents();

        $this->container->addServiceProvider(new DummyNotifier($this->container));
        $this->bootstrapper->bootstrapUserCode();
        $this->bootstrapper->bootstrapUserCode();

        $listeners = $this->tasks->getTasksListeners('deploy', 'before', true);
        $this->assertEquals(['notify'], $listeners);
    }

    public function testCanRegisterCommands()
    {
        $this->config->set('hooks.tasks', DummyCommand::class);
        $this->bootstrapper->bootstrapUserCode();
    }
}
