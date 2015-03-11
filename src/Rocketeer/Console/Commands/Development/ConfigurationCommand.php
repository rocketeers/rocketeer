<?php
namespace Rocketeer\Console\Commands\Development;

use Rocketeer\Abstracts\Commands\AbstractCommand;

class ConfigurationCommand extends AbstractCommand
{
    /**
     * The console command name.
     *
     * @type string
     */
    protected $name = 'debug:config';

    /**
     * @type string
     */
    protected $description = "Dumps the current configuration parsed";

    /**
     * Fire the command.
     */
    public function fire()
    {
        $configuration = $this->config->toArray();

        dump($configuration);
    }
}
