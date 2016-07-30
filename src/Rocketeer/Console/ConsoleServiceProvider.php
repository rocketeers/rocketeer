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

namespace Rocketeer\Console;

use KevinGH\Amend\Command;
use KevinGH\Amend\Helper;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Rocketeer\Services\Bootstrapper\Tasks;
use Rocketeer\Traits\HasLocatorTrait;

class ConsoleServiceProvider extends AbstractServiceProvider
{
    use HasLocatorTrait;

    /**
     * @var array
     */
    protected $provides = [
        Console::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->share(Console::class, function () {
            $console = new Console($this->container);
            $console->getHelperSet()->set(new Helper());
            $console->addCommands($this->getCommands());

            return $console;
        });
    }

    /**
     * @return \Symfony\Component\Console\Command\Command[]
     */
    protected function getCommands()
    {
        $commands = [];
        foreach ($this->getPredefinedCommands() as $slug => $task) {
            $commands[] = $this->builder->buildCommand($task, $slug);
        }

        // Add self update command
        $selfUpdate = new Command('self-update');
        $selfUpdate->setManifestUri('http://rocketeer.autopergamene.eu/versions/manifest.json');
        $commands[] = $selfUpdate;

        return $commands;
    }

    /**
     * Get an array of all already defined tasks.
     *
     * @return array
     */
    public function getPredefinedCommands()
    {
        $tasks = [
            'check' => 'Check',
            'cleanup' => 'Cleanup',
            'current' => 'CurrentRelease',
            'deploy' => 'Deploy',
            'flush' => 'Flush',
            'ignite' => 'Ignite',
            'ignite-stubs' => 'Ignite\Stubs',
            'rollback' => 'Rollback',
            'setup' => 'Setup',
            'strategies' => 'Strategies',
            'teardown' => 'Teardown',
            'test' => 'Test',
            'update' => 'Update',
            'debug-tinker' => 'Development\Tinker',
            'debug-config' => 'Development\Configuration',
            'self-update' => 'Development\SelfUpdate',
            'plugin-publish' => 'Plugins\Publish',
            'plugin-list' => 'Plugins\List',
            'plugin-install' => 'Plugins\Install',
            'plugin-update' => 'Plugins\Updater',
        ];

        // Add user commands
        $userTasks = (array) $this->config->get('hooks.custom');
        $userTasks = array_filter($userTasks);
        $tasks = array_merge($tasks, $userTasks);

        return $tasks;
    }
}
