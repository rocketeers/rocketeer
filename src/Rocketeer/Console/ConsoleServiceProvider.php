<?php
namespace Rocketeer\Console;

use KevinGH\Amend\Helper;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Rocketeer\Services\Ignition\Tasks;

class ConsoleServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = ['console'];

    /**
     * @var string[]
     */
    protected $commands;

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->share('console', function () {
            $console = new Console($this->container);
            $console->getHelperSet()->set(new Helper());

            // Get registered tasks and their commands
            $tasksIgniter = new Tasks($this->container);
            $tasks = $tasksIgniter->getPredefinedTasks();
            $commands = $tasksIgniter->registerTasksAndCommands($tasks);

            // Add found commands to the CLI application
            foreach ($commands as $command) {
                $console->add($command);
            }

            return $console;
        });
    }
}
