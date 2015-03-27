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

class ListCommand extends AbstractCommand
{
    /**
     * The default name.
     *
     * @type string
     */
    protected $name = 'deploy:plugin-list';

    /**
     * The console command description.
     *
     * @type string
     */
    protected $description = 'Lists the currently enabled plugins';

    /**
     * Whether the command's task should be built
     * into a pipeline or run straight.
     *
     * @type bool
     */
    protected $straight = true;

    /**
     * Run the tasks.
     */
    public function fire()
    {
        $rows    = [];
        $plugins = $this->laravel['rocketeer.tasks']->getRegisteredPlugins();
        foreach ($plugins as $plugin => $instance) {
            $rows[] = [$plugin];
        }

        $this->table(['Plugin'], $rows);
    }
}
