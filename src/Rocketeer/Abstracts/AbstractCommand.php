<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Abstracts;

use Closure;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * An abstract command with various helpers for all
 * subcommands to inherit.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
abstract class AbstractCommand extends Command
{
    /**
     * Whether the command's task should be built
     * into a pipeline or run straight.
     *
     * @type bool
     */
    protected $straight = false;

    /**
     * the task to execute on fire.
     *
     * @type AbstractTask
     */
    protected $task;

    /**
     * @param AbstractTask|null $task
     */
    public function __construct(AbstractTask $task = null)
    {
        parent::__construct();

        // If we passed a Task, bind its properties
        // to the command
        if ($task) {
            $this->task          = $task;
            $this->task->command = $this;

            if (!$this->description && $description = $task->getDescription()) {
                $this->setDescription($description);
            }
        }
    }

    /**
     * Get the task this command executes.
     *
     * @return AbstractTask
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * Returns the command name.
     *
     * @return string The command name
     */
    public function getName()
    {
        // Return commands as is in Laravel
        if ($this->isInsideLaravel()) {
            return $this->name;
        }

        $name = str_replace('deploy:', null, $this->name);
        $name = str_replace('-', ':', $name);

        return $name;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// EXECUTION /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Run the tasks.
     */
    abstract public function fire();

    /**
     * Get the console command options.
     *
     * @return array<string[]|array<string|null>>
     */
    protected function getOptions()
    {
        // General options
        $global = [
            ['parallel', 'P', InputOption::VALUE_NONE, 'Run the tasks asynchronously instead of sequentially'],
            ['pretend', 'p', InputOption::VALUE_NONE, 'Shows which command would execute without actually doing anything'],
        ];

        // Options that override the predefined configuration
        $overrides = [
            ['on', 'C', InputOption::VALUE_REQUIRED, 'The connection(s) to execute the Task in'],
            ['stage', 'S', InputOption::VALUE_REQUIRED, 'The stage to execute the Task in'],
            ['branch', 'B', InputOption::VALUE_REQUIRED, 'The branch to deploy'],
        ];

        // Additional credentials passed to Rocketeer
        $credentials = [
            ['host', null, InputOption::VALUE_REQUIRED, 'The host to use if asked'],
            ['username', null, InputOption::VALUE_REQUIRED, 'The username to use if asked'],
            ['password', null, InputOption::VALUE_REQUIRED, 'The password to use if asked'],
            ['key', null, InputOption::VALUE_REQUIRED, 'The key to use if asked'],
            ['keyphrase', null, InputOption::VALUE_REQUIRED, 'The keyphrase to use if asked'],
            ['agent', null, InputOption::VALUE_REQUIRED, 'The agent to use if asked'],
            ['repository', null, InputOption::VALUE_REQUIRED, 'The repository to use if asked'],
        ];

        return array_merge(
            $global,
            $overrides,
            $credentials
        );
    }

    /**
     * Check if the current command is run in the scope of
     * Laravel or standalone.
     *
     * @return bool
     */
    public function isInsideLaravel()
    {
        return $this->laravel->bound('artisan');
    }

    ////////////////////////////////////////////////////////////////////
    ///////////////////////////// CORE METHODS /////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Fire a Tasks Queue.
     *
     * @param string|string[]|\Rocketeer\Abstracts\AbstractTask[] $tasks
     *
     * @return int
     */
    protected function fireTasksQueue($tasks)
    {
        // Bind command to container
        $this->laravel->instance('rocketeer.command', $this);

        // Check for credentials
        if (!$this->laravel['rocketeer.rocketeer']->isLocal()) {
            $this->laravel['rocketeer.credentials']->getServerCredentials();
            $this->laravel['rocketeer.credentials']->getRepositoryCredentials();
        }

        if ($this->straight) {
            // If we only have a single task, run it
            $status = $this->laravel['rocketeer.builder']->buildTask($tasks)->fire();
        } else {
            // Run tasks and display timer
            $status = $this->time(function () use ($tasks) {
                $pipeline = $this->laravel['rocketeer.queue']->run($tasks);

                return $pipeline->succeeded();
            });
        }

        // Remove command instance
        unset($this->laravel['rocketeer.command']);

        // Save history to logs
        $logs = $this->laravel['rocketeer.logs']->write();
        foreach ($logs as $log) {
            $this->info('Saved logs to '.$log);
        }

        return $status ? 0 : 1;
    }

    //////////////////////////////////////////////////////////////////////
    /////////////////////////////// INPUT ////////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Ask a question to the user, with default and/or multiple choices.
     *
     * @param string      $question
     * @param string|null $default
     * @param string[]    $choices
     *
     * @return string
     */
    public function askWith($question, $default = null, $choices = [])
    {
        $question = $this->formatQuestion($question, $default, $choices);

        // If we provided choices, autocomplete
        if ($choices) {
            return $this->askWithCompletion($question, $choices, $default);
        }

        return $this->ask($question, $default);
    }

    /**
     * Ask a question to the user, hiding the input.
     *
     * @param string      $question
     * @param string|null $default
     *
     * @return string|null
     */
    public function askSecretly($question, $default = null)
    {
        $question = $this->formatQuestion($question, $default);

        return $this->secret($question) ?: $default;
    }

    /**
     * Adds additional information to a question.
     *
     * @param string $question
     * @param string $default
     * @param array  $choices
     *
     * @return string
     */
    protected function formatQuestion($question, $default, $choices = [])
    {
        // If default, show it in the question
        if (!is_null($default)) {
            $question .= ' ('.$default.')';
        }

        // If multiple choices, show them
        if ($choices) {
            $question .= ' ['.implode('/', $choices).']';
        }

        return $question;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Time an operation and display it afterwards.
     *
     * @param Closure $callback
     *
     * @return bool
     */
    public function time(Closure $callback)
    {
        // Start timer, execute callback, close timer
        $timerStart = microtime(true);
        $results    = $callback();
        $time       = round(microtime(true) - $timerStart, 4);

        $this->line('Execution time: <comment>'.$time.'s</comment>');

        return $results;
    }
}
