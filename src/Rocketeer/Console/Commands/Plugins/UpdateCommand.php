<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Console\Commands\Plugins;

use Rocketeer\Abstracts\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;

class UpdateCommand extends AbstractCommand
{
    /**
     * The default name
     *
     * @type string
     */
    protected $name = 'plugin:update';

    /**
     * The console command description.
     *
     * @type string
     */
    protected $description = 'Update one or all plugin(s)';

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
        return $this->fireTasksQueue('Plugins\Updater');
    }

    /**
     * Get the console command arguments.
     *
     * @return string[][]
     */
    protected function getArguments()
    {
        return array(
            ['package', InputArgument::OPTIONAL, 'The package to publish the configuration for'],
        );
    }
}
