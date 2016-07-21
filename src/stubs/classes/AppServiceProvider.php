<?php

namespace App;

use Rocketeer\Console\Console;
use Rocketeer\Plugins\AbstractPlugin;
use Rocketeer\Services\Tasks\TasksHandler;

class AppServiceProvider extends AbstractPlugin
{
    /**
     * Here you can tinker with Rocketeer's internals
     * as you wish, replace modules, add events and tasks, etc.
     */
    public function register()
    {
        // $this->container->addServiceProvider(
        //   \Rocketeer\Plugins\Laravel\LaravelPlugin::class
        // );
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        parent::boot();

        // if ($this->connections->is('production')) {
        //     $this->container->add('paths.php', '/usr/bin/php');
        // }
    }

    /**
     * Register additional commands.
     *
     * @param Console $console
     */
    public function onConsole(Console $console)
    {
        // $console->addCommands([
        //    SomeCommand::class,
        // ]);
    }

    /**
     * Register Tasks with Rocketeer.
     *
     * @param TasksHandler $tasks
     */
    public function onQueue(TasksHandler $tasks)
    {
        // $tasks->before('deploy', function ($task) {
        //     $task->run('ls');
        // });
    }
}
