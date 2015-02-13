<?php
namespace Rocketeer\Abstracts\Commands;

class AbstractPluginCommand
{
    /**
     * The plugin task to fire
     *
     * @type string
     */
    protected $pluginTask;

    /**
     * Whether the command's task should be built
     * into a pipeline or run straight
     *
     * @type boolean
     */
    protected $straight = true;

    /**
     * Run the tasks
     *
     * @return integer
     */
    public function fire()
    {
        return $this->fireTasksQueue('Plugins\\'.$this->pluginTask);
    }

    /**
     * Get the console command arguments.
     *
     * @return string[][]
     */
    protected function getArguments()
    {
        return array(
            ['package', InputArgument::REQUIRED, 'The package to publish the configuration for'],
        );
    }
}
