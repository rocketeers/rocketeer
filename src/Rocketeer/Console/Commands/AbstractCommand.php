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

namespace Rocketeer\Console\Commands;

use League\Container\ContainerAwareInterface;
use Rocketeer\Console\Style\RocketeerStyle;
use Rocketeer\Interfaces\IdentifierInterface;
use Rocketeer\Tasks\AbstractTask;
use Rocketeer\Tasks\Closure;
use Rocketeer\Tasks\Plugins\Installer;
use Rocketeer\Traits\ContainerAwareTrait;
use Rocketeer\Traits\Properties\HasEventsTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * An abstract command with various helpers for all
 * subcommands to inherit.
 *
 * @mixin RocketeerStyle
 */
abstract class AbstractCommand extends Command implements IdentifierInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;
    use HasEventsTrait;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var RocketeerStyle
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
     * @return RocketeerStyle
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
            ['root_directory', null, InputOption::VALUE_REQUIRED, 'The root directory to use if asked'],
            ['username', null, InputOption::VALUE_REQUIRED, 'The username to use if asked'],
            ['password', null, InputOption::VALUE_REQUIRED, 'The password to use if asked'],
            ['key', null, InputOption::VALUE_REQUIRED, 'The key to use if asked'],
            ['keyphrase', null, InputOption::VALUE_REQUIRED, 'The keyphrase to use if asked'],
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
            $this->addArgument(...$arguments);
        }

        foreach ($this->getOptions() as $options) {
            $this->addOption(...$options);
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
        if ($key === 'command') {
            return $this;
        }

        return $this->container->get($key);
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
            return $this->output->$name(...$arguments);
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
        $this->output = new RocketeerStyle($input, $output);

        return $this->container->call([$this, 'fire']);
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

        // Fire tasks and events around them
        $status = $this->runWithBeforeAfterEvents(function () use ($tasks) {
            if ($this->straight) {
                return $this->builder->buildTask($tasks)->fire();
            }

            $pipeline = $this->queue->run($tasks);

            return $pipeline->succeeded();
        });

        // Remove command instance
        $this->container->remove('command');

        // Save history to logs
        $this->explainer->info('Saved logs to '.$this->logs->getLogsRealpath());

        return $status ? 0 : 1;
    }

    /**
     * Run the tasks.
     *
     * @return mixed
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
     * Prepare the environment.
     */
    protected function prepareEnvironment()
    {
        // Bind command to container
        $this->container->add('command', $this);

        // Set active connections from flag
        if ($connections = $this->command->option('on')) {
            $this->connections->setActiveConnections($connections);
        }

        // Install and load plugins if not setup already
        $vendor = $this->paths->getRocketeerPath().DS.'vendor';
        if (!$this->files->has($vendor)) {
            $this->queue->execute(Installer::class);
            $this->bootstrapper->bootstrapRocketeerDependencies();
            $this->bootstrapper->bootstrapUserCode();
        }
    }
}
