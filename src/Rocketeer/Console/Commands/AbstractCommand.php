<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Console\Commands;

use Rocketeer\Tasks\Closure;
use Rocketeer\Interfaces\IdentifierInterface;
use Rocketeer\Interfaces\Strategies\FrameworkStrategyInterface;
use Rocketeer\Tasks\AbstractTask;
use Rocketeer\Traits\HasLocator;
use Rocketeer\Traits\Properties\HasEvents;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * An abstract command with various helpers for all
 * subcommands to inherit.
 *
 * @mixin SymfonyStyle
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
abstract class AbstractCommand extends Command implements IdentifierInterface
{
    use HasLocator;
    use HasEvents;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var SymfonyStyle
     */
    protected $output;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description;

    /**
     * Whether the command's task should be built
     * into a pipeline or run straight.
     *
     * @var bool
     */
    protected $straight = false;

    /**
     * the task to execute on fire.
     *
     * @var \Rocketeer\Tasks\AbstractTask
     */
    protected $task;

    /**
     * @param \Rocketeer\Tasks\AbstractTask|null $task
     */
    public function __construct(AbstractTask $task = null)
    {
        parent::__construct($this->name);

        $this->setDescription($this->description);
        $this->specifyParameters();

        // If we passed a Task, bind its properties
        // to the command
        if ($task) {
            $this->task = $task;
            $this->task->command = $this;

            if (!$this->description && $description = $task->getDescription()) {
                $this->setDescription($description);
            }
        }
    }

    /**
     * @return InputInterface
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// ARGUMENTS /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        // General options
        $global = [
            ['parallel', 'P', InputOption::VALUE_NONE, 'Run the tasks asynchronously instead of sequentially'],
            [
                'pretend',
                'p',
                InputOption::VALUE_NONE,
                'Shows which command would execute without actually doing anything',
            ],
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
     * Specify the arguments and options on the command.
     */
    protected function specifyParameters()
    {
        // We will loop through all of the arguments and options for the command and
        // set them all on the base command instance. This specifies what can get
        // passed into these commands as "parameters" to control the execution.
        foreach ($this->getArguments() as $arguments) {
            call_user_func_array([$this, 'addArgument'], $arguments);
        }

        foreach ($this->getOptions() as $options) {
            call_user_func_array([$this, 'addOption'], $options);
        }
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// CONTAINER /////////////////////////////
    //////////////////////////////////////////////////////////////////////

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

        return $this->app->get($key);
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        // Defer calls to output
        if (method_exists($this->output, $name)) {
            return call_user_func_array([$this->output, $name], $arguments);
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
     * Execute the console command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = new SymfonyStyle($input, $output);

        return $this->app->call([$this, 'fire']);
    }

    /**
     * Fire a Tasks Queue.
     *
     * @param string|string[]|Closure|Closure[]|\Rocketeer\Tasks\AbstractTask|\Rocketeer\Tasks\AbstractTask[] $tasks
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
        $this->app->remove('rocketeer.command');

        // Save history to logs
        $logs = $this->logs->write();
        foreach ($logs as $log) {
            $this->explainer->info('Saved logs to '.$log);
        }

        return $status ? 0 : 1;
    }

    /**
     * Run the tasks.
     */
    abstract public function fire();

    //////////////////////////////////////////////////////////////////////
    /////////////////////////////// INPUT ////////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the value of a command argument.
     *
     * @param string $key
     *
     * @return string|array
     */
    public function argument($key = null)
    {
        if (is_null($key)) {
            return $this->input->getArguments();
        }

        return $this->input->getArgument($key);
    }

    /**
     * Get the value of a command option.
     *
     * @param string $key
     *
     * @return string|array
     */
    public function option($key = null)
    {
        if (is_null($key)) {
            return $this->input->getOptions();
        }

        return $this->input->getOption($key);
    }

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
        if ($this->checkInteractivity($question)) {
            return $default;
        }

        // If we provided choices, autocomplete
        if ($choices) {
            return $this->output->choice($question, $choices, $default);
        }

        return $this->output->ask($question, $default, $this->getCredentialsValidator());
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
        if ($this->checkInteractivity($question)) {
            return $default;
        }

        $question = new Question($question);
        $question->setValidator($this->getCredentialsValidator());
        $question->setHidden(true);

        return $this->output->askQuestion($question) ?: $default;
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
        $time = $this->timer->getLatestTime($this);

        $this->explainer->line('Execution time: <comment>'.$time.'s</comment>');

        return $results;
    }

    /**
     * @return \Closure
     */
    protected function getCredentialsValidator()
    {
        return function ($value) {
            if (!is_string($value) && !is_bool($value) && !is_null($value)) {
                throw new RuntimeException('Invalid answer: '.$value);
            }

            return $value ?: true;
        };
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
        $this->app->add('rocketeer.command', $this);

        // Check for credentials
        $this->credentialsGatherer->getServerCredentials();
        $this->credentialsGatherer->getRepositoryCredentials();
    }
}
