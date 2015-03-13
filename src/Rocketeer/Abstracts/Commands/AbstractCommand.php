<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Abstracts\Commands;

use Illuminate\Console\Command;
use Rocketeer\Abstracts\AbstractTask;
use Rocketeer\Abstracts\Closure;
use Rocketeer\Console\Commands\RocketeerCommand;
use Rocketeer\Interfaces\IdentifierInterface;
use Rocketeer\Traits\HasLocator;
use Rocketeer\Traits\Properties\HasEvents;
use Symfony\Component\Console\Input\InputOption;

/**
 * An abstract command with various helpers for all
 * subcommands to inherit.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
abstract class AbstractCommand extends Command implements IdentifierInterface
{
    use HasLocator;
    use HasEvents;

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
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        $key = $this->getLocatorHandle($key);
        if ($key === 'rocketeer.command') {
            return $this;
        }

        return $this->laravel->make($key);
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
        $framework = $this->getFramework();
        if ($framework && $framework->isInsideApplication() && !$this instanceof RocketeerCommand) {
            $name = str_replace(':', '-', $this->name);
            $name = 'deploy:'.$name;

            return $name;
        }

        return $this->name;
    }

    /**
     * Get a global identifier for this entity.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'commands.'.str_replace(':', '.', $this->name);
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
            ['server', null, InputOption::VALUE_REQUIRED, 'The server to execute the Task in'],
            ['branch', 'B', InputOption::VALUE_REQUIRED, 'The branch to deploy'],
            ['release', null, InputOption::VALUE_REQUIRED, 'What to tag the release as'],
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
     * Check if the class is executed inside a Laravel application.
     *
     * @return \Rocketeer\Interfaces\Strategies\FrameworkStrategyInterface|null
     */
    public function getFramework()
    {
        return $this->laravel->bound('rocketeer.builder') ? $this->builder->buildStrategy('Framework') : null;
    }

    /**
     * Check if the current instance has a Command bound.
     *
     * @return bool
     */
    protected function hasCommand()
    {
        return $this->laravel->bound('rocketeer.command');
    }

    ////////////////////////////////////////////////////////////////////
    ///////////////////////////// CORE METHODS /////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Fire a Tasks Queue.
     *
     * @param string|string[]|Closure|Closure[]|\Rocketeer\Abstracts\AbstractTask[] $tasks
     *
     * @return int
     */
    protected function fireTasksQueue($tasks)
    {
        $this->prepareEnvironment();

        // Fire tasks and events arround them
        $status = $this->runWithBeforeAfterEvents(function () use ($tasks) {
            if ($this->straight) {
                return $this->builder->buildTask($tasks)->fire();
            }

            $pipeline = $this->queue->run($tasks);

            return $pipeline->succeeded();
        });

        // Remove command instance
        unset($this->laravel['rocketeer.command']);

        // Save history to logs
        $logs = $this->logs->write();
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
     * @return string|null
     */
    public function askWith($question, $default = null, $choices = [])
    {
        $question = $this->formatQuestion($question, $default, $choices);
        if ($this->checkInteractivity($question)) {
            return $default;
        }

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
        if ($this->checkInteractivity($question)) {
            return $default;
        }

        return $this->secret($question) ?: $default;
    }

    /**
     * Adds additional information to a question.
     *
     * @param string   $question
     * @param string   $default
     * @param string[] $choices
     *
     * @return string
     */
    protected function formatQuestion($question, $default, $choices = [])
    {
        // If default, show it in the question
        if ($default !== null) {
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
     * @param callable $callback
     *
     * @return bool
     */
    public function time(callable $callback)
    {
        $results = $this->timer->time($this, $callback);
        $time    = $this->timer->getLatestTime($this);

        $this->explainer->line('Execution time: <comment>'.$time.'s</comment>');

        return $results;
    }

    /**
     * @param string $question
     *
     * @return bool
     */
    protected function checkInteractivity($question)
    {
        $nonInteractive = !$this->input->isInteractive();
        if ($nonInteractive) {
            $this->explainer->error('Running in non interactive mode, prompt was skipped: '.$question);
        }

        return $nonInteractive;
    }

    /**
     * Prepare the environment.
     */
    protected function prepareEnvironment()
    {
        // Bind command to container
        $this->laravel->instance('rocketeer.command', $this);

        // Check for credentials
        $this->credentialsGatherer->getServerCredentials();
        $this->credentialsGatherer->getRepositoryCredentials();
    }
}
